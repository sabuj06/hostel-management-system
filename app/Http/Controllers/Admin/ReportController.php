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
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $summary = $this->buildSummary();
        $occupancy = $this->buildOccupancyByHostel();
        $revenueTrend = $this->buildRevenueTrend();
        $complaintsByStatus = $this->buildComplaintsByStatus();
        $complaintsByCategory = $this->buildComplaintsByCategory();
        $roomTypeBreakdown = $this->buildRoomTypeBreakdown();

        return view('reports.index', compact(
            'summary', 'occupancy', 'revenueTrend',
            'complaintsByStatus', 'complaintsByCategory', 'roomTypeBreakdown'
        ));
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
}