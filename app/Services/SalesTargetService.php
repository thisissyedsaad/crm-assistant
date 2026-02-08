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

        // =========================================
        // OFF TARGET CALCULATION (MTD-based)
        // =========================================
        // Get excluded user IDs from config
        $excludedUserIds = config('google-sheets.excluded_user_ids', []);

        // Get all staff/admin users who are NOT hidden and NOT excluded
        $includedUsers = User::whereIn('role', ['staff', 'admin'])
            ->where('hide_from_dashboard', false)
            ->whereNotIn('id', $excludedUserIds)
            ->with(['dailyTarget' => function($query) use ($startDate) {
                $query->where('year', $startDate->year)
                      ->where('month', $startDate->month);
            }])
            ->get();

        // Get working days calendars for included users
        $workingDaysCalendars = WorkingDaysCalendar::where('year', $startDate->year)
            ->where('month', $startDate->month)
            ->whereIn('user_id', $includedUsers->pluck('id'))
            ->get()
            ->keyBy('user_id');

        // Group sales by user
        $salesByUser = $validSales->groupBy('csd_id');

        // Calculate totals for included users only
        $totalExpectedOrders = 0;
        $totalOrdersConverted = 0;

        foreach ($includedUsers as $user) {
            $target = $user->dailyTarget;
            $workingDaysInMonth = $target ? $target->working_days : 0;
            $monthlyTarget = $target ? $target->monthly_target : 0;

            // Daily Target = Monthly Target ÷ Working Days in Month
            $dailyTarget = $workingDaysInMonth > 0
                ? $monthlyTarget / $workingDaysInMonth
                : 0;

            // Get Working Days in Range for this user
            $workingDaysInRange = $this->getWorkingDaysInRange(
                $user->id,
                $startDate,
                $endDate,
                $workingDaysCalendars->get($user->id),
                $workingDaysInMonth
            );

            // Expected Orders = Daily Target × Working Days in Range
            $expectedOrders = $dailyTarget * $workingDaysInRange;
            $totalExpectedOrders += $expectedOrders;

            // Orders Converted for this user
            $userOrders = $salesByUser->get($user->id, collect([]))->count();
            $totalOrdersConverted += $userOrders;
        }

        // Off Target = Total Orders Converted - Total Expected Orders
        $offTarget = round($totalOrdersConverted - $totalExpectedOrders);

        // =========================================
        // CONVERSION RATE CALCULATION
        // =========================================
        // Get total leads from Leads tab for the date range
        $totalLeads = $this->googleSheetsService->getTotalLeadsCount($startDate, $endDate);

        // Total New Orders
        $totalNewOrders = $validSales->filter(function ($row) {
            return ($row['business_type'] ?? '') === 'NEW';
        })->count();

        // Overall Conversion Rate = (Total New Orders ÷ Total Leads from Leads tab) × 100
        $conversionRate = $totalLeads > 0
            ? round(($totalNewOrders / $totalLeads) * 100, 1)
            : 0;

        // =========================================
        // NEW METRICS CALCULATIONS
        // =========================================

        // # OF INSURANCE SOLD - Count of rows where insurance_added > 0
        $insuranceSoldCount = $validSales->filter(function ($row) {
            return ($row['insurance_added'] ?? 0) > 0;
        })->count();

        // DRIVERS COST SAVED - Sum of drivers_cost_saved column
        $driversCostSavedTotal = $validSales->sum('drivers_cost_saved');

        // # OF NEW/EXISTING - Count of NEW and EXISTING orders
        $totalExistingOrders = $validSales->filter(function ($row) {
            return ($row['business_type'] ?? '') === 'EXISTING';
        })->count();

        // ORDERS NEEDED - (Total Daily Target × Working Days in Range) − Orders Completed
        // Calculate total expected orders for all users with targets
        $totalExpectedForOrdersNeeded = 0;

        // Get all staff/admin users who have targets (not hidden)
        $usersWithTargets = User::whereIn('role', ['staff', 'admin'])
            ->where('hide_from_dashboard', false)
            ->with(['dailyTarget' => function($query) use ($startDate) {
                $query->where('year', $startDate->year)
                      ->where('month', $startDate->month);
            }])
            ->get();

        // Get working days calendars for all users
        $allWorkingDaysCalendars = WorkingDaysCalendar::where('year', $startDate->year)
            ->where('month', $startDate->month)
            ->whereIn('user_id', $usersWithTargets->pluck('id'))
            ->get()
            ->keyBy('user_id');

        foreach ($usersWithTargets as $user) {
            $target = $user->dailyTarget;
            if (!$target || $target->daily_target_total <= 0) {
                continue; // Skip users without targets
            }

            $workingDaysInMonth = $target->working_days;
            $dailyTargetTotal = $target->daily_target_total;

            // Get Working Days in Range for this user
            $workingDaysInRange = $this->getWorkingDaysInRange(
                $user->id,
                $startDate,
                $endDate,
                $allWorkingDaysCalendars->get($user->id),
                $workingDaysInMonth
            );

            // Expected Orders in Range = Daily Target × Working Days in Range
            $totalExpectedForOrdersNeeded += ($dailyTargetTotal * $workingDaysInRange);
        }

        // Orders Needed = Expected Orders in Range − Orders Completed
        $ordersNeeded = max(0, round($totalExpectedForOrdersNeeded) - $ordersDone);

        return [
            'total_target' => $totalTarget,
            'orders_done' => $ordersDone,
            'off_target' => $offTarget,
            'conversion_rate' => $conversionRate,
            'insurance_sold_count' => $insuranceSoldCount,
            'drivers_cost_saved_total' => $driversCostSavedTotal,
            'new_orders_count' => $totalNewOrders,
            'existing_orders_count' => $totalExistingOrders,
            'orders_needed' => $ordersNeeded,
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

            // Get working days in month (user-defined)
            $workingDaysInMonth = $target ? $target->working_days : 0;

            // Monthly Target (stored in database)
            $monthlyTarget = $target ? $target->monthly_target : 0;

            // Daily Target = Monthly Target ÷ Working Days in Month
            $dailyTarget = $workingDaysInMonth > 0
                ? round($monthlyTarget / $workingDaysInMonth, 2)
                : 0;

            // Monthly targets for display (M/N/E columns)
            $targetTotal = $monthlyTarget;
            $targetNew = $target ? $target->daily_target_new * $workingDaysInMonth : 0;
            $targetExisting = $target ? $target->daily_target_existing * $workingDaysInMonth : 0;

            // Actual orders (filter by sale > 0) - Orders Converted in Range
            $validSales = $userSales->filter(fn($row) => ($row['sale'] ?? 0) > 0);
            $actualTotal = $validSales->count();
            $actualNew = $validSales->filter(fn($row) => ($row['business_type'] ?? '') === 'NEW')->count();
            $actualExisting = $validSales->filter(fn($row) => ($row['business_type'] ?? '') === 'EXISTING')->count();

            // Working Days in Range = working days within selected date range
            $workingDaysInRange = $this->getWorkingDaysInRange(
                $user->id,
                $startDate,
                $endDate,
                $workingDaysCalendars->get($user->id),
                $workingDaysInMonth
            );

            // Expected Orders (Range) = Daily Target × Working Days in Range
            $expectedOrdersRange = $dailyTarget * $workingDaysInRange;

            // Off Target (Range) = Orders Converted (Range) − Expected Orders (Range)
            // Negative means behind target, Positive means ahead of target
            $offTargetRange = $actualTotal - $expectedOrdersRange;

            // Progress (%) = Orders Converted (Range) ÷ Expected Orders (Range) × 100
            $progressPercent = $expectedOrdersRange > 0
                ? round(($actualTotal / $expectedOrdersRange) * 100, 1)
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
                'off_target' => $offTargetRange,
                'expected_range' => $expectedOrdersRange,
                'working_days_in_range' => $workingDaysInRange,
                'working_days_total' => $workingDaysInMonth,
                'daily_target' => $dailyTarget,
                'on_target_percent' => $progressPercent,
                'conversion_rate' => 0, // Placeholder
                'new_business_rate' => $newBusinessRate,
            ];
        });
    }

    /**
     * Calculate working days within the selected date range
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param WorkingDaysCalendar|null $calendar
     * @param int $fallbackWorkingDays
     * @return int
     */
    protected function getWorkingDaysInRange(
        int $userId,
        Carbon $startDate,
        Carbon $endDate,
        ?WorkingDaysCalendar $calendar,
        int $fallbackWorkingDays
    ): int {
        // Use today if endDate is in the future (can't have orders for future days)
        $today = Carbon::now();
        $effectiveEndDate = $endDate->gt($today) ? $today : $endDate;

        if ($calendar && !empty($calendar->working_days)) {
            // Count working days WITHIN the date range (>= startDate AND <= effectiveEndDate)
            $workingDays = collect($calendar->working_days);

            return $workingDays->filter(function ($day) use ($startDate, $effectiveEndDate) {
                $dayDate = Carbon::create($startDate->year, $startDate->month, $day);
                // Must be >= startDate AND <= effectiveEndDate
                return $dayDate->gte($startDate->startOfDay()) && $dayDate->lte($effectiveEndDate);
            })->count();
        }

        // Fallback: estimate based on proportion of range within the month
        $daysInMonth = $startDate->daysInMonth;
        $rangeStart = $startDate->day;
        $rangeEnd = min($effectiveEndDate->day, $daysInMonth);
        $daysInRange = max(0, $rangeEnd - $rangeStart + 1);
        $proportionOfMonth = $daysInRange / $daysInMonth;

        return (int) round($fallbackWorkingDays * $proportionOfMonth);
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
     * Get dashboard stats for a specific user (Staff Dashboard)
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getUserDashboardStats(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $salesData = $this->googleSheetsService->getSalesData($startDate, $endDate);

        // Filter sales data for this specific user
        $userSales = $salesData->filter(function ($row) use ($userId) {
            return ($row['csd_id'] ?? null) == $userId && ($row['sale'] ?? 0) > 0;
        });

        // Get user's target for this month
        $userTarget = DailyTarget::where('user_id', $userId)
            ->where('year', $startDate->year)
            ->where('month', $startDate->month)
            ->first();

        // Get working days calendar
        $workingDaysCalendar = WorkingDaysCalendar::where('user_id', $userId)
            ->where('year', $startDate->year)
            ->where('month', $startDate->month)
            ->first();

        $workingDaysInMonth = $userTarget ? $userTarget->working_days : 0;

        // TOTAL stats
        $monthlyTargetTotal = $userTarget ? $userTarget->monthly_target : 0;
        $dailyTargetTotal = $userTarget ? $userTarget->daily_target_total : 0;
        $ordersConvertedTotal = $userSales->count();
        $onTargetPercentTotal = $monthlyTargetTotal > 0
            ? round(($ordersConvertedTotal / $monthlyTargetTotal) * 100, 1)
            : 0;

        // NEW stats
        $newSales = $userSales->filter(fn($row) => ($row['business_type'] ?? '') === 'NEW');
        $monthlyTargetNew = $userTarget ? ($userTarget->daily_target_new * $workingDaysInMonth) : 0;
        $dailyTargetNew = $userTarget ? $userTarget->daily_target_new : 0;
        $ordersConvertedNew = $newSales->count();
        $onTargetPercentNew = $monthlyTargetNew > 0
            ? round(($ordersConvertedNew / $monthlyTargetNew) * 100, 1)
            : 0;

        // EXISTING stats
        $existingSales = $userSales->filter(fn($row) => ($row['business_type'] ?? '') === 'EXISTING');
        $monthlyTargetExisting = $userTarget ? ($userTarget->daily_target_existing * $workingDaysInMonth) : 0;
        $dailyTargetExisting = $userTarget ? $userTarget->daily_target_existing : 0;
        $ordersConvertedExisting = $existingSales->count();
        $onTargetPercentExisting = $monthlyTargetExisting > 0
            ? round(($ordersConvertedExisting / $monthlyTargetExisting) * 100, 1)
            : 0;

        // Insurance Sold (MTD) - count where insurance_added > 0
        $insuranceSoldCount = $userSales->filter(fn($row) => ($row['insurance_added'] ?? 0) > 0)->count();

        // Drivers Cost Saved (MTD) - count where drivers_cost_saved > 0
        $driversCostSavedCount = $userSales->filter(fn($row) => ($row['drivers_cost_saved'] ?? 0) > 0)->count();

        // Orders Needed This Week
        $ordersNeededThisWeek = $this->getUserOrdersNeededThisWeek($userId, $userTarget, $workingDaysCalendar);

        return [
            'total' => [
                'monthly_target' => $monthlyTargetTotal,
                'daily_target' => $dailyTargetTotal,
                'orders_converted' => $ordersConvertedTotal,
                'on_target_percent' => $onTargetPercentTotal,
            ],
            'new' => [
                'monthly_target' => $monthlyTargetNew,
                'daily_target' => $dailyTargetNew,
                'orders_converted' => $ordersConvertedNew,
                'on_target_percent' => $onTargetPercentNew,
            ],
            'existing' => [
                'monthly_target' => $monthlyTargetExisting,
                'daily_target' => $dailyTargetExisting,
                'orders_converted' => $ordersConvertedExisting,
                'on_target_percent' => $onTargetPercentExisting,
            ],
            'insurance_sold_count' => $insuranceSoldCount,
            'drivers_cost_saved_count' => $driversCostSavedCount,
            'orders_needed_this_week' => $ordersNeededThisWeek,
        ];
    }

    /**
     * Calculate orders needed this week for a user
     *
     * @param int $userId
     * @param DailyTarget|null $userTarget
     * @param WorkingDaysCalendar|null $workingDaysCalendar
     * @return int
     */
    protected function getUserOrdersNeededThisWeek(int $userId, ?DailyTarget $userTarget, ?WorkingDaysCalendar $workingDaysCalendar): int
    {
        if (!$userTarget || $userTarget->daily_target_total <= 0) {
            return 0;
        }

        $today = Carbon::now();
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);

        // Count remaining working days from today to end of week
        $remainingWorkingDays = 0;

        if ($workingDaysCalendar && !empty($workingDaysCalendar->working_days)) {
            $workingDays = collect($workingDaysCalendar->working_days);
            $currentDate = $today->copy();

            while ($currentDate->lte($endOfWeek)) {
                if ($workingDays->contains($currentDate->day)) {
                    $remainingWorkingDays++;
                }
                $currentDate->addDay();
            }
        } else {
            // Fallback: count weekdays (Mon-Fri) remaining this week
            $currentDate = $today->copy();
            while ($currentDate->lte($endOfWeek)) {
                if (!$currentDate->isWeekend()) {
                    $remainingWorkingDays++;
                }
                $currentDate->addDay();
            }
        }

        return $userTarget->daily_target_total * $remainingWorkingDays;
    }

    /**
     * Get chart data for user's MTD Orders vs Target
     *
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getUserOrdersVsTargetChart(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $salesData = $this->googleSheetsService->getSalesData($startDate, $endDate);

        // Filter sales data for this specific user
        $userSales = $salesData->filter(function ($row) use ($userId) {
            return ($row['csd_id'] ?? null) == $userId && ($row['sale'] ?? 0) > 0;
        });

        // Get user's target
        $userTarget = DailyTarget::where('user_id', $userId)
            ->where('year', $startDate->year)
            ->where('month', $startDate->month)
            ->first();

        // Get working days calendar
        $workingDaysCalendar = WorkingDaysCalendar::where('user_id', $userId)
            ->where('year', $startDate->year)
            ->where('month', $startDate->month)
            ->first();

        $dailyTarget = $userTarget ? $userTarget->daily_target_total : 0;
        $workingDays = $workingDaysCalendar ? collect($workingDaysCalendar->working_days) : collect([]);

        // Group sales by date
        $salesByDate = $userSales->groupBy(function ($row) {
            return $row['date']->format('Y-m-d');
        });

        // Generate data for each day of the month
        $labels = [];
        $targetData = [];
        $actualData = [];
        $cumulativeActual = 0;
        $cumulativeTarget = 0;

        $today = Carbon::now();
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayNumber = $currentDate->day;
            $labels[] = $currentDate->format('d');

            // Check if this is a working day
            $isWorkingDay = false;
            if ($workingDays->isNotEmpty()) {
                $isWorkingDay = $workingDays->contains($dayNumber);
            } else {
                // Fallback: weekdays are working days
                $isWorkingDay = !$currentDate->isWeekend();
            }

            // Target line: cumulative target (only add on working days)
            if ($isWorkingDay && $currentDate->lte($today)) {
                $cumulativeTarget += $dailyTarget;
            }
            $targetData[] = round($cumulativeTarget, 1);

            // Actual orders: cumulative count
            if ($currentDate->lte($today)) {
                $dayOrders = $salesByDate->get($dateKey, collect([]))->count();
                $cumulativeActual += $dayOrders;
            }
            $actualData[] = $cumulativeActual;

            $currentDate->addDay();
        }

        return [
            'labels' => $labels,
            'target' => $targetData,
            'actual' => $actualData,
        ];
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
