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

        // Total Target - sum of all staff targets (calculated fresh: daily_target_total × working_days)
        $totalTarget = $targets->sum(function ($target) {
            return $target->daily_target_total * $target->working_days;
        });

        // Filter valid sales (sale > 0)
        $validSales = $salesData->filter(function ($row) {
            return ($row['sale'] ?? 0) > 0;
        });

        // Orders Done - count orders where sale > 0
        $ordersDone = $validSales->count();

        // Off Target - Target - Orders Done
        $offTarget = $totalTarget - $ordersDone;

        // Overall Conversion Rate = (Total New Orders ÷ Total Orders) × 100
        $totalNewOrders = $validSales->filter(function ($row) {
            return ($row['business_type'] ?? '') === 'NEW';
        })->count();

        $conversionRate = $ordersDone > 0
            ? round(($totalNewOrders / $ordersDone) * 100, 1)
            : 0;

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

        // Get all staff and admin users with their targets for the selected month/year
        // Filter out users who are hidden from dashboard
        $staffUsers = User::whereIn('role', ['staff', 'admin'])
            ->where('hide_from_dashboard', false)
            ->with(['dailyTarget' => function($query) use ($startDate) {
                $query->where('year', $startDate->year)
                      ->where('month', $startDate->month);
            }])
            ->get();

        // Group sales data by CSD ID (user_id)
        $salesByUser = $salesData->groupBy('csd_id');

        // Get all working days calendars for this month
        $workingDaysCalendars = WorkingDaysCalendar::where('year', $startDate->year)
            ->where('month', $startDate->month)
            ->get()
            ->keyBy('user_id');

        return $staffUsers->map(function ($user) use ($salesByUser, $startDate, $endDate, $workingDaysCalendars) {
            $userSales = $salesByUser->get($user->id, collect([]));
            $target = $user->dailyTarget;

            // Monthly targets calculated fresh from daily targets × working days
            $workingDaysInMonth = $target ? $target->working_days : 0;
            $dailyTarget = $target ? $target->daily_target_total : 0;
            $targetTotal = $dailyTarget * $workingDaysInMonth;
            $targetNew = $target ? $target->daily_target_new * $workingDaysInMonth : 0;
            $targetExisting = $target ? $target->daily_target_existing * $workingDaysInMonth : 0;

            // Actual orders (filter by sale > 0)
            $validSales = $userSales->filter(fn($row) => ($row['sale'] ?? 0) > 0);
            $actualTotal = $validSales->count();
            $actualNew = $validSales->filter(fn($row) => ($row['business_type'] ?? '') === 'NEW')->count();
            $actualExisting = $validSales->filter(fn($row) => ($row['business_type'] ?? '') === 'EXISTING')->count();

            // Calculate Working Days Elapsed (MTD)
            $workingDaysElapsed = $this->getWorkingDaysElapsed(
                $user->id,
                $startDate,
                $endDate,
                $workingDaysCalendars->get($user->id),
                $workingDaysInMonth
            );

            // Expected Orders MTD = Daily Target × Working Days Elapsed
            $expectedOrdersMtd = $dailyTarget * $workingDaysElapsed;

            // Off Target (MTD) = Actual Orders - Expected Orders MTD
            // Negative means behind target, Positive means ahead of target
            $offTargetMtd = $actualTotal - $expectedOrdersMtd;

            // On Target % = (Actual Orders / Expected Orders MTD) × 100
            $onTargetPercent = $expectedOrdersMtd > 0
                ? round(($actualTotal / $expectedOrdersMtd) * 100, 1)
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
                'off_target' => $offTargetMtd, // Now MTD based
                'expected_mtd' => $expectedOrdersMtd, // Expected orders so far
                'working_days_elapsed' => $workingDaysElapsed,
                'working_days_total' => $workingDaysInMonth,
                'daily_target' => $dailyTarget,
                'on_target_percent' => $onTargetPercent, // Progress percentage
                'conversion_rate' => 0, // Placeholder
                'new_business_rate' => $newBusinessRate,
            ];
        });
    }

    /**
     * Calculate working days elapsed up to the end date
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param WorkingDaysCalendar|null $calendar
     * @param int $fallbackWorkingDays
     * @return int
     */
    protected function getWorkingDaysElapsed(
        int $userId,
        Carbon $startDate,
        Carbon $endDate,
        ?WorkingDaysCalendar $calendar,
        int $fallbackWorkingDays
    ): int {
        // Use today if endDate is in the future
        $today = Carbon::now();
        $effectiveEndDate = $endDate->gt($today) ? $today : $endDate;

        if ($calendar && !empty($calendar->working_days)) {
            // Count working days that have elapsed (up to effective end date)
            $workingDays = collect($calendar->working_days);

            return $workingDays->filter(function ($day) use ($startDate, $effectiveEndDate) {
                $dayDate = Carbon::create($startDate->year, $startDate->month, $day);
                return $dayDate->lte($effectiveEndDate);
            })->count();
        }

        // Fallback: estimate based on proportion of month elapsed
        // This is a rough estimate if no calendar is set
        $daysInMonth = $startDate->daysInMonth;
        $dayOfMonth = min($effectiveEndDate->day, $daysInMonth);
        $proportionElapsed = $dayOfMonth / $daysInMonth;

        return (int) round($fallbackWorkingDays * $proportionElapsed);
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
     * Get Orders by Sales Rep for the chart (sorted high to low)
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

        // Get user names (excluding hidden users)
        $users = User::whereIn('role', ['staff', 'admin'])
            ->where('hide_from_dashboard', false)
            ->pluck('name', 'id');

        // Build array with user data and counts
        $userData = [];
        foreach ($users as $userId => $name) {
            $count = $byUser->get($userId, collect([]))->count();
            $userData[] = [
                'user_id' => $userId,
                'name' => $name,
                'count' => $count,
            ];
        }

        // Sort by count descending (high to low)
        usort($userData, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        // Extract sorted data
        $labels = [];
        $data = [];
        $sortedUserIds = [];

        foreach ($userData as $user) {
            $labels[] = $user['name'];
            $data[] = $user['count'];
            $sortedUserIds[] = $user['user_id'];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'sorted_user_ids' => $sortedUserIds, // Pass sorted order to other charts
        ];
    }

    /**
     * Get New Business orders by rep (follows same order as total orders)
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $sortedUserIds Optional sorted user IDs from getOrdersByRep
     * @return array
     */
    public function getNewBusinessByRep(Carbon $startDate, Carbon $endDate, array $sortedUserIds = []): array
    {
        $salesData = $this->googleSheetsService->getSalesData($startDate, $endDate);

        // Filter new business sales
        $newSales = $salesData->filter(function ($row) {
            return ($row['sale'] ?? 0) > 0 && ($row['business_type'] ?? '') === 'NEW';
        });

        $byUser = $newSales->groupBy('csd_id');
        $users = User::whereIn('role', ['staff', 'admin'])
            ->where('hide_from_dashboard', false)
            ->pluck('name', 'id');

        $labels = [];
        $data = [];

        // Use sorted order if provided, otherwise use default order
        if (!empty($sortedUserIds)) {
            foreach ($sortedUserIds as $userId) {
                if ($users->has($userId)) {
                    $labels[] = $users->get($userId);
                    $data[] = $byUser->get($userId, collect([]))->count();
                }
            }
        } else {
            foreach ($users as $userId => $name) {
                $labels[] = $name;
                $data[] = $byUser->get($userId, collect([]))->count();
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get Existing Business orders by rep (follows same order as total orders)
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $sortedUserIds Optional sorted user IDs from getOrdersByRep
     * @return array
     */
    public function getExistingBusinessByRep(Carbon $startDate, Carbon $endDate, array $sortedUserIds = []): array
    {
        $salesData = $this->googleSheetsService->getSalesData($startDate, $endDate);

        // Filter existing business sales
        $existingSales = $salesData->filter(function ($row) {
            return ($row['sale'] ?? 0) > 0 && ($row['business_type'] ?? '') === 'EXISTING';
        });

        $byUser = $existingSales->groupBy('csd_id');
        $users = User::whereIn('role', ['staff', 'admin'])
            ->where('hide_from_dashboard', false)
            ->pluck('name', 'id');

        $labels = [];
        $data = [];

        // Use sorted order if provided, otherwise use default order
        if (!empty($sortedUserIds)) {
            foreach ($sortedUserIds as $userId) {
                if ($users->has($userId)) {
                    $labels[] = $users->get($userId);
                    $data[] = $byUser->get($userId, collect([]))->count();
                }
            }
        } else {
            foreach ($users as $userId => $name) {
                $labels[] = $name;
                $data[] = $byUser->get($userId, collect([]))->count();
            }
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
        // Get targets for the specific month/year of the start date
        // Filter out users who are hidden from dashboard
        return DailyTarget::with('user')
            ->where('year', $startDate->year)
            ->where('month', $startDate->month)
            ->whereHas('user', function ($query) {
                $query->whereIn('role', ['staff', 'admin'])
                      ->where('hide_from_dashboard', false);
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
