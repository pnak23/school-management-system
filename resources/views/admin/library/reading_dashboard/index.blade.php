@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-chart-line text-primary"></i> Reading Dashboard</h1>
            <p class="text-muted mb-0">Analytics & Statistics for In-Library Reading</p>
        </div>
        <div>
            <button class="btn btn-sm btn-outline-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <!-- Date Range -->
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calendar"></i> Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calendar"></i> Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ date('Y-m-d') }}">
                </div>

                <!-- Visitor Type -->
                <div class="col-md-2">
                    <label class="form-label"><i class="fas fa-user"></i> Visitor Type</label>
                    <select class="form-select" id="visitor_type" name="visitor_type">
                        <option value="all">All</option>
                        <option value="user">Users Only</option>
                        <option value="guest">Guests Only</option>
                    </select>
                </div>

                <!-- Session -->
                <div class="col-md-2">
                    <label class="form-label"><i class="fas fa-sun"></i> Session</label>
                    <select class="form-select" id="session" name="session">
                        <option value="all">All</option>
                        <option value="morning">Morning</option>
                        <option value="afternoon">Afternoon</option>
                        <option value="evening">Evening</option>
                    </select>
                </div>

                <!-- Purpose -->
                <div class="col-md-2">
                    <label class="form-label"><i class="fas fa-bullseye"></i> Purpose</label>
                    <select class="form-select" id="purpose" name="purpose">
                        <option value="all">All</option>
                        <option value="read">Read</option>
                        <option value="study">Study</option>
                        <option value="borrow">Borrow</option>
                        <option value="return">Return</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Quick Date Buttons -->
                <div class="col-md-12">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('today')">
                        Today
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('week')">
                        This Week
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('month')">
                        This Month
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="applyFilters()">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <!-- Total Reading Minutes -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Reading Minutes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalMinutes">
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>
                            <small class="text-muted" id="totalHours"></small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Sessions -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Reading Sessions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="sessionsCount">
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currently Reading -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Currently Reading
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="openSessions">
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>
                            <small class="text-muted">Active now</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-pulse fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average per Session -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg per Session
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="avgMinutes">
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>
                            <small class="text-muted">Minutes</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-chart-area"></i> Reading Trend</h5>
            <div>
                <button class="btn btn-sm btn-light" onclick="loadChart('daily')" id="btnDaily">
                    Last 7 Days
                </button>
                <button class="btn btn-sm btn-outline-light" onclick="loadChart('monthly')" id="btnMonthly">
                    This Month
                </button>
            </div>
        </div>
        <div class="card-body">
            <canvas id="readingChart" height="80"></canvas>
        </div>
    </div>

    <!-- Top Books Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-trophy"></i> Top 10 Books Read</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="topBooksTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">Rank</th>
                            <th width="40%">Book Title</th>
                            <th width="15%">ISBN</th>
                            <th width="15%">Minutes</th>
                            <th width="10%">Hours</th>
                            <th width="15%">Sessions</th>
                        </tr>
                    </thead>
                    <tbody id="topBooksBody">
                        <tr>
                            <td colspan="6" class="text-center">
                                <span class="spinner-border spinner-border-sm"></span> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }
    .border-left-success {
        border-left: 4px solid #1cc88a !important;
    }
    .border-left-warning {
        border-left: 4px solid #f6c23e !important;
    }
    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }
    .text-xs {
        font-size: 0.7rem;
    }
</style>
@endpush

@push('scripts')
<!-- jQuery (if not already loaded) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let readingChart = null;
    let currentChartMode = 'daily';

    // On page load (using jQuery)
    $(document).ready(function() {
        console.log('Dashboard loaded, jQuery version:', $.fn.jquery);
        loadSummary();
        loadChart('daily');
    });

    // Apply filters
    function applyFilters() {
        loadSummary();
        loadChart(currentChartMode);
    }

    // Refresh dashboard
    function refreshDashboard() {
        loadSummary();
        loadChart(currentChartMode);
    }

    // Set date range shortcuts
    function setDateRange(range) {
        const today = new Date();
        let dateFrom, dateTo;

        if (range === 'today') {
            dateFrom = dateTo = formatDate(today);
        } else if (range === 'week') {
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - today.getDay()); // Sunday
            dateFrom = formatDate(weekStart);
            dateTo = formatDate(today);
        } else if (range === 'month') {
            const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
            dateFrom = formatDate(monthStart);
            dateTo = formatDate(today);
        }

        $('#date_from').val(dateFrom);
        $('#date_to').val(dateTo);
        applyFilters();
    }

    // Format date to Y-m-d
    function formatDate(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    // Load summary data
    function loadSummary() {
        const filters = {
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val(),
            visitor_type: $('#visitor_type').val(),
            session: $('#session').val(),
            purpose: $('#purpose').val(),
        };

        $.ajax({
            url: '{{ route('admin.library.reading-dashboard.summary') }}',
            method: 'GET',
            data: filters,
            success: function(response) {
                if (response.success) {
                    const data = response.data;

                    // Update cards
                    $('#totalMinutes').text(data.total_minutes.toLocaleString() + ' min');
                    $('#totalHours').text('(' + data.total_hours + ' hours)');
                    $('#sessionsCount').text(data.sessions_count);
                    $('#openSessions').text(data.open_sessions_now);

                    // Calculate average
                    const avg = data.sessions_count > 0 
                        ? Math.round(data.total_minutes / data.sessions_count) 
                        : 0;
                    $('#avgMinutes').text(avg);

                    // Update top books table
                    updateTopBooksTable(data.top_books);
                }
            },
            error: function(xhr) {
                console.error('Failed to load summary:', xhr);
                $('#totalMinutes').text('Error');
                $('#sessionsCount').text('Error');
                $('#openSessions').text('Error');
                $('#avgMinutes').text('Error');
            }
        });
    }

    // Update top books table
    function updateTopBooksTable(books) {
        const tbody = $('#topBooksBody');
        tbody.empty();

        if (books.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        <i class="fas fa-inbox"></i> No reading data for selected period
                    </td>
                </tr>
            `);
            return;
        }

        books.forEach((book, index) => {
            const rank = index + 1;
            const medalClass = rank === 1 ? 'text-warning' : rank === 2 ? 'text-secondary' : rank === 3 ? 'text-danger' : '';
            const medalIcon = rank <= 3 ? '<i class="fas fa-medal ' + medalClass + '"></i>' : rank;

            tbody.append(`
                <tr>
                    <td class="text-center">${medalIcon}</td>
                    <td><strong>${escapeHtml(book.title)}</strong></td>
                    <td>${book.isbn}</td>
                    <td class="text-end">${book.total_minutes.toLocaleString()}</td>
                    <td class="text-end">${book.total_hours}</td>
                    <td class="text-center">
                        <span class="badge bg-primary">${book.sessions_count}</span>
                    </td>
                </tr>
            `);
        });
    }

    // Load chart
    function loadChart(mode) {
        currentChartMode = mode;

        // Update button states
        if (mode === 'daily') {
            $('#btnDaily').removeClass('btn-outline-light').addClass('btn-light');
            $('#btnMonthly').removeClass('btn-light').addClass('btn-outline-light');
        } else {
            $('#btnMonthly').removeClass('btn-outline-light').addClass('btn-light');
            $('#btnDaily').removeClass('btn-light').addClass('btn-outline-light');
        }

        const filters = {
            mode: mode,
            visitor_type: $('#visitor_type').val(),
            session: $('#session').val(),
            purpose: $('#purpose').val(),
        };

        $.ajax({
            url: '{{ route('admin.library.reading-dashboard.chart') }}',
            method: 'GET',
            data: filters,
            success: function(response) {
                if (response.success) {
                    renderChart(response.data);
                }
            },
            error: function(xhr) {
                console.error('Failed to load chart:', xhr);
            }
        });
    }

    // Render chart
    function renderChart(data) {
        const ctx = document.getElementById('readingChart').getContext('2d');

        // Destroy existing chart
        if (readingChart) {
            readingChart.destroy();
        }

        // Create new chart
        readingChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Reading Minutes',
                        data: data.minutes,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Sessions Count',
                        data: data.sessions,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    if (context.datasetIndex === 0) {
                                        label += context.parsed.y + ' min';
                                    } else {
                                        label += context.parsed.y + ' sessions';
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Minutes'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Sessions'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                }
            }
        });
    }

    // Helper: Escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
</script>
@endpush

