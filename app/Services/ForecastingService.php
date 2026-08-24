<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Invoice;
use App\Models\MessCut;
use App\Models\Student;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;

/**
 * All methods here are rule-based statistics — no AI call, no external
 * dependency, so "Dashboard Insights" and forecasts always work and are
 * cheap to compute. ReportInsightService (separate class) optionally
 * turns this same data into AI-written prose on top of these numbers.
 */
class ForecastingService
{
    // Compares this month vs last month across a few key metrics and
    // returns plain-language, direction-flagged insight strings.
    public function trendInsights(): array
    {
        $insights = [];

        $thisMonthRevenue = DB::table('payments')->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year)->sum('amount');
        $lastMonthRevenue = DB::table('payments')->whereMonth('payment_date', now()->subMonth()->month)->whereYear('payment_date', now()->subMonth()->year)->sum('amount');
        $insights[] = $this->compare('Revenue collection', $thisMonthRevenue, $lastMonthRevenue, '₹');

        $thisMonthComplaints = Complaint::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $lastMonthComplaints = Complaint::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();
        $insights[] = $this->compare('New complaints', $thisMonthComplaints, $lastMonthComplaints, '', true); // rising complaints = bad, flip tone

        $thisMonthVisitors = Visitor::whereMonth('check_in_time', now()->month)->whereYear('check_in_time', now()->year)->count();
        $lastMonthVisitors = Visitor::whereMonth('check_in_time', now()->subMonth()->month)->whereYear('check_in_time', now()->subMonth()->year)->count();
        $insights[] = $this->compare('Visitor traffic', $thisMonthVisitors, $lastMonthVisitors, '');

        return array_filter($insights);
    }

    private function compare(string $label, float $current, float $previous, string $prefix = '', bool $increaseIsBad = false): ?array
    {
        if ($previous == 0 && $current == 0) {
            return null;
        }

        $change = $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 100;
        $direction = $current > $previous ? 'up' : ($current < $previous ? 'down' : 'flat');
        $isGood = $increaseIsBad ? $direction === 'down' : $direction === 'up';

        $arrow = $direction === 'up' ? '↑' : ($direction === 'down' ? '↓' : '→');
        $formattedCurrent = $prefix . number_format($current);

        return [
            'label' => $label,
            'message' => "{$label} is {$formattedCurrent} this month ({$arrow} " . abs($change) . '% vs last month).',
            'direction' => $direction,
            'tone' => $direction === 'flat' ? 'neutral' : ($isGood ? 'good' : 'bad'),
        ];
    }

    // Which hour of day sees the most visitors, plus a same-day anomaly flag
    public function visitorPeakHours(int $daysBack = 30): array
    {
        $since = now()->subDays($daysBack);

        $hourly = Visitor::where('check_in_time', '>=', $since)
            ->selectRaw('HOUR(check_in_time) as hour, count(*) as total')
            ->groupBy('hour')
            ->orderByDesc('total')
            ->get();

        $peakHour = $hourly->first();
        $dailyAverage = $daysBack > 0 ? round(Visitor::where('check_in_time', '>=', $since)->count() / $daysBack, 1) : 0;
        $todayCount = Visitor::whereDate('check_in_time', now())->count();

        $anomaly = $dailyAverage > 0 && $todayCount > ($dailyAverage * 2)
            ? "Unusually high visitor activity today ({$todayCount}, more than double the {$dailyAverage}/day average). Consider extra gate vigilance."
            : null;

        return [
            'peak_hour' => $peakHour ? (int) $peakHour->hour : null,
            'peak_hour_label' => $peakHour ? $this->formatHour((int) $peakHour->hour) : 'No data yet',
            'daily_average' => $dailyAverage,
            'today_count' => $todayCount,
            'anomaly' => $anomaly,
        ];
    }

    private function formatHour(int $hour): string
    {
        return \Carbon\Carbon::createFromTime($hour)->format('g A') . ' - ' . \Carbon\Carbon::createFromTime($hour)->addHour()->format('g A');
    }

    // Expected diners tomorrow = active allocated students minus students
    // with an approved mess cut covering tomorrow's date.
    public function messDemandForecast(?int $hostelId = null): array
    {
        $tomorrow = now()->addDay()->startOfDay();

        $activeStudents = Student::where('status', 'active')
            ->whereHas('currentAllocation')
            ->when($hostelId, fn ($q) => $q->whereHas('currentAllocation.room.floor.block', fn ($q2) => $q2->where('hostel_id', $hostelId)))
            ->count();

        $cutTomorrow = MessCut::where('from_date', '<=', $tomorrow)->where('to_date', '>=', $tomorrow)->count();

        $expected = max(0, $activeStudents - $cutTomorrow);

        return [
            'date' => $tomorrow->format('d M Y'),
            'active_students' => $activeStudents,
            'mess_cuts' => $cutTomorrow,
            'expected_diners' => $expected,
        ];
    }
}