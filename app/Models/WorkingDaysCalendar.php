<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingDaysCalendar extends Model
{
    use HasFactory;

    protected $table = 'working_days_calendar';

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'working_days',
        'total_working_days',
    ];

    protected $casts = [
        'working_days' => 'array',
        'year' => 'integer',
        'month' => 'integer',
        'total_working_days' => 'integer',
    ];

    /**
     * Get the user that owns the calendar
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
