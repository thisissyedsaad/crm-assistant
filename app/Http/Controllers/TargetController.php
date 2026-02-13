<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DailyTarget;
use App\Models\WorkingDaysCalendar;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    /**
     * Constructor - restrict access to admin and super-admin only
     */
    public function __construct()
    {
        $this->middleware('role:admin|super-admin');
    }

    /**
     * Display the targets management page
     */
    public function index(Request $request)
    {
        // Get year and month from request, default to current
        $selectedYear = $request->get('year', now()->year);
        $selectedMonth = $request->get('month', now()->month);

        // Get all staff and admin users with their targets for selected month/year
        $staffUsers = User::whereIn('role', ['staff', 'admin'])
            ->with(['dailyTarget' => function($query) use ($selectedYear, $selectedMonth) {
                $query->where('year', $selectedYear)
                      ->where('month', $selectedMonth);
            }, 'workingDaysCalendar' => function($query) use ($selectedYear, $selectedMonth) {
                $query->where('year', $selectedYear)
                      ->where('month', $selectedMonth);
            }])
            ->orderBy('name')
            ->get();

        // Check if any targets exist for the selected month
        $hasTargetsForMonth = DailyTarget::where('year', $selectedYear)
            ->where('month', $selectedMonth)
            ->exists();

        // Check if previous month has targets (for copy feature)
        $prevMonth = $selectedMonth == 1 ? 12 : $selectedMonth - 1;
        $prevYear = $selectedMonth == 1 ? $selectedYear - 1 : $selectedYear;
        $hasPreviousMonthTargets = DailyTarget::where('year', $prevYear)
            ->where('month', $prevMonth)
            ->exists();

        // Generate year options (current year + 1 previous year)
        $currentYear = now()->year;
        $yearOptions = [$currentYear, $currentYear - 1];

        // Month names for dropdown
        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        return view('admin.targets.index', compact(
            'staffUsers',
            'selectedYear',
            'selectedMonth',
            'hasTargetsForMonth',
            'hasPreviousMonthTargets',
            'yearOptions',
            'monthNames'
        ));
    }

    /**
     * Update or create target for a user
     */
    public function updateTarget(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'daily_target_total' => 'required|integer|min:0',
            'daily_target_new' => 'required|integer|min:0',
            'daily_target_existing' => 'required|integer|min:0',
            'working_days' => 'required|integer|min:0',
        ]);

        // Calculate monthly target (rounded to whole number)
        $monthlyTarget = round($request->daily_target_total * $request->working_days);

        // Update or create target using user_id + year + month as unique key
        $target = DailyTarget::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'year' => $request->year,
                'month' => $request->month,
            ],
            [
                'daily_target_total' => $request->daily_target_total,
                'daily_target_new' => $request->daily_target_new,
                'daily_target_existing' => $request->daily_target_existing,
                'working_days' => $request->working_days,
                'monthly_target' => $monthlyTarget,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Target updated successfully',
            'monthly_target' => $monthlyTarget,
        ]);
    }

    /**
     * Save all targets at once
     */
    public function saveAll(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'targets' => 'required|array',
            'targets.*.user_id' => 'required|exists:users,id',
            'targets.*.daily_target_total' => 'required|integer|min:0',
            'targets.*.daily_target_new' => 'required|integer|min:0',
            'targets.*.daily_target_existing' => 'required|integer|min:0',
            'targets.*.working_days' => 'required|integer|min:0',
        ]);

        $year = $request->year;
        $month = $request->month;

        foreach ($request->targets as $targetData) {
            $monthlyTarget = round($targetData['daily_target_total'] * $targetData['working_days']);

            DailyTarget::updateOrCreate(
                [
                    'user_id' => $targetData['user_id'],
                    'year' => $year,
                    'month' => $month,
                ],
                [
                    'daily_target_total' => $targetData['daily_target_total'],
                    'daily_target_new' => $targetData['daily_target_new'],
                    'daily_target_existing' => $targetData['daily_target_existing'],
                    'working_days' => $targetData['working_days'],
                    'monthly_target' => $monthlyTarget,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'All targets saved successfully',
        ]);
    }

    /**
     * Copy targets from previous month to selected month
     */
    public function copyFromPreviousMonth(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $targetYear = $request->year;
        $targetMonth = $request->month;

        // Calculate previous month
        $prevMonth = $targetMonth == 1 ? 12 : $targetMonth - 1;
        $prevYear = $targetMonth == 1 ? $targetYear - 1 : $targetYear;

        // Get previous month's targets
        $previousTargets = DailyTarget::where('year', $prevYear)
            ->where('month', $prevMonth)
            ->get();

        if ($previousTargets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No targets found for the previous month',
            ], 404);
        }

        // Copy each target to the new month
        $copiedCount = 0;
        foreach ($previousTargets as $prevTarget) {
            // Check if target already exists for this user in target month
            $exists = DailyTarget::where('user_id', $prevTarget->user_id)
                ->where('year', $targetYear)
                ->where('month', $targetMonth)
                ->exists();

            if (!$exists) {
                DailyTarget::create([
                    'user_id' => $prevTarget->user_id,
                    'year' => $targetYear,
                    'month' => $targetMonth,
                    'daily_target_total' => $prevTarget->daily_target_total,
                    'daily_target_new' => $prevTarget->daily_target_new,
                    'daily_target_existing' => $prevTarget->daily_target_existing,
                    'working_days' => $prevTarget->working_days,
                    'monthly_target' => $prevTarget->monthly_target,
                ]);
                $copiedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Copied {$copiedCount} targets from previous month",
            'copied_count' => $copiedCount,
        ]);
    }

    /**
     * Toggle hide from dashboard status for a user
     */
    public function toggleHideFromDashboard(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'hide' => 'required',
        ]);

        // Convert string 'true'/'false' to actual boolean
        $hideValue = filter_var($request->hide, FILTER_VALIDATE_BOOLEAN);

        $user = User::findOrFail($request->user_id);
        $user->hide_from_dashboard = $hideValue;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $hideValue ? 'User hidden from dashboard' : 'User visible on dashboard',
            'hide_from_dashboard' => $user->hide_from_dashboard,
        ]);
    }

    /**
     * Get working days calendar for a specific user/month/year
     * Supports half-day (0.5) values
     */
    public function getWorkingDaysCalendar(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $calendar = WorkingDaysCalendar::where('user_id', $request->user_id)
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->first();

        // If no calendar record exists, auto-populate with weekdays from current month
        if (!$calendar) {
            $workingDays = $this->getWeekdaysForMonth($request->year, $request->month);
            $totalWorkingDays = $this->calculateTotalWorkingDays($workingDays);

            // Create the record with all weekdays pre-selected (full days)
            $calendar = WorkingDaysCalendar::create([
                'user_id' => $request->user_id,
                'year' => $request->year,
                'month' => $request->month,
                'working_days' => $workingDays,
                'total_working_days' => $totalWorkingDays,
            ]);

            // Also update daily_targets table for this month
            $target = DailyTarget::where('user_id', $request->user_id)
                ->where('year', $request->year)
                ->where('month', $request->month)
                ->first();

            if ($target) {
                $target->working_days = $totalWorkingDays;
                $target->monthly_target = round($target->daily_target_total * $totalWorkingDays);
                $target->save();
            }
        }

        return response()->json([
            'success' => true,
            'working_days' => $calendar->working_days,
            'total_working_days' => $calendar->total_working_days,
        ]);
    }

    /**
     * Get all weekdays (Mon-Fri) for a given month/year
     * Returns object format { day: value } for half-day support
     */
    private function getWeekdaysForMonth($year, $month)
    {
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = \Carbon\Carbon::today();
        $weekdays = [];

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            // For current month, only include today and future weekdays
            // For past months, include all weekdays
            // For future months, include all weekdays
            if ($year < $today->year || ($year == $today->year && $month < $today->month)) {
                // Past month - include all weekdays
                if ($date->isWeekday()) {
                    $weekdays[$date->day] = 1; // Full day
                }
            } elseif ($year == $today->year && $month == $today->month) {
                // Current month - only future weekdays (or today)
                if ($date->isWeekday() && $date->gte($today)) {
                    $weekdays[$date->day] = 1; // Full day
                }
            } else {
                // Future month - include all weekdays
                if ($date->isWeekday()) {
                    $weekdays[$date->day] = 1; // Full day
                }
            }
        }

        return $weekdays;
    }

    /**
     * Calculate total working days from working_days data
     * Supports both old format (array) and new format (object with values)
     */
    private function calculateTotalWorkingDays($workingDays)
    {
        if (empty($workingDays)) {
            return 0;
        }

        // Check if it's old format (sequential array) or new format (associative with values)
        if (is_array($workingDays) && array_keys($workingDays) === range(0, count($workingDays) - 1)) {
            // Old format: simple array of days - each day = 1
            return count($workingDays);
        }

        // New format: object with day => value (0, 0.5, or 1)
        $total = 0;
        foreach ($workingDays as $day => $value) {
            $total += floatval($value);
        }
        return $total;
    }

    /**
     * Save working days calendar for a specific user/month/year
     * Supports half-day (0.5) values
     */
    public function saveWorkingDaysCalendar(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'working_days' => 'required',
        ]);

        // Parse the working_days - it comes as JSON string from frontend
        $workingDaysData = $request->working_days;
        if (is_string($workingDaysData)) {
            $workingDaysData = json_decode($workingDaysData, true) ?? [];
        }

        // Calculate total working days (sum of all values: 1 for full day, 0.5 for half day)
        $totalWorkingDays = $this->calculateTotalWorkingDays($workingDaysData);

        // Save to working_days_calendar table (for history)
        WorkingDaysCalendar::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'year' => $request->year,
                'month' => $request->month,
            ],
            [
                'working_days' => $workingDaysData,
                'total_working_days' => $totalWorkingDays,
            ]
        );

        // Update daily_targets table for this specific month
        $target = DailyTarget::where('user_id', $request->user_id)
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->first();

        if ($target) {
            $target->working_days = $totalWorkingDays;
            $target->monthly_target = round($target->daily_target_total * $totalWorkingDays);
            $target->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Working days saved successfully',
            'total_working_days' => $totalWorkingDays,
            'monthly_target' => $target ? $target->monthly_target : 0,
        ]);
    }
}
