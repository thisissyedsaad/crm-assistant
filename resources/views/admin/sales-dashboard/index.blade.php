@extends('admin.layouts.app')

@section('title', 'Sales Target Dashboard | CSD Assistant')

@push('links')
<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- DataTables CSS -->
<link rel="stylesheet" href="{{ asset('assets/admin/css/dataTables.bootstrap5.min.css') }}">
<style>
/* Stats Cards - Compact Horizontal Layout */
.stats-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 10px;
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
    transform: translateY(20px);
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
}

.stats-card .card-body {
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stats-card .stats-icon {
    width: 50px;
    height: 50px;
    min-width: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 0;
}

.stats-card .stats-content {
    flex: 1;
    text-align: right;
}

.stats-card .stats-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.1rem;
    line-height: 1.2;
}

.stats-card .stats-label {
    color: #6c757d;
    font-size: 0.75rem;
    font-weight: 500;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Card Color Themes */
.stats-card.blue {
    border-left: 4px solid #007bff;
}
.stats-card.blue .stats-icon {
    background: rgba(0, 123, 255, 0.1);
    color: #007bff;
}
.stats-card.blue .stats-value {
    color: #007bff;
}

.stats-card.green {
    border-left: 4px solid #28a745;
}
.stats-card.green .stats-icon {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}
.stats-card.green .stats-value {
    color: #28a745;
}

.stats-card.red {
    border-left: 4px solid #dc3545;
}
.stats-card.red .stats-icon {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}
.stats-card.red .stats-value {
    color: #dc3545;
}

.stats-card.yellow {
    border-left: 4px solid #ffc107;
}
.stats-card.yellow .stats-icon {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}
.stats-card.yellow .stats-value {
    color: #856404;
}

.stats-card.gray {
    border-left: 4px solid #6c757d;
}
.stats-card.gray .stats-icon {
    background: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}
.stats-card.gray .stats-value {
    color: #6c757d;
}

/* Animation delays for stats cards */
.stats-card:nth-child(1) { animation-delay: 0.1s; }
.stats-card:nth-child(2) { animation-delay: 0.2s; }
.stats-card:nth-child(3) { animation-delay: 0.3s; }
.stats-card:nth-child(4) { animation-delay: 0.4s; }
.stats-card:nth-child(5) { animation-delay: 0.5s; }
.stats-card:nth-child(6) { animation-delay: 0.6s; }
.stats-card:nth-child(7) { animation-delay: 0.7s; }
.stats-card:nth-child(8) { animation-delay: 0.8s; }

/* Purple theme for new cards */
.stats-card.purple {
    border-left: 4px solid #6f42c1;
}
.stats-card.purple .stats-icon {
    background: rgba(111, 66, 193, 0.1);
    color: #6f42c1;
}
.stats-card.purple .stats-value {
    color: #6f42c1;
}

/* Cyan/Teal theme */
.stats-card.cyan {
    border-left: 4px solid #17a2b8;
}
.stats-card.cyan .stats-icon {
    background: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}
.stats-card.cyan .stats-value {
    color: #17a2b8;
}

/* Orange theme */
.stats-card.orange {
    border-left: 4px solid #fd7e14;
}
.stats-card.orange .stats-icon {
    background: rgba(253, 126, 20, 0.1);
    color: #fd7e14;
}
.stats-card.orange .stats-value {
    color: #fd7e14;
}

/* New/Existing card special styling */
.stats-card .stats-value-split {
    font-size: 1.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.2rem;
    line-height: 1.2;
    margin-bottom: 0.1rem;
}
.stats-card .stats-value-split .new-count {
    color: #28a745;
}
.stats-card .stats-value-split .separator {
    color: #6c757d;
}
.stats-card .stats-value-split .existing-count {
    color: #007bff;
}

/* Team Performance Table */
.performance-table {
    border-radius: 12px;
    overflow: hidden;
}

.performance-table .table {
    margin-bottom: 0;
}

.performance-table th {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem 0.75rem;
    white-space: nowrap;
}

.performance-table td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
}

.target-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
}

.target-badge.total { background: rgba(0, 123, 255, 0.1); color: #007bff; }
.target-badge.new { background: rgba(40, 167, 69, 0.1); color: #28a745; }
.target-badge.existing { background: rgba(255, 193, 7, 0.1); color: #856404; }

.off-target-positive { color: #28a745; font-weight: 600; }
.off-target-negative { color: #dc3545; font-weight: 600; }

/* Progress Bar */
.progress-mini {
    height: 20px;
    border-radius: 10px;
    background: #e9ecef;
    overflow: hidden;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    position: relative;
}

.progress-mini .progress-bar {
    height: 100%;
    border-radius: 10px;
    transition: width 0.6s ease;
    display: block;
    min-height: 20px;
}

/* Progress bar color variations - using background-color for better compatibility */
.progress-mini .progress-bar.bg-success {
    background: #28a745 !important;
    background: linear-gradient(90deg, #28a745, #20c997) !important;
}

.progress-mini .progress-bar.bg-warning {
    background: #ffc107 !important;
    background: linear-gradient(90deg, #ffc107, #fd7e14) !important;
}

.progress-mini .progress-bar.bg-danger {
    background: #dc3545 !important;
    background: linear-gradient(90deg, #dc3545, #e74c3c) !important;
}

/* Chart Cards */
.chart-card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.chart-card .card-header {
    background: #fff;
    border-bottom: 1px solid #f0f0f0;
    padding: 1rem 1.25rem;
}

.chart-card .card-header h5 {
    margin: 0;
    font-weight: 600;
    font-size: 1rem;
    color: #495057;
}

.chart-card .card-body {
    padding: 1.25rem;
}

/* Date Range Picker */
.date-range-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}

#daterange {
    width: 250px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    cursor: pointer;
}

#daterange:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: none;
}

/* Configuration Warning */
.config-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
}

.config-warning i {
    font-size: 3rem;
    color: #856404;
    margin-bottom: 1rem;
}

.config-warning h4 {
    color: #856404;
    margin-bottom: 0.5rem;
}

.config-warning p {
    color: #6c757d;
    margin-bottom: 1rem;
}

.config-warning code {
    background: #fff;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    display: block;
    margin-top: 0.5rem;
}

/* Cache Info Badge */
.cache-info {
    font-size: 0.75rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cache-info i {
    color: #28a745;
}

/* Animations */
@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .date-range-container {
        flex-direction: column;
        align-items: stretch;
    }

    #daterange {
        width: 100%;
    }

    .stats-card .card-body {
        padding: 0.875rem 1rem;
    }

    .stats-card .stats-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        font-size: 1.25rem;
    }

    .stats-card .stats-value {
        font-size: 1.25rem;
    }

    .stats-card .stats-value-split {
        font-size: 1.25rem;
    }

    .stats-card .stats-label {
        font-size: 0.7rem;
    }

    .performance-table {
        overflow-x: auto;
    }
}
</style>
@endpush

@section('content')
<!-- Main Content Area -->
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="dashboard-area">
            <div class="container-fluid">

                <!-- Page Header with Date Range -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card" style="border-radius: 12px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div>
                                        <h4 class="mb-1" style="font-weight: 600;">Sales Target Dashboard</h4>
                                        <div class="cache-info">
                                            <i class="bx bx-time-five"></i>
                                            <span>Auto-refresh every {{ $cacheTtlMinutes }} minutes</span>
                                        </div>
                                    </div>
                                    <div class="date-range-container">
                                        <input type="text" id="daterange" class="form-control"
                                               value="{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}" />
                                        <button class="btn btn-primary" id="refreshBtn" style="border-radius: 8px;">
                                            <i class="bx bx-refresh"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(!$isConfigured)
                <!-- Configuration Warning -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="config-warning">
                            <i class="bx bx-error-circle"></i>
                            <h4>Google Sheets Not Configured</h4>
                            <p>Please configure Google Sheets API to display dashboard data.</p>
                            <div class="text-start" style="max-width: 600px; margin: 0 auto;">
                                <p class="mb-2"><strong>Required steps:</strong></p>
                                <ol class="text-start" style="font-size: 0.9rem;">
                                    <li>Create a Google Cloud Project and enable Sheets API</li>
                                    <li>Create a Service Account and download JSON credentials</li>
                                    <li>Place credentials file at: <code>storage/app/google-credentials.json</code></li>
                                    <li>Share your Google Sheet with the service account email</li>
                                    <li>Add to your .env file:</li>
                                </ol>
                                <code>
                                    GOOGLE_CREDENTIALS_PATH=storage/app/google-credentials.json<br>
                                    GOOGLE_SHEETS_ID=your_sheet_id_here<br>
                                    GOOGLE_SHEETS_CACHE_TTL=300
                                </code>
                            </div>
                        </div>
                    </div>
                </div>
                @else

                <!-- Stats Cards Row 1 -->
                <div class="row g-3 mb-3">
                    <!-- Orders Completed -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card stats-card green">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="bx bx-check-circle"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value" id="ordersDone">{{ $stats['orders_done'] ?? 0 }}</div>
                                    <p class="stats-label">ORDERS COMPLETED</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- # of New/Existing -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card stats-card purple">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="bx bx-transfer-alt"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value-split">
                                        <span class="new-count">{{ $stats['new_orders_count'] ?? 0 }}</span>
                                        <span class="separator">/</span>
                                        <span class="existing-count">{{ $stats['existing_orders_count'] ?? 0 }}</span>
                                    </div>
                                    <p class="stats-label"># OF NEW/EXISTING</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversion Rate -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card stats-card gray">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="bx bx-percentage"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value" id="conversionRate">{{ $stats['conversion_rate'] ?? 0 }}%</div>
                                    <p class="stats-label">CONV. RATE</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Needed -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card stats-card {{ ($stats['orders_needed'] ?? 0) > 0 ? 'orange' : 'green' }}">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="bx bx-{{ ($stats['orders_needed'] ?? 0) > 0 ? 'error' : 'check-double' }}"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value" id="ordersNeeded">{{ $stats['orders_needed'] ?? 0 }}</div>
                                    <p class="stats-label">ORDERS NEEDED</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards Row 2 -->
                <div class="row g-3 mb-4">
                    <!-- Total Target -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card stats-card blue">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="bx bx-target-lock"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value" id="totalTarget">{{ $stats['total_target'] ?? 0 }}</div>
                                    <p class="stats-label">TOTAL TARGET</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Off Target -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card stats-card {{ ($stats['off_target'] ?? 0) >= 0 ? 'green' : 'red' }}">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="bx bx-trending-{{ ($stats['off_target'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value" id="offTarget">{{ ($stats['off_target'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['off_target'] ?? 0 }}</div>
                                    <p class="stats-label">OFF TARGET</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- # of Insurance Sold -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card stats-card cyan">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="bx bx-shield-plus"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value" id="insuranceSold">{{ $stats['insurance_sold_count'] ?? 0 }}</div>
                                    <p class="stats-label"># OF INSURANCE SOLD</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Drivers Cost Saved -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card stats-card yellow">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="bx bx-pound"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value" id="driversCostSaved">£{{ number_format($stats['drivers_cost_saved_total'] ?? 0, 2) }}</div>
                                    <p class="stats-label">DRIVERS COST SAVED</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Performance Table -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card performance-table">
                            <div class="card-header d-flex justify-content-between align-items-center" style="background: #fff; border-bottom: 1px solid #f0f0f0;">
                                <h5 class="mb-0" style="font-weight: 600;">Team Performance</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="performanceTable">
                                        <thead>
                                            <tr>
                                                <th>Staff Member</th>
                                                <th class="text-center">Target (M/N/E)</th>
                                                <th class="text-center">Actual (M/N/E)</th>
                                                <th class="text-center">Off Target </th>
                                                <th class="text-center" style="min-width: 150px;">Progress %</th>
                                                <!-- <th class="text-center">Conv. %</th> -->
                                                <!-- <th class="text-center" style="min-width: 150px;">Conv. Rate (New Business)</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($teamPerformance as $member)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.staff-sales-dashboard.index', ['user_id' => $member['user_id']]) }}"
                                                       class="text-decoration-none"
                                                       title="View {{ $member['name'] }}'s Dashboard">
                                                        <strong class="text-primary" style="cursor: pointer;">{{ $member['name'] }}</strong>
                                                        <i class="bx bx-link-external ms-1" style="font-size: 0.8rem;"></i>
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <span class="target-badge total">{{ $member['target_total'] }}</span>
                                                    <span class="target-badge new">{{ $member['target_new'] }}</span>
                                                    <span class="target-badge existing">{{ $member['target_existing'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="target-badge total">{{ $member['actual_total'] }}</span>
                                                    <span class="target-badge new">{{ $member['actual_new'] }}</span>
                                                    <span class="target-badge existing">{{ $member['actual_existing'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        // Off Target (Range) = Orders Converted − Expected Orders
                                                        // Negative = behind target, Positive = ahead of target
                                                        $offTarget = $member['off_target'];
                                                        $expectedRange = $member['expected_range'] ?? 0;
                                                    @endphp
                                                    <span class="{{ $offTarget >= 0 ? 'off-target-positive' : 'off-target-negative' }}"
                                                          title="Expected: {{ $expectedRange }} | Actual: {{ $member['actual_total'] }}">
                                                        {{ $offTarget >= 0 ? '+' : '' }}{{ $offTarget }}
                                                    </span>
                                                    <small class="d-block text-muted" style="font-size: 0.7rem;">
                                                        ({{ $member['actual_total'] }}/{{ $expectedRange }} exp.)
                                                    </small>
                                                </td>
                                                <td>
                                                    @php
                                                        // Progress (%) = Orders Converted ÷ Expected Orders × 100
                                                        $onTargetPercent = $member['on_target_percent'] ?? 0;
                                                        $progressBarWidth = min(100, $onTargetPercent); // Cap bar at 100%
                                                        $progressClass = $onTargetPercent >= 100 ? 'bg-success' : ($onTargetPercent >= 50 ? 'bg-warning' : 'bg-danger');
                                                    @endphp
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span style="font-size: 0.85rem; font-weight: 600; min-width: 45px;">{{ $onTargetPercent }}%</span>
                                                        <div class="progress-mini flex-grow-1" style="min-width: 100px;">
                                                            <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $progressBarWidth }}%; min-width: {{ $progressBarWidth > 0 ? '5px' : '0' }};" aria-valuenow="{{ $onTargetPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <!-- <td>
                                                    @php
                                                        // Conv. Rate (New Business) = (New Orders ÷ Total Orders) × 100
                                                        $newBusinessRate = $member['new_business_rate'] ?? 0;
                                                        $convBarWidth = min(100, $newBusinessRate); // Cap bar at 100%
                                                        // Color based on new business rate: higher is better
                                                        $convClass = $newBusinessRate >= 50 ? 'bg-success' : ($newBusinessRate >= 25 ? 'bg-warning' : 'bg-danger');
                                                    @endphp
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress-mini flex-grow-1" style="min-width: 80px;">
                                                            <div class="progress-bar {{ $convClass }}" role="progressbar" style="width: {{ $convBarWidth }}%; min-width: {{ $convBarWidth > 0 ? '5px' : '0' }};" aria-valuenow="{{ $newBusinessRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                        <span style="font-size: 0.85rem; font-weight: 600; min-width: 45px;">{{ $newBusinessRate }}%</span>
                                                    </div>
                                                </td> -->
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <i class="bx bx-info-circle" style="font-size: 2rem; color: #6c757d;"></i>
                                                    <p class="mb-0 mt-2 text-muted">No team performance data available</p>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="row g-4 mb-4">
                    <!-- New vs Existing Orders (MTD) -->
                    <div class="col-lg-12">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h5><i class="bx bx-bar-chart-alt-2 me-2"></i>NEW vs EXISTING ORDERS (MTD)</h5>
                            </div>
                            <div class="card-body">
                                <div id="newVsExistingChart" style="height: 350px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="row g-4 mb-4">
                    <!-- Orders by Sales Rep -->
                    <div class="col-lg-4">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h5><i class="bx bx-user me-2"></i>ORDERS BY SALES REP (MTD)</h5>
                            </div>
                            <div class="card-body">
                                <div id="ordersByRepChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- New Business per Staff -->
                    <div class="col-lg-4">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h5><i class="bx bx-trending-up me-2"></i>NEW BUSINESS</h5>
                            </div>
                            <div class="card-body">
                                <div id="newBusinessChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Business per Staff -->
                    <div class="col-lg-4">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h5><i class="bx bx-repeat me-2"></i>EXISTING BUSINESS</h5>
                            </div>
                            <div class="card-body">
                                <div id="existingBusinessChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Moment.js -->
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<!-- Date Range Picker -->
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize Date Range Picker
    $('#daterange').daterangepicker({
        opens: 'left',
        locale: {
            format: 'DD/MM/YYYY'
        },
        startDate: moment('{{ $startDate->format("Y-m-d") }}'),
        endDate: moment('{{ $endDate->format("Y-m-d") }}'),
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, function(start, end) {
        // Reload page with new date range
        window.location.href = '{{ route("admin.sales-dashboard.index") }}?start_date=' + start.format('YYYY-MM-DD') + '&end_date=' + end.format('YYYY-MM-DD');
    });

    // Refresh Button
    $('#refreshBtn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Refreshing...');

        $.ajax({
            url: '{{ route("admin.sales-dashboard.refresh") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Refreshed',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to refresh data. Please try again.'
                });
                btn.prop('disabled', false).html('<i class="bx bx-refresh"></i> Refresh');
            }
        });
    });

    @if($isConfigured && !empty($chartData))
    // Initialize Charts
    initializeCharts();
    @endif
});

@if($isConfigured && !empty($chartData))
function initializeCharts() {
    // New vs Existing Orders (MTD) - Daily Stacked Bar Chart
    const newVsExistingData = @json($chartData['newVsExisting'] ?? ['labels' => [], 'new' => [], 'existing' => []]);

    if (newVsExistingData.labels.length > 0) {
        new ApexCharts(document.querySelector("#newVsExistingChart"), {
            series: [{
                name: 'New Business',
                data: newVsExistingData.new
            }, {
                name: 'Existing Business',
                data: newVsExistingData.existing
            }],
            chart: {
                type: 'bar',
                height: 350,
                stacked: true,
                toolbar: { show: true },
                zoom: { enabled: false }
            },
            colors: ['#28a745', '#007bff'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 4,
                    columnWidth: '60%'
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: newVsExistingData.labels,
                title: { text: 'Day of Month' }
            },
            yaxis: {
                title: { text: 'Number of Orders' }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'left'
            },
            fill: { opacity: 1 },
            tooltip: {
                enabled: true,
                shared: true,
                intersect: false,
                followCursor: true,
                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                    var newOrders = series[0][dataPointIndex] || 0;
                    var existingOrders = series[1][dataPointIndex] || 0;
                    var totalOrders = newOrders + existingOrders;
                    var day = w.globals.labels[dataPointIndex];

                    return '<div style="padding: 12px; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 180px;">' +
                        '<div style="font-weight: 700; font-size: 14px; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 2px solid #eee; color: #333;">Day ' + day + '</div>' +
                        '<div style="display: flex; align-items: center; margin-bottom: 6px;">' +
                            '<span style="width: 12px; height: 12px; background: #28a745; border-radius: 50%; display: inline-block; margin-right: 10px;"></span>' +
                            '<span style="color: #555;">New Business: <strong style="color: #28a745;">' + newOrders + ' orders</strong></span>' +
                        '</div>' +
                        '<div style="display: flex; align-items: center; margin-bottom: 10px;">' +
                            '<span style="width: 12px; height: 12px; background: #007bff; border-radius: 50%; display: inline-block; margin-right: 10px;"></span>' +
                            '<span style="color: #555;">Existing Business: <strong style="color: #007bff;">' + existingOrders + ' orders</strong></span>' +
                        '</div>' +
                        '<div style="font-weight: 700; font-size: 14px; padding-top: 8px; border-top: 2px solid #eee; color: #333; background: #f8f9fa; margin: 0 -12px -12px -12px; padding: 10px 12px; border-radius: 0 0 8px 8px;">' +
                            '📊 Total: <strong style="color: #6f42c1;">' + totalOrders + ' orders</strong>' +
                        '</div>' +
                    '</div>';
                }
            }
        }).render();
    }

    // Orders by Sales Rep - Horizontal Bar Chart
    const ordersByRepData = @json($chartData['ordersByRep'] ?? ['labels' => [], 'data' => []]);

    if (ordersByRepData.labels.length > 0) {
        new ApexCharts(document.querySelector("#ordersByRepChart"), {
            series: [{
                name: 'Orders',
                data: ordersByRepData.data
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false }
            },
            colors: ['#6f42c1'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '60%'
                }
            },
            dataLabels: {
                enabled: true,
                style: { fontSize: '12px', colors: ['#fff'] }
            },
            xaxis: {
                categories: ordersByRepData.labels
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " orders";
                    }
                }
            }
        }).render();
    }

    // New Business per Staff - Horizontal Bar Chart
    const newBusinessData = @json($chartData['newBusinessByRep'] ?? ['labels' => [], 'data' => []]);

    if (newBusinessData.labels.length > 0) {
        new ApexCharts(document.querySelector("#newBusinessChart"), {
            series: [{
                name: 'New Business',
                data: newBusinessData.data
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false }
            },
            colors: ['#28a745'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '60%'
                }
            },
            dataLabels: {
                enabled: true,
                style: { fontSize: '12px', colors: ['#fff'] }
            },
            xaxis: {
                categories: newBusinessData.labels
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " orders";
                    }
                }
            }
        }).render();
    }

    // Existing Business per Staff - Horizontal Bar Chart
    const existingBusinessData = @json($chartData['existingBusinessByRep'] ?? ['labels' => [], 'data' => []]);

    if (existingBusinessData.labels.length > 0) {
        new ApexCharts(document.querySelector("#existingBusinessChart"), {
            series: [{
                name: 'Existing Business',
                data: existingBusinessData.data
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false }
            },
            colors: ['#007bff'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '60%'
                }
            },
            dataLabels: {
                enabled: true,
                style: { fontSize: '12px', colors: ['#fff'] }
            },
            xaxis: {
                categories: existingBusinessData.labels
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " orders";
                    }
                }
            }
        }).render();
    }
}
@endif
</script>
@endpush
