@extends('admin.layouts.app')

@section('title', 'API Request Monitor | CSD Assistant')

@push('links')
<style>
    .stat-card {
        border-radius: 14px;
        color: #fff;
        padding: 24px 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 6px 24px rgba(0,0,0,.12);
    }
    .stat-card .stat-num  { font-size: 2.6rem; font-weight: 700; line-height: 1; }
    .stat-card .stat-lbl  { font-size: .82rem; opacity: .85; margin-top: 6px; }
    .stat-card .stat-icon { position: absolute; right: 18px; top: 16px; font-size: 2.4rem; opacity: .18; }

    .card-today   { background: linear-gradient(135deg,#0652dd,#1289A7); }
    .card-week    { background: linear-gradient(135deg,#5f27cd,#341f97); }
    .card-month   { background: linear-gradient(135deg,#ee5a24,#c0392b); }
    .card-avg     { background: linear-gradient(135deg,#10ac84,#1dd1a1); }

    .trigger-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: .75rem;
        font-weight: 600;
        color: #fff;
    }
    .tb-notifications { background: #e84393; }
    .tb-datatable     { background: #0652dd; }
    .tb-counters      { background: #f9ca24; color: #333 !important; }
    .tb-get-customer  { background: #6ab04c; }
    .tb-update-status { background: #eb4d4b; }
    .tb-order-show    { background: #8e44ad; }
    .tb-order-count   { background: #16a085; }
    .tb-unknown       { background: #95a5a6; }

    .hourly-bar-wrap  { height: 30px; background:#f1f2f6; border-radius:6px; overflow:hidden; }
    .hourly-bar       { height: 100%; background: linear-gradient(90deg,#0652dd,#1289A7); border-radius:6px; transition: width .4s; }

    .log-table td, .log-table th { font-size: .82rem; vertical-align: middle; }
    .status-ok   { color: #10ac84; font-weight: 600; }
    .status-err  { color: #ee5a24; font-weight: 600; }
    .ms-fast     { color: #10ac84; }
    .ms-slow     { color: #ee5a24; }
</style>
@endpush

@section('content')
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="data-table-area">
    <div class="container-fluid">

        {{-- Header --}}
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h4 class="mb-0 fw-bold">EasyTrans API Monitor</h4>
                <small class="text-muted">All outbound requests to mytransport.co.uk &mdash; as of {{ $now->format('d M Y, H:i') }}</small>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                    <i class="bx bx-refresh me-1"></i> Refresh
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card card-today">
                    <div class="stat-icon"><i class="bx bx-calendar-check"></i></div>
                    <div class="stat-num">{{ number_format($todayCount) }}</div>
                    <div class="stat-lbl">Requests Today</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card card-week">
                    <div class="stat-icon"><i class="bx bx-calendar-week"></i></div>
                    <div class="stat-num">{{ number_format($weekCount) }}</div>
                    <div class="stat-lbl">This Week</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card card-month">
                    <div class="stat-icon"><i class="bx bx-calendar"></i></div>
                    <div class="stat-num">{{ number_format($monthCount) }}</div>
                    <div class="stat-lbl">This Month</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card card-avg">
                    <div class="stat-icon"><i class="bx bx-time-five"></i></div>
                    <div class="stat-num">{{ $avgResponseMs ? round($avgResponseMs) : '—' }}<span style="font-size:1rem"> ms</span></div>
                    <div class="stat-lbl">Avg Response Time (Today)</div>
                </div>
            </div>
        </div>

        {{-- Daily Breakdown --}}
        @php $maxDaily = $dailyBreakdown->max('total') ?: 1; @endphp
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-semibold bg-white border-bottom d-flex justify-content-between align-items-center">
                <span>Daily Requests <small class="text-muted fw-normal">(last 30 days)</small></span>
            </div>
            <div class="card-body p-0">
                @if($dailyBreakdown->isEmpty())
                    <p class="text-center text-muted py-4 mb-0">No data yet</p>
                @else
                <table class="table table-sm table-hover mb-0 log-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:140px">Date</th>
                            <th>Requests</th>
                            <th class="text-end" style="width:80px">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyBreakdown as $day)
                        <tr>
                            <td style="white-space:nowrap;font-weight:600">
                                {{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}
                                @if($day->date === now()->toDateString())
                                    <span class="badge bg-primary ms-1" style="font-size:.65rem">Today</span>
                                @endif
                            </td>
                            <td>
                                <div class="hourly-bar-wrap" style="max-width:500px;">
                                    <div class="hourly-bar" style="width:{{ round(($day->total / $maxDaily) * 100) }}%"></div>
                                </div>
                            </td>
                            <td class="text-end fw-bold">{{ number_format($day->total) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-4">

            {{-- By Trigger --}}
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold bg-white border-bottom">
                        Requests by Source <small class="text-muted">(today)</small>
                    </div>
                    <div class="card-body p-0" style="max-height:320px;overflow-y:auto;">
                        <table class="table table-sm mb-0 log-table">
                            <thead class="table-light">
                                <tr><th>Source</th><th class="text-end">Count</th></tr>
                            </thead>
                            <tbody>
                                @forelse($byTrigger as $row)
                                <tr>
                                    <td>
                                        <span class="trigger-badge tb-{{ $row->triggered_by }}">
                                            {{ $row->triggered_by ?? 'unknown' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($row->total) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">No data yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- By Endpoint --}}
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold bg-white border-bottom">
                        Requests by Endpoint <small class="text-muted">(today)</small>
                    </div>
                    <div class="card-body p-0" style="max-height:320px;overflow-y:auto;">
                        <table class="table table-sm mb-0 log-table">
                            <thead class="table-light">
                                <tr><th>Endpoint</th><th class="text-end">Count</th></tr>
                            </thead>
                            <tbody>
                                @forelse($byEndpoint as $row)
                                <tr>
                                    <td><code style="font-size:.75rem">{{ $row->endpoint }}</code></td>
                                    <td class="text-end fw-bold">{{ number_format($row->total) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">No data yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Hourly bar chart --}}
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold bg-white border-bottom">
                        Hourly Breakdown <small class="text-muted">(today)</small>
                    </div>
                    <div class="card-body" style="max-height:320px;overflow-y:auto;">
                        @php $maxHourly = collect($hourlyData)->max('total') ?: 1; @endphp
                        @foreach($hourlyData as $h)
                        @if($h['total'] > 0)
                        <div class="d-flex align-items-center gap-2 mb-1" style="font-size:.78rem;">
                            <span style="width:80px;flex-shrink:0;">
                                <span style="color:#333;font-weight:600">{{ $h['hour'] }}</span>
                                <span style="color:#aaa;font-size:.7rem"> / {{ $h['pkt_hour'] }} PKT</span>
                            </span>
                            <div class="hourly-bar-wrap flex-grow-1">
                                <div class="hourly-bar" style="width:{{ round(($h['total']/$maxHourly)*100) }}%"></div>
                            </div>
                            <span style="width:32px;text-align:right;font-weight:600">{{ $h['total'] }}</span>
                        </div>
                        @endif
                        @endforeach
                        @if(collect($hourlyData)->sum('total') === 0)
                        <p class="text-center text-muted py-3 mb-0">No requests today yet</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- Recent Log --}}
        <div class="card shadow-sm">
            <div class="card-header fw-semibold bg-white border-bottom d-flex justify-content-between align-items-center">
                <span>Recent 50 Requests</span>
                <small class="text-muted">Latest first</small>
            </div>
            <div class="card-body p-0" style="overflow-x:auto;">
                <table class="table table-sm table-hover mb-0 log-table">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Source</th>
                            <th>Endpoint</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Response</th>
                            <th>Full URL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent as $log)
                        <tr>
                            <td style="white-space:nowrap">{{ $log->created_at->setTimezone('Europe/London')->format('H:i:s') }}</td>
                            <td>
                                <span class="trigger-badge tb-{{ $log->triggered_by }}">
                                    {{ $log->triggered_by ?? 'unknown' }}
                                </span>
                            </td>
                            <td><code style="font-size:.75rem">{{ $log->endpoint }}</code></td>
                            <td class="text-center">
                                @if($log->status_code >= 200 && $log->status_code < 300)
                                    <span class="status-ok">{{ $log->status_code }}</span>
                                @else
                                    <span class="status-err">{{ $log->status_code ?? '?' }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($log->response_time_ms !== null)
                                    <span class="{{ $log->response_time_ms > 2000 ? 'ms-slow' : 'ms-fast' }}">
                                        {{ $log->response_time_ms }} ms
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <span title="{{ $log->full_url }}" style="font-size:.72rem;color:#666">
                                    {{ $log->full_url }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No requests logged yet. They will appear here once the app starts making API calls.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection