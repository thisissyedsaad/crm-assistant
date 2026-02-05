<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SalesTargetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SalesTargetDashboardController extends Controller
{
    protected SalesTargetService $salesTargetService;

    public function __construct(SalesTargetService $salesTargetService)
    {
        $this->salesTargetService = $salesTargetService;
    }

    /**
     * Display the sales target dashboard
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Default date range: start of current month to today
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))
            : Carbon::now()->startOfMonth();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))
            : Carbon::now();

        // Check if Google Sheets is configured
        $isConfigured = $this->salesTargetService->isConfigured();

        // Get dashboard data if configured
        $stats = [];
        $teamPerformance = collect([]);
        $chartData = [];

        if ($isConfigured) {
            try {
                $stats = $this->salesTargetService->getDashboardStats($startDate, $endDate);
                $teamPerformance = $this->salesTargetService->getTeamPerformance($startDate, $endDate);

                // Get orders by rep first (sorted high to low) and extract sorted user IDs
                $ordersByRep = $this->salesTargetService->getOrdersByRep($startDate, $endDate);
                $sortedUserIds = $ordersByRep['sorted_user_ids'] ?? [];

                // Pass sorted user IDs to other charts so they follow the same order
                $chartData = [
                    'newVsExisting' => $this->salesTargetService->getNewVsExistingByDay($startDate, $endDate),
                    'ordersByRep' => $ordersByRep,
                    'newBusinessByRep' => $this->salesTargetService->getNewBusinessByRep($startDate, $endDate, $sortedUserIds),
                    'existingBusinessByRep' => $this->salesTargetService->getExistingBusinessByRep($startDate, $endDate, $sortedUserIds),
                ];
            } catch (\Exception $e) {
                // Log the error but don't crash the page
                \Log::error('Sales Target Dashboard Error: ' . $e->getMessage());
                session()->flash('error', 'Error fetching data from Google Sheets: ' . $e->getMessage());
            }
        }

        // Cache settings for display
        $cacheTtl = $this->salesTargetService->getCacheTtl();
        $cacheTtlMinutes = round($cacheTtl / 60);

        return view('admin.sales-dashboard.index', compact(
            'stats',
            'teamPerformance',
            'chartData',
            'startDate',
            'endDate',
            'isConfigured',
            'cacheTtlMinutes'
        ));
    }

    /**
     * Get chart data via AJAX
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getChartData(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))
            : Carbon::now()->startOfMonth();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))
            : Carbon::now();

        try {
            // Get orders by rep first (sorted high to low) and extract sorted user IDs
            $ordersByRep = $this->salesTargetService->getOrdersByRep($startDate, $endDate);
            $sortedUserIds = $ordersByRep['sorted_user_ids'] ?? [];

            return response()->json([
                'success' => true,
                'stats' => $this->salesTargetService->getDashboardStats($startDate, $endDate),
                'teamPerformance' => $this->salesTargetService->getTeamPerformance($startDate, $endDate),
                'charts' => [
                    'newVsExisting' => $this->salesTargetService->getNewVsExistingByDay($startDate, $endDate),
                    'ordersByRep' => $ordersByRep,
                    'newBusinessByRep' => $this->salesTargetService->getNewBusinessByRep($startDate, $endDate, $sortedUserIds),
                    'existingBusinessByRep' => $this->salesTargetService->getExistingBusinessByRep($startDate, $endDate, $sortedUserIds),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh the data cache
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        try {
            $this->salesTargetService->refreshCache();

            return response()->json([
                'success' => true,
                'message' => 'Data refreshed successfully. Cache cleared.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error refreshing data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
