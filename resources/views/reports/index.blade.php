@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics')

@push('styles')
<style>
    .stat-card .fs-4 { font-weight:700; }
    .chart-card { min-height: 340px; }
</style>
@endpush

@section('content')
<!-- Summary cards -->
<div class="row g-3 mb-4">
    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 stat-card">
            <div class="card-body">
                <div class="text-muted small">Active Students</div>
                <div class="fs-4">{{ $summary['total_students'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 stat-card">
            <div class="card-body">
                <div class="text-muted small">Occupancy Rate</div>
                <div class="fs-4 text-primary">{{ $summary['occupancy_rate'] }}%</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 stat-card">
            <div class="card-body">
                <div class="text-muted small">Total Revenue</div>
                <div class="fs-4 text-success">₹{{ number_format($summary['total_revenue'], 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 stat-card">
            <div class="card-body">
                <div class="text-muted small">Outstanding Dues</div>
                <div class="fs-4 text-danger">₹{{ number_format($summary['outstanding_dues'], 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 stat-card">
            <div class="card-body">
                <div class="text-muted small">Open Complaints</div>
                <div class="fs-4 text-warning">{{ $summary['open_complaints'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 stat-card">
            <div class="card-body">
                <div class="text-muted small">Visitors Today</div>
                <div class="fs-4">{{ $summary['visitors_today'] }}</div>
            </div>
        </div>
    </div>
</div>

<!-- AI Report Summary -->
<div class="card shadow-sm border-0 mb-3 border-start border-4 border-primary">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-stars text-primary"></i> AI Summary</h6>
            <button class="btn btn-sm btn-outline-primary" id="generate-summary-btn">Generate</button>
        </div>
        <p id="ai-summary-text" class="mb-0 mt-2 text-muted" style="display:none;"></p>
    </div>
</div>

<!-- Dashboard Insights (deterministic, always available) -->
<div class="row g-3 mb-3">
    @foreach($trendInsights as $insight)
    <div class="col-md-4">
        <div class="alert alert-{{ $insight['tone'] === 'good' ? 'success' : ($insight['tone'] === 'bad' ? 'warning' : 'secondary') }} mb-0">
            <i class="bi bi-{{ $insight['direction'] === 'up' ? 'arrow-up-circle' : ($insight['direction'] === 'down' ? 'arrow-down-circle' : 'dash-circle') }}"></i>
            {{ $insight['message'] }}
        </div>
    </div>
    @endforeach
</div>

<!-- Forecasting widgets -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-2"><i class="bi bi-graph-up"></i> Visitor Traffic Insight</h6>
                <p class="mb-1 small">Peak hour (last 30 days): <strong>{{ $visitorPeakHours['peak_hour_label'] }}</strong></p>
                <p class="mb-1 small">Daily average: <strong>{{ $visitorPeakHours['daily_average'] }}</strong> visitors/day &middot; Today: <strong>{{ $visitorPeakHours['today_count'] }}</strong></p>
                @if($visitorPeakHours['anomaly'])
                <div class="alert alert-warning small mb-0 mt-2"><i class="bi bi-exclamation-triangle"></i> {{ $visitorPeakHours['anomaly'] }}</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-2"><i class="bi bi-cup-hot"></i> Mess Demand Forecast</h6>
                <p class="mb-1 small">Expected diners for {{ $messForecast['date'] }}:</p>
                <div class="fs-3 fw-bold text-primary">{{ $messForecast['expected_diners'] }}</div>
                <p class="text-muted small mb-0">{{ $messForecast['active_students'] }} allocated students minus {{ $messForecast['mess_cuts'] }} on mess cut.</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 chart-card">
            <div class="card-body">
                <h6 class="mb-3">Bed Occupancy by Hostel</h6>
                <canvas id="occupancyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0 chart-card">
            <div class="card-body">
                <h6 class="mb-3">Revenue Collected (Last 6 Months)</h6>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 chart-card">
            <div class="card-body">
                <h6 class="mb-3">Complaints by Status</h6>
                <canvas id="complaintStatusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 chart-card">
            <div class="card-body">
                <h6 class="mb-3">Complaints by Category</h6>
                <canvas id="complaintCategoryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 chart-card">
            <div class="card-body">
                <h6 class="mb-3">Room Type Breakdown</h6>
                <canvas id="roomTypeChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        // AI Report Summary — this was missing before, causing the button to do nothing
        $('#generate-summary-btn').on('click', function () {
            const $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Generating...');

            $.ajax({
                url: '{{ route('reports.ai-summary') }}',
                method: 'POST',
                success: function (res) {
                    $('#ai-summary-text').text(res.summary).show();
                },
                error: function () {
                    $('#ai-summary-text').text('Could not generate summary right now. Please try again.').show();
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Generate');
                }
            });
        });

        const palette = ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc', '#858796', '#fd7e14'];

        // 1. Occupancy by hostel — stacked bar (occupied vs available)
        new Chart(document.getElementById('occupancyChart'), {
            type: 'bar',
            data: {
                labels: @json($occupancy['labels']),
                datasets: [
                    { label: 'Occupied', data: @json($occupancy['occupied']), backgroundColor: '#e74a3b' },
                    { label: 'Available', data: @json($occupancy['available']), backgroundColor: '#1cc88a' }
                ]
            },
            options: {
                responsive: true,
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
            }
        });

        // 2. Revenue trend — line chart
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: @json($revenueTrend['labels']),
                datasets: [{
                    label: 'Revenue (₹)',
                    data: @json($revenueTrend['data']),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78,115,223,0.15)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        // 3. Complaints by status — doughnut
        new Chart(document.getElementById('complaintStatusChart'), {
            type: 'doughnut',
            data: {
                labels: @json($complaintsByStatus['labels']),
                datasets: [{ data: @json($complaintsByStatus['data']), backgroundColor: palette }]
            },
            options: { responsive: true }
        });

        // 4. Complaints by category — horizontal bar
        new Chart(document.getElementById('complaintCategoryChart'), {
            type: 'bar',
            data: {
                labels: @json($complaintsByCategory['labels']),
                datasets: [{ label: 'Complaints', data: @json($complaintsByCategory['data']), backgroundColor: '#f6c23e' }]
            },
            options: { indexAxis: 'y', responsive: true, scales: { x: { beginAtZero: true } } }
        });

        // 5. Room type breakdown — pie
        new Chart(document.getElementById('roomTypeChart'), {
            type: 'pie',
            data: {
                labels: @json($roomTypeBreakdown['labels']),
                datasets: [{ data: @json($roomTypeBreakdown['data']), backgroundColor: palette }]
            },
            options: { responsive: true }
        });
    });
</script>
@endpush