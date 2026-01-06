<!-- Trends Chart Section -->
<div class="card mb-3" id="loanTrendsCard">
    <div class="card-header bg-light">
        <div class="row align-items-center">
            <div class="col-md-4">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line"></i> Loan Trends
                </h5>
            </div>
            <div class="col-md-8">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <select id="dashboardPeriod" class="form-select form-select-sm">
                            <option value="week">Weekly (Last 8 Weeks)</option>
                            <option value="month">Monthly (Last 12 Months)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="dashboardBorrowerType" class="form-select form-select-sm">
                            <option value="">All Borrower Types</option>
                            <option value="student">Student Only</option>
                            <option value="teacher">Teacher Only</option>
                            <option value="staff">Staff Only</option>
                            <option value="guest">Guest Only</option>
                        </select>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleChartBtn" onclick="toggleChart()">
                            <i class="fas fa-eye-slash"></i> Hide Chart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body" id="chartContainer">
        <canvas id="loansTrendChart" height="100"></canvas>
    </div>
</div>

@push('scripts')
<script>
// ========================================
// CHART VISIBILITY TOGGLE
// ========================================

// Initialize chart visibility from localStorage
function initChartVisibility() {
    const chartVisible = localStorage.getItem('loanChartVisible');
    
    // Default to visible if not set
    if (chartVisible === 'false') {
        hideChartContent();
    } else {
        showChartContent();
    }
}

// Toggle chart visibility
function toggleChart() {
    const chartContainer = $('#chartContainer');
    const isVisible = chartContainer.is(':visible');
    
    if (isVisible) {
        hideChartContent();
    } else {
        showChartContent();
    }
}

// Hide chart content
function hideChartContent() {
    $('#chartContainer').slideUp(300);
    $('#toggleChartBtn').html('<i class="fas fa-eye"></i> Show Chart');
    localStorage.setItem('loanChartVisible', 'false');
}

// Show chart content
function showChartContent() {
    $('#chartContainer').slideDown(300);
    $('#toggleChartBtn').html('<i class="fas fa-eye-slash"></i> Hide Chart');
    localStorage.setItem('loanChartVisible', 'true');
    
    // Refresh chart if it was hidden
    if (typeof fetchLoanTrends === 'function') {
        setTimeout(() => fetchLoanTrends(), 350);
    }
}

// Initialize on page load
$(document).ready(function() {
    initChartVisibility();
});
</script>
@endpush









