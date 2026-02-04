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
                $chartData = [
                    'newVsExisting' => $this->salesTargetService->getNewVsExistingByDay($startDate, $endDate),
                    'ordersByRep' => $this->salesTargetService->getOrdersByRep($startDate, $endDate),
                    'newBusinessByRep' => $this->salesTargetService->getNewBusinessByRep($startDate, $endDate),
                    'existingBusinessByRep' => $this->salesTargetService->getExistingBusinessByRep($startDate, $endDate),
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
            return response()->json([
                'success' => true,
                'stats' => $this->salesTargetService->getDashboardStats($startDate, $endDate),
                'teamPerformance' => $this->salesTargetService->getTeamPerformance($startDate, $endDate),
                'charts' => [
                    'newVsExisting' => $this->salesTargetService->getNewVsExistingByDay($startDate, $endDate),
                    'ordersByRep' => $this->salesTargetService->getOrdersByRep($startDate, $endDate),
                    'newBusinessByRep' => $this->salesTargetService->getNewBusinessByRep($startDate, $endDate),
                    'existingBusinessByRep' => $this->salesTargetService->getExistingBusinessByRep($startDate, $endDate),
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
