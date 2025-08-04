<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentJobsTracking extends Model
{
    use HasFactory;

    protected $table = 'current_jobs_trackings';

    protected $fillable = [
        'order_id',
        'collection_checked_in',
        'driver_eta_confirmed',
        'midpoint_check_completed',
        'delivered',
        'status',
        'order_data'
    ];

    protected $casts = [
        'collection_checked_in' => 'boolean',
        'driver_eta_confirmed' => 'boolean',
        'midpoint_check_completed' => 'boolean',
        'delivered' => 'integer',
        'order_data' => 'array',
    ];

    // Helper method to get or create tracking record
    public static function getOrCreateForOrder($orderId, $orderData = null)
    {
        return static::firstOrCreate(
            ['order_id' => $orderId],
            [
                'order_data' => $orderData,
                'status' => 'pending'
            ]
        );
    }

    // Mark collection as checked in and update status
    public function markCollectionCheckedIn()
    {
        $this->collection_checked_in = true;
        $this->checkAndUpdateStatus();
        $this->save();
    }

    // Mark driver ETA as confirmed and update status
    public function markDriverETAConfirmed()
    {
        $this->driver_eta_confirmed = true;
        $this->checkAndUpdateStatus();
        $this->save();
    }

    // Mark midpoint check as completed and update status
    public function markMidpointCheckCompleted()
    {
        $this->midpoint_check_completed = true;
        $this->checkAndUpdateStatus();
        $this->save();
    }

    // Check if all three are completed and update status
    private function checkAndUpdateStatus()
    {
        if ($this->collection_checked_in && 
            $this->driver_eta_confirmed && 
            $this->midpoint_check_completed) {
            $this->status = 'completed';
        }
    }

    // Check if job is completed
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function markDelivered($status)
    {
        $this->delivered = (int) $status; // Cast to integer
        $this->save();
        return $this;
    }
}
