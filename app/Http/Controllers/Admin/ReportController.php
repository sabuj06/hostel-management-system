<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Complaint;
use App\Models\Hostel;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\Student;
use App\Models\Visitor;
use App\Services\ForecastingService;
use App\Services\ReportInsightService;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(private ForecastingService $forecasting)
    {
    }

    public function index()
    {
        $summary = $this->buildSummary();
        $occupancy = $this->buildOccupancyByHostel();
        $revenueTrend = $this->buildRevenueTrend();
        $complaintsByStatus = $this->buildComplaintsByStatus();
        $complaintsByCategory = $this->buildComplaintsByCategory();
        $roomTypeBreakdown = $this->buildRoomTypeBreakdown();
        $trendInsights = $this->forecasting->trendInsights();
        $visitorPeakHours = $this->forecasting->visitorPeakHours();
        $messForecast = $this->forecasting->messDemandForecast();
        $attendanceSummary = $this->buildAttendanceSummary();
        return view('reports.index', compact(
            'summary', 'occupancy', 'revenueTrend',
            'complaintsByStatus', 'complaintsByCategory', 'roomTypeBreakdown',
            'trendInsights', 'visitorPeakHours', 'messForecast','attendanceSummary'
        ));
    }

    // AJAX: AI-written natural-language summary of the current dashboard state
    public function aiSummary(Request $request, ReportInsightService $insightService)
    {
        $summary = $this->buildSummary();
        $trendInsights = $this->forecasting->trendInsights();

        return response()->json(['summary' => $insightService->summarize($summary, $trendInsights)]);
    }

    private function buildSummary(): array
    {
        $totalBeds = Bed::count();
        $occupiedBeds = Bed::where('status', 'occupied')->count();

        return [
            'total_students' => Student::where('status', 'active')->count(),
            'occupancy_rate' => $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0,
            'total_revenue' => Invoice::sum('paid_amount'),
            'outstanding_dues' => Invoice::sum('amount') - Invoice::sum('paid_amount'),
            'open_complaints' => Complaint::whereIn('status', ['open', 'in_progress'])->count(),
            'visitors_today' => Visitor::whereDate('check_in_time', now())->count(),
        ];
    }

    // Bar chart: occupied vs available beds per hostel
    private function buildOccupancyByHostel(): array
    {
        $hostels = Hostel::with('blocks.floors.rooms.beds')->active()->get();

        $labels = [];
        $occupied = [];
        $available = [];

        foreach ($hostels as $hostel) {
            $beds = $hostel->blocks->flatMap->floors->flatMap->rooms->flatMap->beds;
            $labels[] = $hostel->name;
            $occupied[] = $beds->where('status', 'occupied')->count();
            $available[] = $beds->where('status', 'available')->count();
        }

        return compact('labels', 'occupied', 'available');
    }

    // Line chart: paid amount collected per month, last 6 months
    private function buildRevenueTrend(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        $labels = $months->map(fn ($m) => $m->format('M Y'))->toArray();

        $data = $months->map(function ($month) {
            return (float) DB::table('payments')
                ->whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('amount');
        })->toArray();

        return compact('labels', 'data');
    }

    // Doughnut chart: complaint counts by status
    private function buildComplaintsByStatus(): array
    {
        $rows = Complaint::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = ['open', 'in_progress', 'resolved', 'closed', 'rejected'];
        $data = collect($labels)->map(fn ($s) => $rows[$s] ?? 0)->toArray();

        return ['labels' => array_map(fn ($l) => ucfirst(str_replace('_', ' ', $l)), $labels), 'data' => $data];
    }

    // Bar chart: complaint counts by category
    private function buildComplaintsByCategory(): array
    {
        $rows = Complaint::join('complaint_categories', 'complaints.complaint_category_id', '=', 'complaint_categories.id')
            ->select('complaint_categories.name', DB::raw('count(*) as total'))
            ->groupBy('complaint_categories.name')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->pluck('name')->toArray(),
            'data' => $rows->pluck('total')->toArray(),
        ];
    }

    // Pie chart: room count by type
    private function buildRoomTypeBreakdown(): array
    {
        $rows = Room::select('room_type', DB::raw('count(*) as total'))
            ->groupBy('room_type')
            ->pluck('total', 'room_type');

        return [
            'labels' => $rows->keys()->map(fn ($k) => ucfirst($k))->toArray(),
            'data' => $rows->values()->toArray(),
        ];

    }

        // Attendance report: last 30 days overview + per-student percentage,
    // sorted so the students with the worst attendance surface first —
    // that is the list a warden actually needs to act on.
    private function buildAttendanceSummary(): array
    {
        $from = now()->subDays(29)->format('Y-m-d');
        $to = now()->format('Y-m-d');

        $rows = Attendance::whereBetween('date', [$from, $to])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $overview = [
            'present' => $rows['present'] ?? 0,
            'absent' => $rows['absent'] ?? 0,
            'on_leave' => $rows['on_leave'] ?? 0,
            'late' => Attendance::whereBetween('date', [$from, $to])->where('is_late', true)->count(),
        ];

        // Per-student attendance % over the same window, active students only
        $students = Student::where('status', 'active')
            ->withCount([
                'attendances as present_count' => fn ($q) => $q->whereBetween('date', [$from, $to])->where('status', 'present'),
                'attendances as marked_count' => fn ($q) => $q->whereBetween('date', [$from, $to]),
            ])
            ->get()
            ->map(function ($student) {
                $percentage = $student->marked_count > 0
                    ? round(($student->present_count / $student->marked_count) * 100, 1)
                    : null;

                return [
                    'name' => $student->name,
                    'student_uid' => $student->student_uid,
                    'present_count' => $student->present_count,
                    'marked_count' => $student->marked_count,
                    'percentage' => $percentage,
                ];
            })
            ->filter(fn ($s) => $s['marked_count'] > 0)
            ->sortBy('percentage')
            ->values()
            ->take(10)
            ->toArray();

        return [
            'from' => $from,
            'to' => $to,
            'overview' => $overview,
            'low_attendance_students' => $students,
        ];
    }
}