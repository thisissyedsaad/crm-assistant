<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HiddenCurrentJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'hidden_by',
        'hidden_at'
    ];

    protected $dates = [
        'hidden_at'
    ];

    protected $casts = [
        'hidden_at' => 'datetime'
    ];

    /**
     * Scope to get hidden jobs for a specific user
     */
    public function scopeForUser($query, $userId = null)
    {
        $userId = $userId ?? (auth()->id() ?? 'guest');
        return $query->where('hidden_by', $userId);
    }

    /**
     * Scope to get hidden jobs for today only
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    /**
     * Check if a specific order is hidden for current user
     */
    // public static function isOrderHidden($orderId, $userId = null)
    // {
    //     $userId = $userId ?? (auth()->id() ?? 'guest');
        
    //     return self::where('order_id', $orderId)
    //         ->where('hidden_by', $userId)
    //         ->whereDate('created_at', Carbon::today())
    //         ->exists();
    // }

    // /**
    //  * Hide an order for current user
    //  */
    // public static function hideOrder($orderId, $userId = null)
    // {
    //     $userId = $userId ?? (auth()->id() ?? 'guest');
        
    //     return self::firstOrCreate([
    //         'order_id' => $orderId,
    //         'hidden_by' => $userId,
    //         'created_at' => Carbon::today()
    //     ], [
    //         'hidden_at' => Carbon::now()
    //     ]);
    // }


    // In HiddenCurrentJob model, change these methods:
    public static function isOrderHidden($orderId, $userId = null)
    {
        // Remove user filtering
        return self::where('order_id', $orderId)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    public static function hideOrder($orderId, $userId = null)
    {
        $userId = $userId ?? (auth()->id() ?? 'guest');
        
        // Remove user from unique constraint but keep for audit
        return self::firstOrCreate([
            'order_id' => $orderId,
            'created_at' => Carbon::today()
        ], [
            'hidden_by' => $userId, // Still track who hid it
            'hidden_at' => Carbon::now()
        ]);
    }

    /**
     * Clean up old hidden jobs (older than 7 days)
     */
    public static function cleanupOldHiddenJobs()
    {
        return self::where('created_at', '<', Carbon::now()->subDays(7))->delete();
    }
}