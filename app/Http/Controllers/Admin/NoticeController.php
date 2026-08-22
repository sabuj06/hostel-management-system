<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Notice;
use App\Models\Student;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
    }

    // Visible to everyone logged in — the notice board
    public function index(Request $request)
    {
        $user = $request->user();

        $notices = Notice::with('postedBy', 'hostel')
            ->published()
            ->when(! $user->hasRole('admin') && ! $user->hasRole('warden'), function ($q) use ($user) {
                // Non-management users only see notices meant for them
                $q->where(function ($q2) use ($user) {
                    $q2->where('audience', 'all');
                    if ($user->hasRole('student')) {
                        $q2->orWhere('audience', 'students');
                    } else {
                        $q2->orWhere('audience', 'staff');
                    }
                });
            })
            ->orderByDesc('priority')
            ->latest('publish_date')
            ->paginate(10);

        $readIds = \App\Models\NoticeRead::where('user_id', $user->id)->pluck('notice_id')->toArray();

        return view('notices.index', compact('notices', 'readIds'));
    }

    // Management-only: list including drafts, for editing
    public function manage(Request $request)
    {
        $notices = Notice::with('postedBy')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('notices.manage', compact('notices'));
    }

    public function create()
    {
        $hostels = Hostel::active()->get();

        return view('notices.create', compact('hostels'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['posted_by'] = $request->user()->id;
        $data['publish_date'] = $data['publish_date'] ?? now()->format('Y-m-d');

        $notice = Notice::create($data);

        if ($request->boolean('notify_now') && $notice->status === 'published') {
            $this->notifyStudentsAbout($notice, $request->user()->id);
        }

        return redirect()->route('notices.manage')->with('status', 'Notice published successfully.');
    }

    public function edit(Notice $notice)
    {
        $hostels = Hostel::active()->get();

        return view('notices.edit', compact('notice', 'hostels'));
    }

    public function update(Request $request, Notice $notice)
    {
        $notice->update($this->validated($request));

        return redirect()->route('notices.manage')->with('status', 'Notice updated successfully.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();

        return back()->with('status', 'Notice deleted successfully.');
    }

    // AJAX: mark a notice as read for the current user
    public function markRead(Request $request, Notice $notice)
    {
        $notice->reads()->firstOrCreate(['user_id' => $request->user()->id]);

        return response()->json(['success' => true]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'audience' => ['required', 'in:all,students,staff,hostel'],
            'hostel_id' => ['nullable', 'exists:hostels,id', 'required_if:audience,hostel'],
            'priority' => ['required', 'in:normal,important,urgent'],
            'publish_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
            'status' => ['required', 'in:draft,published,archived'],
            'notify_now' => ['nullable', 'boolean'],
        ]);
    }

    // Fans the notice out to every matching student's email/SMS.
    // Runs synchronously — fine for small/medium hostels; for large
    // rosters, dispatch this via a queued job instead.
    private function notifyStudentsAbout(Notice $notice, int $triggeredBy): void
    {
        $students = Student::where('status', 'active')
            ->when($notice->audience === 'hostel', function ($q) use ($notice) {
                $q->whereHas('currentAllocation.room.floor.block', fn ($q2) => $q2->where('hostel_id', $notice->hostel_id));
            })
            ->get();

        $message = "{$notice->title}\n\n{$notice->body}";

        foreach ($students as $student) {
            $this->notifications->notifyStudent($student, "Notice: {$notice->title}", $message, $notice, $triggeredBy);
        }
    }
}