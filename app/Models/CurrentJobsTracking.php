<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CurrentJobsTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_data',
        'collection_checked_in',
        'driver_eta_confirmed',
        'midpoint_check_completed',
        'delivered',
        'status',
        'completed_at'
    ];

    protected $casts = [
        'order_data' => 'array',
        'collection_checked_in' => 'boolean',
        'driver_eta_confirmed' => 'boolean',
        'midpoint_check_completed' => 'boolean',
        'completed_at' => 'datetime'
    ];

    /**
     * Get or create a tracking record for an order
     */
    public static function getOrCreateForOrder($orderId, $orderData = null)
    {
        return self::firstOrCreate(
            ['order_id' => $orderId],
            [
                'order_data' => $orderData,
                'collection_checked_in' => false,
                'driver_eta_confirmed' => false,
                'midpoint_check_completed' => false,
                'delivered' => null,
                'status' => 'active'
            ]
        );
    }

    /**
     * Mark collection as checked in
     */
    public function markCollectionCheckedIn()
    {
        $this->collection_checked_in = true;
        $this->save();
        return $this;
    }

    /**
     * Mark driver ETA as confirmed
     */
    public function markDriverETAConfirmed()
    {
        $this->driver_eta_confirmed = true;
        $this->save();
        return $this;
    }

    /**
     * Mark midpoint check as completed
     */
    public function markMidpointCheckCompleted()
    {
        $this->midpoint_check_completed = true;
        $this->save();
        return $this;
    }

    /**
     * Mark delivery status
     */
    public function markDelivered($deliveredStatus)
    {
        $this->delivered = (int) $deliveredStatus;
        $this->save();
        return $this;
    }

    /**
     * Mark job as completed
     */
    public function markCompleted()
    {
        $this->status = 'completed';
        $this->completed_at = Carbon::now();
        $this->save();
        return $this;
    }

    /**
     * Check if job is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Scope for active jobs only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed jobs only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}