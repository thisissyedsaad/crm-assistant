@extends('admin.layouts.app')

@section('title', 'Sales Staff Dashboard | CSD Assistant')

@push('links')
<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
/* Staff Dashboard Column Cards */
.dashboard-column {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
    transform: translateY(20px);
}

.dashboard-column:nth-child(1) { animation-delay: 0.1s; }
.dashboard-column:nth-child(2) { animation-delay: 0.2s; }
.dashboard-column:nth-child(3) { animation-delay: 0.3s; }

.dashboard-column:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    transition: all 0.3s ease;
}

.dashboard-column .column-header {
    padding: 1rem 1.25rem;
    font-weight: 700;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: center;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.dashboard-column .column-header .info-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    margin-left: 0;
}

.dashboard-column.total .column-header { background: linear-gradient(135deg, #326E53, #326E53); }
.dashboard-column.new .column-header { background: linear-gradient(135deg, #28a745, #28a745); }
.dashboard-column.existing .column-header { background: linear-gradient(135deg, #0D65D9, #0D65D9); }

.dashboard-column .column-body {
    padding: 1.25rem;
    background: #fff;
}

.dashboard-column .metric-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.dashboard-column .metric-item:last-child {
    border-bottom: none;
}

.dashboard-column .metric-label {
    color: #6c757d;
    font-size: 0.85rem;
    font-weight: 500;
}

.dashboard-column .metric-value {
    font-size: 1.25rem;
    font-weight: 700;
}

.dashboard-column.total .metric-value { color: #326E53; }
.dashboard-column.new .metric-value { color: #28a745; }
.dashboard-column.existing .metric-value { color: #0D65D9; }

/* Stats Cards - Second Row */
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

.stats-card:nth-child(1) { animation-delay: 0.4s; }
.stats-card:nth-child(2) { animation-delay: 0.5s; }
.stats-card:nth-child(3) { animation-delay: 0.6s; }

/* Today Cards - 3rd row */
.today-card:nth-child(1) { animation-delay: 0.4s; }
.today-card:nth-child(2) { animation-delay: 0.5s; }
.today-card:nth-child(3) { animation-delay: 0.6s; }

.today-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 10px;
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
    transform: translateY(20px);
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.today-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
}

.today-card .card-body {
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    position: relative;
}

.today-card .stats-icon {
    width: 50px;
    height: 50px;
    min-width: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.today-card .stats-content {
    flex: 1;
    text-align: right;
    margin-right: 15px;
}

.today-card .stats-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.1rem;
    line-height: 1.2;
}

.today-card .stats-label {
    color: #6c757d;
    font-size: 0.75rem;
    font-weight: 500;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.today-card.green {
    border-left: 4px solid #20c997;
}
.today-card.green .stats-icon {
    background: rgba(32, 201, 151, 0.1);
    color: #20c997;
}
.today-card.green .stats-value {
    color: #20c997;
}

.today-card.purple {
    border-left: 4px solid #6f42c1;
}
.today-card.purple .stats-icon {
    background: rgba(111, 66, 193, 0.1);
    color: #6f42c1;
}
.today-card.purple .stats-value {
    color: #6f42c1;
}

.today-card.red {
    border-left: 4px solid #dc3545;
}
.today-card.red .stats-icon {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}
.today-card.red .stats-value {
    color: #dc3545;
}

.today-card .info-icon {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.1);
    color: #6c757d;
}

.today-card .info-icon:hover {
    background: rgba(0, 0, 0, 0.2);
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
}

.stats-card .stats-content {
    flex: 1;
    text-align: right;
    margin-right: 15px;
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

/* Chart Card */
.chart-card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    animation: fadeInUp 0.5s ease forwards;
    animation-delay: 0.7s;
    opacity: 0;
    transform: translateY(20px);
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

/* Date Range Picker (Admin only) */
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

/* Error Alert */
.error-alert {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
}

.error-alert i {
    font-size: 3rem;
    color: #856404;
    margin-bottom: 1rem;
}

.error-alert h4 {
    color: #856404;
    margin-bottom: 0.5rem;
}

.error-alert p {
    color: #6c757d;
    margin-bottom: 0;
}

/* Number Masking Feature - Card-wise hover */
@if($maskNumbers)
.masked-value {
    cursor: pointer;
    position: relative;
}

.masked-value .actual-value {
    display: none;
}

.masked-value .mask-stars {
    display: inline;
    letter-spacing: 2px;
}

/* Card-wise hover - reveal all numbers in the card */
.dashboard-column:hover .masked-value .actual-value,
.stats-card:hover .masked-value .actual-value,
.today-card:hover .masked-value .actual-value {
    display: inline;
}

.dashboard-column:hover .masked-value .mask-stars,
.stats-card:hover .masked-value .mask-stars,
.today-card:hover .masked-value .mask-stars {
    display: none;
}

/* Auto-hide after timeout - force stars even on hover */
.dashboard-column.mask-timeout .masked-value .actual-value,
.stats-card.mask-timeout .masked-value .actual-value,
.today-card.mask-timeout .masked-value .actual-value {
    display: none !important;
}

.dashboard-column.mask-timeout .masked-value .mask-stars,
.stats-card.mask-timeout .masked-value .mask-stars,
.today-card.mask-timeout .masked-value .mask-stars {
    display: inline !important;
}
@endif

/* Info Icon Tooltip */
.info-icon {
    display: inline-flex;
    /* align-items: center;
    justify-content: center; */
    width: 18px;
    height: 18px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    font-size: 0.7rem;
    cursor: pointer;
    margin-left: 6px;
    transition: all 0.2s ease;
}

.info-icon:hover {
    background: rgba(255, 255, 255, 0.5);
    transform: scale(1.1);
}

.stats-card .info-icon {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.1);
    color: #6c757d;
}

.stats-card .info-icon:hover {
    background: rgba(0, 0, 0, 0.2);
}

.stats-card .card-body {
    position: relative;
}

.chart-card .info-icon {
    background: rgba(0, 0, 0, 0.1);
    color: #6c757d;
}

.chart-card .info-icon:hover {
    background: rgba(0, 0, 0, 0.2);
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

    .dashboard-column .metric-value {
        font-size: 1.1rem;
    }

    .stats-card .stats-value {
        font-size: 1.25rem;
    }
}

i.bx.bxs-info-circle {
    font-size: 15px;
}
</style>
@endpush

@section('content')
<!-- Main Content Area -->
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="dashboard-area">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card" style="border-radius: 12px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div>
                                        <h4 class="mb-1" style="font-weight: 600;">
                                            @if($viewingOtherUser && $viewingUser)
                                                <a href="{{ route('admin.sales-dashboard.index') }}" class="btn btn-sm btn-outline-secondary me-2" title="Back to Team Dashboard">
                                                    <i class="bx bx-arrow-back"></i>
                                                </a>
                                                {{ $viewingUser->name }}'s Dashboard
                                            @else
                                                Sales Staff Dashboard
                                            @endif
                                        </h4>
                                        <div class="cache-info">
                                            <i class="bx bx-time-five"></i>
                                            <span>Auto-refresh every {{ floor($cacheTtl / 60) }} minutes</span>
                                        </div>
                                    </div>
                                    <div class="date-range-container">
                                        <input type="text" id="daterange" class="form-control"
                                               value="{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($error)
                <!-- Error Alert -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="error-alert">
                            <i class="bx bx-error-circle"></i>
                            <h4>Unable to Load Dashboard</h4>
                            <p>{{ $error }}</p>
                        </div>
                    </div>
                </div>
                @elseif($stats)

                <!-- Top Row - Three Columns (TOTAL, NEW, EXISTING) -->
                <div class="row g-4 mb-4">
                    <!-- TOTAL Column -->
                    <div class="col-md-4">
                        <div class="card dashboard-column total">
                            <div class="column-header">
                                <i class="bx bx-chart me-2"></i> TOTAL
                                <span class="info-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Overall performance across both new and existing business, measured against target pace.">
                                    <i class="bx bxs-info-circle"></i>
                                </span>
                            </div>
                            <div class="column-body">
                                <div class="metric-item">
                                    <span class="metric-label">Monthly Target:</span>
                                    <span class="metric-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['total']['monthly_target'] }}</span>
                                        @else
                                            {{ $stats['total']['monthly_target'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Daily Target:</span>
                                    <span class="metric-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['total']['daily_target'] }}</span>
                                        @else
                                            {{ $stats['total']['daily_target'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Orders Converted (MTD):</span>
                                    <span class="metric-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['total']['orders_converted'] }}</span>
                                        @else
                                            {{ $stats['total']['orders_converted'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">On Target:</span>
                                    <span class="metric-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['total']['on_target_percent'] }}%</span>
                                        @else
                                            {{ $stats['total']['on_target_percent'] }}%
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NEW Column -->
                    <div class="col-md-4">
                        <div class="card dashboard-column new">
                            <div class="column-header">
                                <i class="bx bx-plus-circle me-2"></i> NEW
                                <span class="info-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Performance for new business orders compared to target expectations.">
                                    <i class="bx bxs-info-circle"></i>
                                </span>
                            </div>
                            <div class="column-body">
                                <div class="metric-item">
                                    <span class="metric-label">Monthly Target:</span>
                                    <span class="metric-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['new']['monthly_target'] }}</span>
                                        @else
                                            {{ $stats['new']['monthly_target'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Daily Target:</span>
                                    <span class="metric-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['new']['daily_target'] }}</span>
                                        @else
                                            {{ $stats['new']['daily_target'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Orders Converted (MTD):</span>
                                    <span class="metric-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['new']['orders_converted'] }}</span>
                                        @else
                                            {{ $stats['new']['orders_converted'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">On Target:</span>
                                    <span class="metric-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['new']['on_target_percent'] }}%</span>
                                        @else
                                            {{ $stats['new']['on_target_percent'] }}%
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- EXISTING Column -->
                    <div class="col-md-4">
                        <div class="card dashboard-column existing">
                            <div class="column-header">
                                <i class="bx bx-refresh me-2"></i> EXISTING
                                <span class="info-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Performance for repeat and existing customer orders against target pace.">
                                    <i class="bx bxs-info-circle"></i>
                                </span>
                            </div>
                            <div class="column-body">
                                <div class="metric-item">
                                    <span class="metric-label">Monthly Target:</span>
                                    <span class="metric-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['existing']['monthly_target'] }}</span>
                                        @else
                                            {{ $stats['existing']['monthly_target'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Daily Target:</span>
                                    <span class="metric-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['existing']['daily_target'] }}</span>
                                        @else
                                            {{ $stats['existing']['daily_target'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Orders Converted (MTD):</span>
                                    <span class="metric-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['existing']['orders_converted'] }}</span>
                                        @else
                                            {{ $stats['existing']['orders_converted'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">On Target:</span>
                                    <span class="metric-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['existing']['on_target_percent'] }}%</span>
                                        @else
                                            {{ $stats['existing']['on_target_percent'] }}%
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second Row - Three Stats Cards -->
                <div class="row g-3 mb-4">
                    <!-- Insurance Sold (MTD) -->
                    <div class="col-md-4">
                        <div class="card stats-card cyan">
                            <div class="card-body">
                                <span class="info-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Total insurance add-ons sold so far this month.">
                                    <i class="bx bxs-info-circle"></i>
                                </span>
                                <div class="stats-icon">
                                    <i class="bx bx-shield-plus"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['insurance_sold_count'] }}</span>
                                        @else
                                            {{ $stats['insurance_sold_count'] }}
                                        @endif
                                    </div>
                                    <p class="stats-label">Insurance Sold (MTD)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Drivers Cost Saved (MTD) -->
                    <div class="col-md-4">
                        <div class="card stats-card yellow">
                            <div class="card-body">
                                <span class="info-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Number of total cost savings generated on driver bookings this month.">
                                    <i class="bx bxs-info-circle"></i>
                                </span>
                                <div class="stats-icon">
                                    <i class="bx bx-car"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['drivers_cost_saved_count'] }}</span>
                                        @else
                                            {{ $stats['drivers_cost_saved_count'] }}
                                        @endif
                                    </div>
                                    <p class="stats-label">Drivers Cost Saved (MTD)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Needed This Week -->
                    <div class="col-md-4">
                        <div class="card stats-card orange">
                            <div class="card-body">
                                <span class="info-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Remaining orders required this week to stay on track with target.">
                                    <i class="bx bxs-info-circle"></i>
                                </span>
                                <div class="stats-icon">
                                    <i class="bx bx-calendar-week"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['orders_needed_this_week'] }}</span>
                                        @else
                                            {{ $stats['orders_needed_this_week'] }}
                                        @endif
                                    </div>
                                    <p class="stats-label">Orders Needed This Week</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Third Row - Today Cards -->
                <div class="row g-3 mb-4">
                    <!-- Insurance Sold Today -->
                    <div class="col-md-4">
                        <div class="card today-card green">
                            <div class="card-body">
                                <span class="info-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Number of insurance add-ons sold today." style="display:none;">
                                    <i class="bx bxs-info-circle"></i>
                                </span>
                                <div class="stats-icon">
                                    <i class="bx bx-shield-quarter"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['insurance_sold_today'] }}</span>
                                        @else
                                            {{ $stats['insurance_sold_today'] }}
                                        @endif
                                    </div>
                                    <p class="stats-label">Insurance Sold Today</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Drivers Cost Saved Today -->
                    <div class="col-md-4">
                        <div class="card today-card purple">
                            <div class="card-body">
                                <span class="info-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Total drivers cost savings generated today." style="display:none;">
                                    <i class="bx bxs-info-circle"></i>
                                </span>
                                <div class="stats-icon">
                                    <i class="bx bx-car"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">£{{ number_format($stats['drivers_cost_saved_today'], 2) }}</span>
                                        @else
                                            £{{ number_format($stats['drivers_cost_saved_today'], 2) }}
                                        @endif
                                    </div>
                                    <p class="stats-label">Drivers Cost Saved Today</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Needed Today -->
                    <div class="col-md-4">
                        <div class="card today-card red">
                            <div class="card-body">
                                <span class="info-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Orders still needed today to meet your daily target. Negative means you're ahead of today's target." style="display:none;">
                                    <i class="bx bxs-info-circle"></i>
                                </span>
                                <div class="stats-icon">
                                    <i class="bx bx-target-lock"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-value @if($maskNumbers) masked-value @endif">
                                        @if($maskNumbers)
                                            <span class="mask-stars">***</span>
                                            <span class="actual-value">{{ $stats['orders_needed_today'] }}</span>
                                        @else
                                            {{ $stats['orders_needed_today'] }}
                                        @endif
                                    </div>
                                    <p class="stats-label">Orders Needed Today</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Row - MTD Orders vs Target -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h5>
                                    <i class="bx bx-line-chart me-2"></i>MTD Orders vs Target
                                    <span class="info-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Comparison of actual month-to-date orders against expected target pace.">
                                        <i class="bx bxs-info-circle"></i>
                                    </span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="ordersVsTargetChart" style="height: 350px;"></div>
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

<script>
$(document).ready(function() {
    // Initialize Bootstrap tooltips for info icons
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    @if($maskNumbers)
    // Auto-hide masked numbers after 10 seconds of no mouse movement
    const MASK_TIMEOUT = 10000; // 10 seconds
    let maskTimeouts = new Map();

    function setupMaskAutoHide(cards) {
        cards.each(function() {
            const card = $(this);
            let timeoutId = null;

            // Start timeout when mouse enters
            card.on('mouseenter', function() {
                card.removeClass('mask-timeout');
                resetMaskTimeout();
            });

            // Reset timeout on mouse movement
            card.on('mousemove', function() {
                if (card.hasClass('mask-timeout')) {
                    card.removeClass('mask-timeout');
                }
                resetMaskTimeout();
            });

            // Clear timeout when mouse leaves
            card.on('mouseleave', function() {
                clearMaskTimeout();
                card.removeClass('mask-timeout');
            });

            function resetMaskTimeout() {
                clearMaskTimeout();
                timeoutId = setTimeout(function() {
                    card.addClass('mask-timeout');
                }, MASK_TIMEOUT);
            }

            function clearMaskTimeout() {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                    timeoutId = null;
                }
            }
        });
    }

    // Apply to all masked cards
    setupMaskAutoHide($('.dashboard-column, .stats-card, .today-card'));
    @endif

    // Initialize Date Range Picker
    @if($isAdmin)
    $('#daterange').daterangepicker({
        opens: 'left',
        locale: { format: 'DD/MM/YYYY' },
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
        @if($viewingOtherUser && $viewingUser)
        window.location.href = '{{ route("admin.staff-sales-dashboard.index", ["user_id" => $viewingUser->id]) }}?start_date=' + start.format('YYYY-MM-DD') + '&end_date=' + end.format('YYYY-MM-DD');
        @else
        window.location.href = '{{ route("admin.staff-sales-dashboard.index") }}?start_date=' + start.format('YYYY-MM-DD') + '&end_date=' + end.format('YYYY-MM-DD');
        @endif
    });
    @else
    // Staff: date filter restricted to current month only
    $('#daterange').daterangepicker({
        opens: 'left',
        locale: { format: 'DD/MM/YYYY' },
        startDate: moment('{{ $startDate->format("Y-m-d") }}'),
        endDate: moment('{{ $endDate->format("Y-m-d") }}'),
        minDate: moment().startOf('month'),
        maxDate: moment(),
        ranges: {
            'Today': [moment(), moment()],
            'This Week': [moment().startOf('isoWeek'), moment()],
            'This Month': [moment().startOf('month'), moment()]
        }
    }, function(start, end) {
        window.location.href = '{{ route("admin.staff-sales-dashboard.index") }}?start_date=' + start.format('YYYY-MM-DD') + '&end_date=' + end.format('YYYY-MM-DD');
    });
    @endif

    @if($stats && $chartData)
    // Initialize MTD Orders vs Target Chart
    initializeChart();
    @endif
});

@if($stats && $chartData)
function initializeChart() {
    const chartData = @json($chartData);

    if (chartData.labels.length > 0) {
        new ApexCharts(document.querySelector("#ordersVsTargetChart"), {
            series: [{
                name: 'Target (Cumulative)',
                type: 'line',
                data: chartData.target
            }, {
                name: 'Actual Orders (Cumulative)',
                type: 'line',
                data: chartData.actual
            }],
            chart: {
                type: 'line',
                height: 350,
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true
                    }
                },
                zoom: { enabled: true }
            },
            colors: ['#6c757d', '#007bff'],
            stroke: {
                width: [2, 3],
                curve: 'smooth',
                dashArray: [5, 0]
            },
            markers: {
                size: [0, 4],
                colors: ['#6c757d', '#007bff'],
                strokeColors: '#fff',
                strokeWidth: 2,
                hover: {
                    size: 6
                }
            },
            xaxis: {
                categories: chartData.labels,
                title: { text: 'Day of Month' },
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                show: false,
                title: { text: '' },
                min: 0,
                labels: {
                    show: false
                }
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center',
                floating: false,
                fontSize: '14px',
                fontFamily: 'inherit',
                fontWeight: 500,
                offsetY: 0,
                markers: {
                    width: 14,
                    height: 14,
                    radius: 3
                },
                itemMargin: {
                    horizontal: 15,
                    vertical: 5
                }
            },
            grid: {
                borderColor: '#f0f0f0',
                strokeDashArray: 3,
                padding: {
                    top: 0,
                    right: 10,
                    bottom: 0,
                    left: 10
                }
            },
            tooltip: {
                enabled: true,
                shared: true,
                intersect: false,
                y: {
                    formatter: function(val) {
                        return Math.round(val) + " orders";
                    }
                }
            }
        }).render();
    }
}
@endif
</script>
@endpush
