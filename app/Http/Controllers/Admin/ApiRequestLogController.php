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

        $hourlyData = [];
        for ($h = 0; $h <= 23; $h++) {
            $hourlyData[] = [
                'hour'  => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00',
                'total' => $hourlyRaw->get($h)?->total ?? 0,
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

        return view('admin.api-logs.index', compact(
            'todayCount',
            'weekCount',
            'monthCount',
            'hourlyData',
            'byTrigger',
            'byEndpoint',
            'recent',
            'avgResponseMs',
            'now'
        ));
    }
}