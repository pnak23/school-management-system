@extends('layouts.app')

@section('title', 'Library Analytics Dashboard - School Management System')

@push('styles')
<style>
    body {
        background-color: #f8fafc;
    }

    .kpi-card {
        border: none;
        border-radius: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.10);
    }

    .chart-container {
        background: linear-gradient(180deg, #ffffff, #f9fafb);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 8px 28px rgba(0,0,0,0.06);
        border: 1px solid #e5e7eb;
    }

    .chart-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 18px;
        color: #111827;
    }

    .no-data-message {
        text-align: center;
        padding: 60px;
        color: #9ca3af;
        font-style: italic;
        font-size: 15px;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 15px;
        margin-top: 4px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-5">
        <h1 class="text-3xl font-extrabold text-gray-900">
            Library Analytics Dashboard
        </h1>
        <p class="page-subtitle">
            Monitor library visits, sessions, and usage trends
        </p>
    </div>
    

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('charts.analytics.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="year" class="form-label">Year</label>
                    <select name="year" id="year" class="form-select">
                        @php
                            $currentYear = \Carbon\Carbon::now()->year;
                        @endphp
                        @for($y = $currentYear; $y >= $currentYear - 4; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="month" class="form-label">Month (Optional)</label>
                    <select name="month" id="month" class="form-select">
                        <option value="">All Months</option>
                        @php
                            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                        @endphp
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ $months[$m - 1] }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card kpi-card border-left-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-calendar-day text-primary fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Today Total Visits</div>
                            <div class="h4 mb-0">{{ number_format($kpi['today_total_visits']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card border-left-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded p-3">
                            <i class="fas fa-door-open text-warning fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Open Sessions Today</div>
                            <div class="h4 mb-0">{{ number_format($kpi['today_open_sessions']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card border-left-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success bg-opacity-10 rounded p-3">
                            <i class="fas fa-check-circle text-success fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Checked Out Today</div>
                            <div class="h4 mb-0">{{ number_format($kpi['today_checked_out']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card border-left-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-info bg-opacity-10 rounded p-3">
                            <i class="fas fa-calendar-alt text-info fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">This Month Visits</div>
                            <div class="h4 mb-0">{{ number_format($kpi['this_month_visits']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Line Chart - Visits by Month -->
        <div class="col-md-12 mb-4">
            <div class="chart-container">
                <div class="chart-title">Visits by Month (Trend)</div>
                <div id="chart-line"></div>
            </div>
        </div>

        <!-- Bar Chart - Visits by Session -->
        <div class="col-md-6 mb-4">
            <div class="chart-container">
                <div class="chart-title">Visits by Session</div>
                <div id="chart-bar"></div>
            </div>
        </div>

        <!-- Pie Chart - Visitor Type -->
        <div class="col-md-6 mb-4">
            <div class="chart-container">
                <div class="chart-title">Visitor Type Distribution</div>
                <div id="chart-pie"></div>
            </div>
        </div>

        <!-- Doughnut Chart - Today Sessions Status -->
        <div class="col-md-6 mb-4">
            <div class="chart-container">
                <div class="chart-title">Today Sessions Status</div>
                <div id="chart-doughnut"></div>
            </div>
        </div>

        <!-- Area Chart - Daily Visits (Last 30 Days) -->
        <div class="col-md-12 mb-4">
            <div class="chart-container">
                <div class="chart-title">Daily Visits (Last 30 Days)</div>
                <div id="chart-area"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    // Chart data from PHP
    const lineData = @json($line);
    const barData = @json($bar);
    const pieData = @json($pie);
    const doughnutData = @json($doughnut);
    const areaData = @json($area);

    // Helper function to check if data is empty
    function isEmptyData(series) {
        if (!series || !Array.isArray(series) || series.length === 0) return true;
        return series.every(s => !s.data || s.data.every(v => v === 0));
    }

    // Helper function to show no data message
    function showNoDataMessage(containerId) {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '<div class="no-data-message">No data available for the selected period</div>';
        }
    }

    // Initialize Line Chart
    function initLineChart() {
        const container = document.getElementById('chart-line');
        if (!container) return;

        if (isEmptyData(lineData.series)) {
            showNoDataMessage('chart-line');
            return;
        }

        const options = {
            series: lineData.series,
            chart: {
                type: 'line',
                height: 350,
                toolbar: { show: true }
            },
            xaxis: {
                categories: lineData.categories
            },
            title: {
                text: 'Monthly Visit Trends',
                align: 'left'
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            markers: {
                size: 5
            },
            colors: ['#3B82F6']
        };

        const chart = new ApexCharts(container, options);
        chart.render();
    }

    // Initialize Bar Chart
    function initBarChart() {
        const container = document.getElementById('chart-bar');
        if (!container) return;

        if (isEmptyData(barData.series)) {
            showNoDataMessage('chart-bar');
            return;
        }

        const options = {
            series: barData.series,
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: true }
            },
            xaxis: {
                categories: barData.categories
            },
            title: {
                text: 'Visits by Session Type',
                align: 'left'
            },
            colors: ['#10B981']
        };

        const chart = new ApexCharts(container, options);
        chart.render();
    }

    // Initialize Pie Chart
    function initPieChart() {
        const container = document.getElementById('chart-pie');
        if (!container) return;

        if (!pieData.series || pieData.series.every(v => v === 0)) {
            showNoDataMessage('chart-pie');
            return;
        }

        const options = {
            series: pieData.series,
            chart: {
                type: 'pie',
                height: 350
            },
            labels: pieData.categories,
            title: {
                text: 'User vs Guest Visits',
                align: 'left'
            },
            colors: ['#3B82F6', '#F59E0B'],
            legend: {
                position: 'bottom'
            }
        };

        const chart = new ApexCharts(container, options);
        chart.render();
    }

    // Initialize Doughnut Chart
    function initDoughnutChart() {
        const container = document.getElementById('chart-doughnut');
        if (!container) return;

        if (!doughnutData.series || doughnutData.series.every(v => v === 0)) {
            showNoDataMessage('chart-doughnut');
            return;
        }

        const options = {
            series: doughnutData.series,
            chart: {
                type: 'donut',
                height: 350
            },
            labels: doughnutData.categories,
            title: {
                text: 'Today\'s Session Status',
                align: 'left'
            },
            colors: ['#EF4444', '#10B981'],
            legend: {
                position: 'bottom'
            }
        };

        const chart = new ApexCharts(container, options);
        chart.render();
    }

    // Initialize Area Chart
    function initAreaChart() {
        const container = document.getElementById('chart-area');
        if (!container) return;

        if (isEmptyData(areaData.series)) {
            showNoDataMessage('chart-area');
            return;
        }

        const options = {
            series: areaData.series,
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: true }
            },
            xaxis: {
                categories: areaData.categories,
                labels: {
                    rotate: -45,
                    rotateAlways: true
                }
            },
            title: {
                text: 'Daily Visit Trends (Last 30 Days)',
                align: 'left'
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3
                }
            },
            colors: ['#8B5CF6']
        };

        const chart = new ApexCharts(container, options);
        chart.render();
    }

    // Initialize all charts when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initLineChart();
        initBarChart();
        initPieChart();
        initDoughnutChart();
        initAreaChart();
    });
</script>
@endpush

