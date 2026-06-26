<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiRequestLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiRequestLogController extends Controller
{
    public function index(Request $request)
    {
        $tz = 'Europe/London';
        $now = Carbon::now($tz);

        // Summary counts
        $todayCount   = ApiRequestLog::today()->count();
        $weekCount    = ApiRequestLog::thisWeek()->count();
        $monthCount   = ApiRequestLog::thisMonth()->count();

        // Per-hour breakdown for today (0–23)
        $hourlyRaw = ApiRequestLog::today()
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupByRaw('HOUR(created_at)')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        // DB stores UTC; PKT = UTC+5
        $hourlyData = [];
        for ($h = 0; $h <= 23; $h++) {
            $pktH = ($h + 5) % 24;
            $hourlyData[] = [
                'hour'     => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00',
                'pkt_hour' => str_pad($pktH, 2, '0', STR_PAD_LEFT) . ':00',
                'total'    => $hourlyRaw->get($h)?->total ?? 0,
            ];
        }

        // Per-trigger breakdown today
        $byTrigger = ApiRequestLog::today()
            ->selectRaw('triggered_by, COUNT(*) as total')
            ->groupBy('triggered_by')
            ->orderByDesc('total')
            ->get();

        // Per-endpoint breakdown today
        $byEndpoint = ApiRequestLog::today()
            ->selectRaw('endpoint, COUNT(*) as total')
            ->groupBy('endpoint')
            ->orderByDesc('total')
            ->get();

        // Recent 50 logs
        $recent = ApiRequestLog::latest()->limit(50)->get();

        // Average response time today (ms)
        $avgResponseMs = ApiRequestLog::today()->avg('response_time_ms');

        // Daily breakdown — last 30 days
        $dailyBreakdown = ApiRequestLog::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', Carbon::now($tz)->subDays(29)->startOfDay())
            ->groupByRaw('DATE(created_at)')
            ->orderByDesc('date')
            ->get();

        return view('admin.api-logs.index', compact(
            'todayCount',
            'weekCount',
            'monthCount',
            'hourlyData',
            'byTrigger',
            'byEndpoint',
            'recent',
            'avgResponseMs',
            'dailyBreakdown',
            'now'
        ));
    }
}