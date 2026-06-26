<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ApiRequestLog extends Model
{
    protected $fillable = [
        'endpoint',
        'full_url',
        'method',
        'status_code',
        'response_time_ms',
        'triggered_by',
    ];

    public static function record(string $fullUrl, string $triggeredBy, int $statusCode = null, int $responseTimeMs = null, string $method = 'GET'): void
    {
        $endpoint = self::labelFromUrl($fullUrl);

        self::create([
            'endpoint'         => $endpoint,
            'full_url'         => $fullUrl,
            'method'           => $method,
            'status_code'      => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'triggered_by'     => $triggeredBy,
        ]);
    }

    private static function labelFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;
        $query = parse_url($url, PHP_URL_QUERY) ?? '';

        // Extract the last two path segments: e.g. /v1/orders -> "orders"
        $segments = array_filter(explode('/', $path));
        $resource = end($segments) ?: 'unknown';

        // Annotate with key filters
        $tags = [];
        if (str_contains($query, 'filter%5Bstatus%5D=planned') || str_contains($query, 'filter[status]=planned')) {
            $tags[] = 'planned';
        }
        if (str_contains($query, 'filter%5Bstatus%5D=open') || str_contains($query, 'filter[status]=open')) {
            $tags[] = 'open';
        }
        if (str_contains($query, 'filter%5BupdatedAt') || str_contains($query, 'filter[updatedAt]')) {
            $tags[] = 'updatedAt';
        }
        if (str_contains($query, 'filter%5BcustomerNo') || str_contains($query, 'filter[customerNo]')) {
            $tags[] = 'customer';
        }

        $label = $resource;
        if (!empty($tags)) {
            $label .= '/' . implode('+', $tags);
        }

        return $label;
    }

    // --- Scopes ---

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today('Europe/London'));
    }

    public function scopeThisWeek($query)
    {
        return $query->where('created_at', '>=', Carbon::now('Europe/London')->startOfWeek());
    }

    public function scopeThisMonth($query)
    {
        return $query->where('created_at', '>=', Carbon::now('Europe/London')->startOfMonth());
    }
}