<?php

namespace App\Services;

use App\Models\DailyTarget;
use App\Models\User;
use App\Models\WorkingDaysCalendar;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesTargetService
{
    protected GoogleSheetsService $googleSheetsService;

    public function __construct(GoogleSheetsService $googleSheetsService)
    {
        $this->googleSheetsService = $googleSheetsService;
    }

    /**
     * Get dashboard summary statistics
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getDashboardStats(Carbon $startDate, Carbon $endDate): array
    {
        $salesData = $this->googleSheetsService->getSalesData($startDate, $endDate);
        $targets = $this->getTargetsForPeriod($startDate, $endDate);

        // Total Target - sum of all staff targets for the period
        $totalTarget = $targets->sum('monthly_target');

        // Orders Done - count orders where sale > 0
        $ordersDone = $salesData->filter(function ($row) {
            return ($row['sale'] ?? 0) > 0;
        })->count();

        // Off Target - Target - Orders Done
        $offTarget = $totalTarget - $ordersDone;

        // Conversion Rate - placeholder for now
        $conversionRate = 0;

        return [
            'total_target' => $totalTarget,
            'orders_done' => $ordersDone,
            'off_target' => $offTarget,
            'conversion_rate' => $conversionRate,
        ];
    }

    /**
     * Get team performance data for the table
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    public function getTeamPerformance(Carbon $startDate, Carbon $endDate): Collection
    {
        $salesData = $this->googleSheetsService->getSalesData($startDate, $endDate);

        // Get all staff users with their targets
        $staffUsers = User::where('role', 'staff')
            ->with('dailyTarget')
            ->get();

        // Group sales data by CSD ID (user_id)
        $salesByUser = $salesData->groupBy('csd_id');

        return $staffUsers->map(function ($user) use ($salesByUser, $startDate, $endDate) {
            $userSales = $salesByUser->get($user->id, collect([]));
            $target = $user->dailyTarget;

            // Calculate targets for the period
            $workingDays = $this->getWorkingDaysForPeriod($user->id, $startDate, $endDate);

            // Monthly targets (M = total, N = new, E = existing)
            $targetTotal = $target ? $target->daily_target_total * $workingDays : 0;
            $targetNew = $target ? $target->daily_target_new * $workingDays : 0;
            $targetExisting = $target ? $target->daily_target_existing * $workingDays : 0;

            // Actual orders (filter by sale > 0)
            $validSales = $userSales->filter(fn($row) => ($row['sale'] ?? 0) > 0);
            $actualTotal = $validSales->count();
            $actualNew = $validSales->filter(fn($row) => ($row['business_type'] ?? '') === 'NEW')->count();
            $actualExisting = $validSales->filter(fn($row) => ($row['business_type'] ?? '') === 'EXISTING')->count();

            // Off Target
            $offTarget = $targetTotal - $actualTotal;

            // Progress (Target) - percentage
            $progressTarget = $targetTotal > 0
                ? round((($targetTotal - $actualTotal) / $targetTotal) * 100, 1)
                : 0;

            // New Business Conversion Rate
            $newBusinessRate = $actualTotal > 0
                ? round(($actualNew / $actualTotal) * 100, 1)
                : 0;

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'target_total' => $targetTotal,
                'target_new' => $targetNew,
                'target_existing' => $targetExisting,
                'actual_total' => $actualTotal,
                'actual_new' => $actualNew,
                'actual_existing' => $actualExisting,
                'off_target' => $offTarget,
                'progress_target' => $progressTarget,
                'conversion_rate' => 0, // Placeholder
                'new_business_rate' => $newBusinessRate,
            ];
        });
    }

    /**
     * Get New vs Existing Orders by day for the chart
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getNewVsExistingByDay(Carbon $startDate, Carbon $endDate): array
    {
        $salesData = $this->googleSheetsService->getSalesData($startDate, $endDate);

        // Filter valid sales
        $validSales = $salesData->filter(fn($row) => ($row['sale'] ?? 0) > 0);

        // Group by date
        $byDate = $validSales->groupBy(function ($row) {
            return $row['date']->format('Y-m-d');
        });

        // Generate all dates in range
        $dates = [];
        $newData = [];
        $existingData = [];

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            $dates[] = $currentDate->format('d');

            $dayData = $byDate->get($dateKey, collect([]));
            $newData[] = $dayData->filter(fn($row) => ($row['business_type'] ?? '') === 'NEW')->count();
            $existingData[] = $dayData->filter(fn($row) => ($row['business_type'] ?? '') === 'EXISTING')->count();

            $currentDate->addDay();
        }

        return [
            'labels' => $dates,
            'new' => $newData,
            'existing' => $existingData,
        ];
    }

    /**
     * Get Orders by Sales Rep for the chart
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getOrdersByRep(Carbon $startDate, Carbon $endDate): array
    {
        $salesData = $this->googleSheetsService->getSalesData($startDate, $endDate);

        // Filter valid sales and group by user
        $validSales = $salesData->filter(fn($row) => ($row['sale'] ?? 0) > 0);
        $byUser = $validSales->groupBy('csd_id');

        // Get user names
        $users = User::where('role', 'staff')->pluck('name', 'id');

        $labels = [];
        $data = [];

        foreach ($byUser as $userId => $userSales) {
            $labels[] = $users->get($userId, 'Unknown');
            $data[] = $userSales->count();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get New Business orders by rep
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getNewBusinessByRep(Carbon $startDate, Carbon $endDate): array
    {
        $salesData = $this->googleSheetsService->getSalesData($startDate, $endDate);

        // Filter new business sales
        $newSales = $salesData->filter(function ($row) {
            return ($row['sale'] ?? 0) > 0 && ($row['business_type'] ?? '') === 'NEW';
        });

        $byUser = $newSales->groupBy('csd_id');
        $users = User::where('role', 'staff')->pluck('name', 'id');

        $labels = [];
        $data = [];

        foreach ($users as $userId => $name) {
            $labels[] = $name;
            $data[] = $byUser->get($userId, collect([]))->count();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get Existing Business orders by rep
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getExistingBusinessByRep(Carbon $startDate, Carbon $endDate): array
    {
        $salesData = $this->googleSheetsService->getSalesData($startDate, $endDate);

        // Filter existing business sales
        $existingSales = $salesData->filter(function ($row) {
            return ($row['sale'] ?? 0) > 0 && ($row['business_type'] ?? '') === 'EXISTING';
        });

        $byUser = $existingSales->groupBy('csd_id');
        $users = User::where('role', 'staff')->pluck('name', 'id');

        $labels = [];
        $data = [];

        foreach ($users as $userId => $name) {
            $labels[] = $name;
            $data[] = $byUser->get($userId, collect([]))->count();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get targets for the specified period
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    protected function getTargetsForPeriod(Carbon $startDate, Carbon $endDate): Collection
    {
        return DailyTarget::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'staff');
            })
            ->get();
    }

    /**
     * Get working days count for a user in the specified period
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return int
     */
    protected function getWorkingDaysForPeriod(int $userId, Carbon $startDate, Carbon $endDate): int
    {
        $calendar = WorkingDaysCalendar::where('user_id', $userId)
            ->where('year', $startDate->year)
            ->where('month', $startDate->month)
            ->first();

        if ($calendar && !empty($calendar->working_days)) {
            // Count working days within the date range
            $workingDays = collect($calendar->working_days);

            return $workingDays->filter(function ($day) use ($startDate, $endDate) {
                $dayDate = Carbon::create($startDate->year, $startDate->month, $day);
                return $dayDate->gte($startDate) && $dayDate->lte($endDate);
            })->count();
        }

        // Fallback: use the working_days from daily_target
        $target = DailyTarget::where('user_id', $userId)->first();
        return $target ? $target->working_days : 0;
    }

    /**
     * Clear the Google Sheets cache
     *
     * @return void
     */
    public function refreshCache(): void
    {
        $this->googleSheetsService->clearCache();
    }

    /**
     * Check if the service is properly configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->googleSheetsService->isConfigured();
    }

    /**
     * Get the current cache TTL
     *
     * @return int
     */
    public function getCacheTtl(): int
    {
        return $this->googleSheetsService->getCacheTtl();
    }
}
