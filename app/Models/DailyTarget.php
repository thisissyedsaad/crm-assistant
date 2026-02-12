<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'daily_target_total',
        'daily_target_new',
        'daily_target_existing',
        'working_days',
        'monthly_target',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'daily_target_total' => 'integer',
        'daily_target_new' => 'integer',
        'daily_target_existing' => 'integer',
        'working_days' => 'float', // Supports decimal values like 18.5 for half days
        'monthly_target' => 'float', // Supports decimal calculations
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