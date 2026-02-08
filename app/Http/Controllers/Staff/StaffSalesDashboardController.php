<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SalesTargetService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffSalesDashboardController extends Controller
{
    protected SalesTargetService $salesTargetService;

    public function __construct(SalesTargetService $salesTargetService)
    {
        $this->salesTargetService = $salesTargetService;
    }

    /**
     * Display the staff sales dashboard
     * Admin/Super-admin can view any user's dashboard by passing user_id
     *
     * @param Request $request
     * @param int|null $user_id Optional user ID for admin viewing other users
     * @return View
     */
    public function index(Request $request, ?int $user_id = null): View
    {
        $currentUser = auth()->user();
        $isAdmin = in_array($currentUser->role, ['admin', 'super-admin']);

        // Determine which user's dashboard to show
        $targetUserId = $user_id;
        $viewingOtherUser = false;
        $targetUser = null;

        if ($user_id && $isAdmin) {
            // Admin viewing another user's dashboard
            $targetUser = User::find($user_id);
            if (!$targetUser) {
                abort(404, 'User not found');
            }
            $targetUserId = $user_id;
            $viewingOtherUser = true;
        } else {
            // User viewing their own dashboard
            $targetUserId = $currentUser->id;
            $targetUser = $currentUser;
        }

        // Masking: Only apply for staff viewing their own dashboard (if env is true)
        // Admin/Super-admin NEVER see masked numbers
        $maskNumbers = false;
        if (!$isAdmin && config('google-sheets.staff_dashboard_mask_numbers', false)) {
            $maskNumbers = true;
        }

        // Check if Google Sheets is configured
        if (!$this->salesTargetService->isConfigured()) {
            return view('staff.dashboard.index', [
                'error' => 'Google Sheets integration is not configured. Please contact an administrator.',
                'stats' => null,
                'chartData' => null,
                'startDate' => null,
                'endDate' => null,
                'isAdmin' => $isAdmin,
                'cacheTtl' => 0,
                'maskNumbers' => $maskNumbers,
                'viewingUser' => $targetUser,
                'viewingOtherUser' => $viewingOtherUser,
            ]);
        }

        // Default to current month MTD
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now();

        // Admin users can use date filter
        if ($isAdmin && $request->has('start_date') && $request->has('end_date')) {
            try {
                $startDate = Carbon::parse($request->input('start_date'));
                $endDate = Carbon::parse($request->input('end_date'));
            } catch (\Exception $e) {
                // Keep default dates if parsing fails
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now();
            }
        }

        try {
            // Get dashboard stats for target user
            $stats = $this->salesTargetService->getUserDashboardStats($targetUserId, $startDate, $endDate);
            $chartData = $this->salesTargetService->getUserOrdersVsTargetChart($targetUserId, $startDate, $endDate);
            $cacheTtl = $this->salesTargetService->getCacheTtl();

            return view('staff.dashboard.index', [
                'stats' => $stats,
                'chartData' => $chartData,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'isAdmin' => $isAdmin,
                'cacheTtl' => $cacheTtl,
                'error' => null,
                'maskNumbers' => $maskNumbers,
                'viewingUser' => $targetUser,
                'viewingOtherUser' => $viewingOtherUser,
            ]);
        } catch (\Exception $e) {
            return view('staff.dashboard.index', [
                'error' => 'Error loading dashboard data: ' . $e->getMessage(),
                'stats' => null,
                'chartData' => null,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'isAdmin' => $isAdmin,
                'cacheTtl' => 0,
                'maskNumbers' => $maskNumbers,
                'viewingUser' => $targetUser,
                'viewingOtherUser' => $viewingOtherUser,
            ]);
        }
    }

    /**
     * Get chart data via AJAX (for date range updates)
     *
     * @param Request $request
     * @param int|null $user_id Optional user ID for admin viewing other users
     * @return JsonResponse
     */
    public function getChartData(Request $request, ?int $user_id = null): JsonResponse
    {
        $currentUser = auth()->user();
        $isAdmin = in_array($currentUser->role, ['admin', 'super-admin']);

        // Determine which user's data to fetch
        $targetUserId = ($user_id && $isAdmin) ? $user_id : $currentUser->id;

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now();

        if ($isAdmin && $request->has('start_date') && $request->has('end_date')) {
            try {
                $startDate = Carbon::parse($request->input('start_date'));
                $endDate = Carbon::parse($request->input('end_date'));
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format'], 400);
            }
        }

        try {
            $stats = $this->salesTargetService->getUserDashboardStats($targetUserId, $startDate, $endDate);
            $chartData = $this->salesTargetService->getUserOrdersVsTargetChart($targetUserId, $startDate, $endDate);

            return response()->json([
                'stats' => $stats,
                'chartData' => $chartData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
