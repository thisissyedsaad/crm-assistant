<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DailyTarget;
use App\Models\WorkingDaysCalendar;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    /**
     * Display the targets management page
     */
    public function index()
    {
        // Get current month/year
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // Get all staff users with their targets
        $staffUsers = User::where('role', 'staff')
            ->with(['dailyTarget', 'workingDaysCalendar' => function($query) use ($currentYear, $currentMonth) {
                $query->where('year', $currentYear)
                      ->where('month', $currentMonth);
            }])
            ->orderBy('name')
            ->get();

        return view('admin.targets.index', compact('staffUsers'));
    }

    /**
     * Update or create target for a user
     */
    public function updateTarget(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'daily_target_total' => 'required|integer|min:0',
            'daily_target_new' => 'required|integer|min:0',
            'daily_target_existing' => 'required|integer|min:0',
            'working_days' => 'required|integer|min:0',
        ]);

        // Calculate monthly target
        $monthlyTarget = $request->daily_target_total * $request->working_days;

        // Update or create target
        $target = DailyTarget::updateOrCreate(
            ['user_id' => $request->user_id],
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
            'targets' => 'required|array',
            'targets.*.user_id' => 'required|exists:users,id',
            'targets.*.daily_target_total' => 'required|integer|min:0',
            'targets.*.daily_target_new' => 'required|integer|min:0',
            'targets.*.daily_target_existing' => 'required|integer|min:0',
            'targets.*.working_days' => 'required|integer|min:0',
        ]);

        foreach ($request->targets as $targetData) {
            $monthlyTarget = $targetData['daily_target_total'] * $targetData['working_days'];

            DailyTarget::updateOrCreate(
                ['user_id' => $targetData['user_id']],
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
     * Get working days calendar for a specific user/month/year
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

            // Create the record with all weekdays pre-selected
            $calendar = WorkingDaysCalendar::create([
                'user_id' => $request->user_id,
                'year' => $request->year,
                'month' => $request->month,
                'working_days' => $workingDays,
                'total_working_days' => count($workingDays),
            ]);

            // Also update daily_targets table
            $target = DailyTarget::where('user_id', $request->user_id)->first();
            if ($target) {
                $target->working_days = count($workingDays);
                $target->monthly_target = $target->daily_target_total * count($workingDays);
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
     */
    private function getWeekdaysForMonth($year, $month)
    {
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = \Carbon\Carbon::today();
        $weekdays = [];

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            // Only include future weekdays (or today)
            if ($date->isWeekday() && $date->gte($today)) {
                $weekdays[] = $date->day;
            }
        }

        return $weekdays;
    }

    /**
     * Save working days calendar for a specific user/month/year
     */
    public function saveWorkingDaysCalendar(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'working_days' => 'required|array',
        ]);

        $workingDaysArray = $request->working_days;
        $totalWorkingDays = count($workingDaysArray);

        // Save to working_days_calendar table (for history)
        WorkingDaysCalendar::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'year' => $request->year,
                'month' => $request->month,
            ],
            [
                'working_days' => $workingDaysArray,
                'total_working_days' => $totalWorkingDays,
            ]
        );

        // Update daily_targets table (for current calculations)
        $target = DailyTarget::where('user_id', $request->user_id)->first();
        if ($target) {
            $target->working_days = $totalWorkingDays;
            $target->monthly_target = $target->daily_target_total * $totalWorkingDays;
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