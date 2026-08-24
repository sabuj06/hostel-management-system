<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\Notice;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Services\ComplaintAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentPortalController extends Controller
{
    // Resolve the Student profile linked to the currently logged-in user.
    // Throws a friendly error if an admin creates a login but forgets to link it.
    private function currentStudent(Request $request): Student
    {
        $student = Student::where('user_id', $request->user()->id)->first();

        if (! $student) {
            abort(
                403,
                'Your account is not linked to a student profile yet. Please contact the hostel admin.'
            );
        }

        return $student;
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard(Request $request)
    {
        $student = $this->currentStudent($request);

        $student->load(
            'currentAllocation.room',
            'currentAllocation.bed'
        );

        $stats = [
            'open_complaints' => Complaint::where('student_id', $student->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->count(),

            'unpaid_invoices' => $student->invoices()
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->count(),

            'total_due' => $student->invoices()->sum('amount')
                - $student->invoices()->sum('paid_amount'),
        ];

        $notices = Notice::published()
            ->where(
                fn ($q) => $q
                    ->where('audience', 'all')
                    ->orWhere('audience', 'students')
            )
            ->latest('publish_date')
            ->limit(5)
            ->get();

        return view(
            'student-portal.dashboard',
            compact('student', 'stats', 'notices')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    public function profile(Request $request)
    {
        $student = $this->currentStudent($request);

        return view(
            'student-portal.profile',
            compact('student')
        );
    }

    public function updateProfile(Request $request)
    {
        $student = $this->currentStudent($request);

        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ]);

        $student->update($data);

        return back()->with(
            'status',
            'Profile updated successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Invoices
    |--------------------------------------------------------------------------
    */

    public function invoices(Request $request)
    {
        $student = $this->currentStudent($request);

        $invoices = $student->invoices()
            ->with('payments')
            ->latest()
            ->paginate(10);

        return view(
            'student-portal.invoices',
            compact('invoices')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Complaints
    |--------------------------------------------------------------------------
    */

    public function complaints(Request $request)
    {
        $student = $this->currentStudent($request);

        $complaints = $student->complaints()
            ->with('category')
            ->latest()
            ->paginate(10);

        return view(
            'student-portal.complaints-index',
            compact('complaints')
        );
    }

    public function createComplaint(Request $request)
    {
        $categories = ComplaintCategory::all();

        return view(
            'student-portal.complaints-create',
            compact('categories')
        );
    }

    public function storeComplaint(
        Request $request,
        ComplaintAssistant $assistant
    ) {
        $student = $this->currentStudent($request);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        // Auto-classify complaint
        $suggestion = $assistant->analyze(
            $data['title'],
            $data['description']
        );

        $roomId = optional(
            $student->currentAllocation
        )->room_id;

        Complaint::create([
            'ticket_no' => 'TCK-'
                . now()->format('ymd')
                . '-'
                . Str::upper(Str::random(5)),

            'student_id' => $student->id,

            'complaint_category_id' =>
                $suggestion['suggested_category_id'],

            'room_id' => $roomId,

            'title' => $data['title'],

            'description' => $data['description'],

            'priority' =>
                $suggestion['suggested_priority'],

            'status' => 'open',
        ]);

        return redirect()
            ->route('student-portal.complaints')
            ->with(
                'status',
                'Complaint submitted successfully. The hostel team has been notified.'
            );
    }

    public function showComplaint(
        Request $request,
        Complaint $complaint
    ) {
        $student = $this->currentStudent($request);

        // Students can only view their own complaints
        if ($complaint->student_id !== $student->id) {
            abort(
                403,
                'You may only view your own complaints.'
            );
        }

        $complaint->load(
            'category',
            'assignedTo',
            'comments.user'
        );

        return view(
            'student-portal.complaints-show',
            compact('complaint')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    public function attendance(Request $request)
    {
        $student = $this->currentStudent($request);

        $records = \App\Models\Attendance::where(
            'student_id',
            $student->id
        )
            ->latest('date')
            ->paginate(30);

        $summary = [
            'present' => \App\Models\Attendance::where(
                'student_id',
                $student->id
            )
                ->where('status', 'present')
                ->count(),

            'absent' => \App\Models\Attendance::where(
                'student_id',
                $student->id
            )
                ->where('status', 'absent')
                ->count(),

            'on_leave' => \App\Models\Attendance::where(
                'student_id',
                $student->id
            )
                ->where('status', 'on_leave')
                ->count(),

            'late' => \App\Models\Attendance::where(
                'student_id',
                $student->id
            )
                ->where('is_late', true)
                ->count(),
        ];

        return view(
            'student-portal.attendance',
            compact('records', 'summary')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Leave Requests
    |--------------------------------------------------------------------------
    */

    public function leaveRequests(Request $request)
    {
        $student = $this->currentStudent($request);

        $leaveRequests = $student->leaveRequests()
            ->latest()
            ->paginate(10);

        return view(
            'student-portal.leave-requests-index',
            compact('leaveRequests')
        );
    }

    public function createLeaveRequest(Request $request)
    {
        return view(
            'student-portal.leave-requests-create'
        );
    }

    public function storeLeaveRequest(Request $request)
    {
        $student = $this->currentStudent($request);

        $data = $request->validate([
            'from_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],

            'to_date' => [
                'required',
                'date',
                'after_or_equal:from_date'
            ],

            'reason' => [
                'required',
                'string',
                'max:255'
            ],

            'details' => [
                'nullable',
                'string'
            ],
        ]);

        $student->leaveRequests()->create([
            ...$data,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('student-portal.leave-requests')
            ->with(
                'status',
                'Leave request submitted. Awaiting approval.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Meal Menu
    |--------------------------------------------------------------------------
    */

    public function mealMenu(Request $request)
    {
        $student = $this->currentStudent($request);

        $hostelId = optional(
            $student->currentAllocation
        )->room?->floor?->block?->hostel_id;

        $menus = \App\Models\MealMenu::where(
            'hostel_id',
            $hostelId
        )
            ->orWhereNull('hostel_id')
            ->get()
            ->groupBy('day_of_week')
            ->map(
                fn ($dayMenus) =>
                $dayMenus->keyBy('meal_type')
            );

        return view(
            'student-portal.meal-menu',
            compact('menus')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Mess Cuts
    |--------------------------------------------------------------------------
    */

    public function messCuts(Request $request)
    {
        $student = $this->currentStudent($request);

        $messCuts = $student->messCuts()
            ->latest()
            ->paginate(10);

        return view(
            'student-portal.mess-cuts-index',
            compact('messCuts')
        );
    }

    public function createMessCut(Request $request)
    {
        return view(
            'student-portal.mess-cuts-create'
        );
    }

    public function storeMessCut(Request $request)
    {
        $student = $this->currentStudent($request);

        $data = $request->validate([
            'from_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],

            'to_date' => [
                'required',
                'date',
                'after_or_equal:from_date'
            ],

            'reason' => [
                'nullable',
                'string',
                'max:255'
            ],
        ]);

        $student->messCuts()->create([
            ...$data,
            'marked_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('student-portal.mess-cuts')
            ->with(
                'status',
                'Mess cut submitted. It will be excluded from your next mess bill.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Student Documents
    |--------------------------------------------------------------------------
    */

    public function documents(Request $request)
    {
        $student = $this->currentStudent($request);

        $documents = $student->documents()
            ->latest()
            ->get();

        return view(
            'student-portal.documents',
            compact('documents')
        );
    }

    public function uploadDocument(Request $request)
    {
        $student = $this->currentStudent($request);

        $data = $request->validate([
            'document_type' => [
                'required',
                'in:nid,birth_certificate,photo,other',
            ],

            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Store uploaded file
        |--------------------------------------------------------------------------
        |
        | Files will be stored inside:
        | storage/app/public/student-documents/{student_id}/
        |
        */

        $path = $request->file('file')
            ->store(
                'student-documents/' . $student->id,
                'public'
            );

        /*
        |--------------------------------------------------------------------------
        | Create document record
        |--------------------------------------------------------------------------
        */

        StudentDocument::create([
            'student_id' => $student->id,

            'document_type' =>
                $data['document_type'],

            'file_path' => $path,

            'original_name' =>
                $request->file('file')->getClientOriginalName(),

            'status' => 'pending',
        ]);

        return redirect()
            ->route('student-portal.documents')
            ->with(
                'status',
                'Document uploaded successfully. Waiting for admin verification.'
            );
    }
}