<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DailyTarget;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    /**
     * Display the targets management page
     */
    public function index()
    {
        // Get all staff users with their targets
        $staffUsers = User::where('role', 'staff')
            ->with('dailyTarget')
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
}