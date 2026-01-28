<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'daily_target_total',
        'daily_target_new',
        'daily_target_existing',
        'working_days',
        'monthly_target',
    ];

    protected $casts = [
        'daily_target_total' => 'integer',
        'daily_target_new' => 'integer',
        'daily_target_existing' => 'integer',
        'working_days' => 'integer',
        'monthly_target' => 'integer',
    ];

    /**
     * Get the user that owns the target
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate monthly target
     */
    public function calculateMonthlyTarget()
    {
        return $this->daily_target_total * $this->working_days;
    }
}