<?php

namespace App\Http\Controllers\Schedular;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use App\Models\CurrentJobsTracking;

class CurrentJobsController extends Controller
{

    // public function index(Request $request)
    // {
    //     if ($request->ajax()) {

    //         try {
    //             $client = new Client();
    //             $apiUrl = env('TRANSPORT_API_URL'); 
    //             $apiKey = env('TRANSPORT_API_KEY');

    //             // DataTables parameters
    //             $draw = $request->input('draw', 1);
    //             $start = $request->input('start', 0);
    //             $length = $request->input('length', 25);
    //             $searchValue = $request->input('search.value', '');
    //             $filterType = $request->input('filterType', 'all'); // NEW: Get filter type

    //             // Get sorting parameters
    //             $orderColumn = $request->input('order.0.column', 0);
    //             $orderDirection = $request->input('order.0.dir', 'desc');
                
    //             // Map column index to field name - FIXED FOR 9 COLUMNS (removed some columns)
    //             $columns = ['orderNo', 'collectionTime', 'departureTime', 'deliveryTime', 'midpointCheck', 'internalNotes', 'collectionCheckIn', 'driverConfirmedETA', 'midpointCheckComplete', 'delivered'];
    //             $sortField = $columns[$orderColumn] ?? 'orderNo';

    //             // Build API query
    //             $apiQuery = [];

    //             // Date Range Filtering
    //             if ($request->filled('fromDate')) {
    //                 $apiQuery['filter[createdAt][gte]'] = Carbon::parse($request->input('fromDate'))->startOfDay()->format('Y-m-d\TH:i:s');
    //             }
    //             if ($request->filled('toDate')) {
    //                 $apiQuery['filter[createdAt][lte]'] = Carbon::parse($request->input('toDate'))->endOfDay()->format('Y-m-d\TH:i:s');
    //             }

    //             // if (!$request->filled('fromDate') && !$request->filled('toDate')) {
    //             //     $apiQuery['filter[createdAt][gte]'] = Carbon::now('UTC')->subDays(2)->format('Y-m-d\TH:i:s\Z');
    //             // }

    //             // Set sorting - handle different field mappings for API
    //             if ($sortField === 'orderNo') {
    //                 $apiQuery['sort'] = ($orderDirection === 'desc') ? '-orderNo' : 'orderNo';
    //             }
    //             $apiQuery['filter[status]'] = 'planned';
    //             $today = date('Y-m-d');

    //             $response = $client->get($apiUrl . 'orders', [
    //                 'headers' => [
    //                     'Authorization' => 'Basic ' . $apiKey,
    //                     'Content-Type'  => 'application/json',
    //                     'Accept'        => 'application/json',
    //                 ],
    //                 'query' => $apiQuery,
    //             ]);

    //             $res = json_decode($response->getBody()->getContents(), true);
    //             $records = collect($res['data'] ?? []);

    //             $orders = $records->filter(function($order) use ($today) {
    //                 $destinations = $order['attributes']['destinations'] ?? [];
    //                 $pickup = collect($destinations)->firstWhere('taskType', 'pickup');
    //                 $delivery = collect($destinations)->firstWhere('taskType', 'delivery');
    //                 $status = $order['attributes']['status'] ?? '';
                    
    //                 // Check if pickup date is today
    //                 $pickupToday = $pickup && isset($pickup['date']) && $pickup['date'] === $today;
                    
    //                 // Check if delivery date is today
    //                 $deliveryToday = $delivery && isset($delivery['date']) && $delivery['date'] === $today;
                    
    //                 // Only include orders where:
    //                 // 1. pickup date is today OR delivery date is today
    //                 // 2. status is NOT "pending-acceptation" or "quote"
    //                 return ($pickupToday || $deliveryToday) &&
    //                     !in_array($status, ['pending-acceptation', 'quote']);
    //             });
    //             $meta = $res['meta'] ?? [];
                
    //             // Transform data with tracking integration
    //             $transformedData = $orders->map(function($row) {
    //                 // Get pickup and delivery destinations
    //                 $destinations = $row['attributes']['destinations'] ?? [];
    //                 $pickup = collect($destinations)->firstWhere('taskType', 'pickup');
    //                 $delivery = collect($destinations)->firstWhere('taskType', 'delivery');

    //                 // Calculate midpoint check
    //                 $midpointCheck = null;
    //                 if ($pickup && $delivery && isset($pickup['toTime']) && isset($delivery['deliveryTime'])) {
    //                     try {
    //                         $collectionTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $pickup['date'] . ' ' . $pickup['toTime']);
    //                         $deliveryTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $delivery['date'] . ' ' . $delivery['deliveryTime']);
                            
    //                         if ($deliveryTime->diffInHours($collectionTime) >=2) {
    //                             $midpointCheck = $collectionTime->copy()->addMinutes($deliveryTime->diffInMinutes($collectionTime) / 2)->format('H:i');
    //                         }
    //                     } catch (\Exception $e) {
    //                         $midpointCheck = null;
    //                     }
    //                 }

    //                 // Get carrier/driver name from carrierNo
    //                 $driverName = null;
    //                 if (isset($row['attributes']['carrierNo'])) {
    //                     $driverName = $row['attributes']['carrierNo'];
    //                 }

    //                 // Get customer info
    //                 $userDisplay = null;
    //                 if (isset($row['attributes']['usernameCreated'])) {
    //                     $userDisplay = $row['attributes']['usernameCreated'] ?? '-';
    //                 }

    //                 // Check local tracking for this order
    //                 $orderId = $row['id'];
    //                 $tracking = CurrentJobsTracking::where('order_id', $orderId)->first();

    //                 return [
    //                     'id' => $row['id'] ?? null,
    //                     'orderNo' => $row['attributes']['orderNo'] ?? null,
    //                     'carrierNo' => $driverName,
    //                     // 'collectionDate' => $pickup['date'] ?? null,
    //                     'collectionTime' => $pickup['toTime'] ?? null,
    //                     'departureTime' => $pickup['departureTime'] ?? null,
    //                     'deliveryTime' => $delivery['deliveryTime'] ?? null,
    //                     'midpointCheck' => $midpointCheck,
    //                     'internalNotes' => $row['attributes']['internalNotes'] ?? null,
                        
    //                     // Additional fields for search and modal
    //                     'customerNo' => $row['attributes']['customerNo'] ?? null,
    //                     'vehicleTypeName' => $row['attributes']['vehicleTypeName'] ?? null,
    //                     'status' => $row['attributes']['status'] ?? null,

    //                     // Add tracking status for buttons
    //                     'collectionCheckIn' => $tracking ? $tracking->collection_checked_in : false,
    //                     'driverConfirmedETA' => $tracking ? $tracking->driver_eta_confirmed : false,
    //                     'midpointCheckComplete' => $tracking ? $tracking->midpoint_check_completed : false,
    //                     'delivered' => $tracking ? $tracking->delivered : null,
                        
    //                     // Add computed fields for filtering
    //                     'isCollectionOverdue' => $this->isCollectionOverdue($pickup, $row['attributes']['orderNo']),
    //                     'isDeliveryOverdue' => $this->isDeliveryOverdue($delivery),
    //                     'isMidpointOverdue' => $this->isMidpointOverdue($pickup, $delivery),
    //                     'isDelivered' => $tracking && $tracking->status === 'completed',
    //                 ];
    //             })
    //             // Filter out completed jobs
    //             // Only filter out completed jobs if NOT showing delivered
    //             ->filter(function($item) use ($filterType) {
    //                 // If user wants to see delivered jobs, don't filter out completed ones
    //                 if ($filterType === 'delivered') {
    //                     return true; // Show all jobs including completed
    //                 }
                    
    //                 // For all other filters, hide completed jobs
    //                 $tracking = CurrentJobsTracking::where('order_id', $item['id'])->first();
    //                 return !$tracking || $tracking->status !== 'completed';
    //             });

    //             // NEW: Apply card-based filtering
    //             if ($filterType !== 'all') {
    //                 $transformedData = $transformedData->filter(function($item) use ($filterType) {
    //                     switch ($filterType) {
    //                         case 'collections-overdue':
    //                             return $item['isCollectionOverdue'];
    //                         case 'deliveries-overdue':
    //                             return $item['isDeliveryOverdue'];
    //                         case 'midpoint-overdue':
    //                             return $item['isMidpointOverdue'];
    //                         case 'delivered':
    //                             return $item['isDelivered'];
    //                         default:
    //                             return true;
    //                     }
    //                 });
    //             }

    //             // Apply search filter if provided
    //             if (!empty($searchValue)) {
    //                 $transformedData = $transformedData->filter(function($item) use ($searchValue) {
    //                     $searchLower = strtolower($searchValue);
                        
    //                     return str_contains(strtolower($item['customerUserId'] ?? ''), $searchLower) ||
    //                            str_contains(strtolower($item['orderNo'] ?? ''), $searchLower) ||
    //                            str_contains(strtolower($item['vehicleTypeName'] ?? ''), $searchLower) ||
    //                            str_contains(strtolower($item['status'] ?? ''), $searchLower) ||
    //                            str_contains(strtolower($item['carrierNo'] ?? ''), $searchLower) ||
    //                            str_contains(strtolower($item['internalNotes'] ?? ''), $searchLower);
    //                 });
    //             }

    //             // Apply client-side sorting for fields not sortable by API
    //             if (in_array($sortField, ['customerUserId', 'carrierNo', 'newExisting', 'collectionDate', 'collectionTime', 'departureTime', 'orderPrice', 'deliveryTime', 'midpointCheck', 'internalNotes', 'collectionCheckIn', 'driverConfirmedETA', 'midpointCheckComplete'])) {
    //                 $transformedData = $transformedData->sortBy(function($item) use ($sortField) {
    //                     // For price fields, convert to numeric for proper sorting
    //                     if (in_array($sortField, ['orderPrice'])) {
    //                         return (float) ($item[$sortField] ?? 0);
    //                     }
    //                     // For date/time fields
    //                     if (in_array($sortField, ['collectionDate', 'collectionTime', 'departureTime', 'deliveryTime'])) {
    //                         return $item[$sortField] ?? '';
    //                     }
    //                     // For text fields, convert to lowercase
    //                     return strtolower($item[$sortField] ?? '');
    //                 });
                    
    //                 if ($orderDirection === 'desc') {
    //                     $transformedData = $transformedData->reverse();
    //                 }
                    
    //                 $transformedData = $transformedData->values(); // Reset keys
    //             }

    //             // Handle pagination - limit to 100 records total
    //             $totalRecords = min($transformedData->count(), 100);
    //             $recordsToTake = min($length, $totalRecords - $start);
    //             $recordsToTake = max(0, $recordsToTake); // Ensure non-negative
                
    //             $paginatedData = $transformedData->slice($start, $recordsToTake)->values();

    //             return response()->json([
    //                 'draw' => intval($draw),
    //                 'recordsTotal' => $totalRecords,
    //                 'recordsFiltered' => $totalRecords,
    //                 'data' => $paginatedData->toArray()
    //             ]);

    //         } catch (\Exception $e) {
    //             Log::error("DataTables error: " . $e->getMessage());
    //             return response()->json([
    //                 'draw'            => $request->input('draw', 1),
    //                 'recordsTotal'    => 0,
    //                 'recordsFiltered' => 0,
    //                 'data'            => [],
    //                 'error'           => 'Something went wrong while fetching data: ' . $e->getMessage()
    //             ], 500);
    //         }
    //     }

    //     // Calculate dynamic dashboard counters
    //     $countData = $this->calculateDashboardCounters();
    //     return view('admin.schedular.current-jobs', compact('countData'));
    // }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            try {
                // Store the current filter in session when AJAX request comes
                if ($request->filled('filterType')) {
                    session(['current_jobs_filter' => $request->input('filterType')]);
                }

                $client = new Client();
                $apiUrl = env('TRANSPORT_API_URL'); 
                $apiKey = env('TRANSPORT_API_KEY');

                // DataTables parameters
                $draw = $request->input('draw', 1);
                $start = $request->input('start', 0);
                $length = $request->input('length', 25);
                $searchValue = $request->input('search.value', '');
                $filterType = $request->input('filterType', 'all'); // NEW: Get filter type

                // Get sorting parameters
                $orderColumn = $request->input('order.0.column', 0);
                $orderDirection = $request->input('order.0.dir', 'desc');
                
                // Map column index to field name - FIXED FOR 9 COLUMNS (removed some columns)
                $columns = ['orderNo', 'collectionTime', 'departureTime', 'deliveryTime', 'midpointCheck', 'internalNotes', 'collectionCheckIn', 'driverConfirmedETA', 'midpointCheckComplete', 'delivered'];
                $sortField = $columns[$orderColumn] ?? 'orderNo';

                // Build API query
                $apiQuery = [];

                // Date Range Filtering
                if ($request->filled('fromDate')) {
                    $apiQuery['filter[createdAt][gte]'] = Carbon::parse($request->input('fromDate'))->startOfDay()->format('Y-m-d\TH:i:s');
                }
                if ($request->filled('toDate')) {
                    $apiQuery['filter[createdAt][lte]'] = Carbon::parse($request->input('toDate'))->endOfDay()->format('Y-m-d\TH:i:s');
                }

                // if (!$request->filled('fromDate') && !$request->filled('toDate')) {
                //     $apiQuery['filter[createdAt][gte]'] = Carbon::now('UTC')->subDays(2)->format('Y-m-d\TH:i:s\Z');
                // }

                // Set sorting - handle different field mappings for API
                if ($sortField === 'orderNo') {
                    $apiQuery['sort'] = ($orderDirection === 'desc') ? '-orderNo' : 'orderNo';
                }
                $apiQuery['filter[status]'] = 'planned';
                // $apiQuery['filter[status]'] = 'signed-off';
                $today = date('Y-m-d');

                $response = $client->get($apiUrl . 'orders', [
                    'headers' => [
                        'Authorization' => 'Basic ' . $apiKey,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json',
                    ],
                    'query' => $apiQuery,
                ]);

                $res = json_decode($response->getBody()->getContents(), true);
                $records = collect($res['data'] ?? []);

                $orders = $records->filter(function($order) use ($today) {
                    $destinations = $order['attributes']['destinations'] ?? [];
                    $pickup = collect($destinations)->firstWhere('taskType', 'pickup');
                    $delivery = collect($destinations)->firstWhere('taskType', 'delivery');
                    $status = $order['attributes']['status'] ?? '';
                    
                    // Check if pickup date is today
                    $pickupToday = $pickup && isset($pickup['date']) && $pickup['date'] === $today;
                    
                    // Check if delivery date is today
                    $deliveryToday = $delivery && isset($delivery['date']) && $delivery['date'] === $today;
                    
                    // Only include orders where:
                    // 1. pickup date is today OR delivery date is today
                    // 2. status is NOT "pending-acceptation" or "quote"
                    return ($pickupToday || $deliveryToday) &&
                        !in_array($status, ['pending-acceptation', 'quote']);
                });
                $meta = $res['meta'] ?? [];
                
                // Transform data with tracking integration
                $transformedData = $orders->map(function($row) {
                    // Get pickup and delivery destinations
                    $destinations = $row['attributes']['destinations'] ?? [];
                    $pickup = collect($destinations)->firstWhere('taskType', 'pickup');
                    $delivery = collect($destinations)->firstWhere('taskType', 'delivery');

                    // Calculate midpoint check
                    $midpointCheck = null;
                    if ($pickup && $delivery && isset($pickup['toTime']) && isset($delivery['deliveryTime'])) {
                        try {
                            $collectionTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $pickup['date'] . ' ' . $pickup['toTime']);
                            $deliveryTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $delivery['date'] . ' ' . $delivery['deliveryTime']);
                            
                            if ($deliveryTime->diffInHours($collectionTime) >=2) {
                                $midpointCheck = $collectionTime->copy()->addMinutes($deliveryTime->diffInMinutes($collectionTime) / 2)->format('H:i');
                            }
                        } catch (\Exception $e) {
                            $midpointCheck = null;
                        }
                    }

                    // Get carrier/driver name from carrierNo
                    $driverName = null;
                    if (isset($row['attributes']['carrierNo'])) {
                        $driverName = $row['attributes']['carrierNo'];
                    }

                    // Check local tracking for this order
                    $orderId = $row['id'];
                    $tracking = CurrentJobsTracking::where('order_id', $orderId)->first();

                    return [
                        'id' => $row['id'] ?? null,
                        'orderNo' => $row['attributes']['orderNo'] ?? null,
                        'carrierNo' => $driverName,
                        // 'collectionDate' => $pickup['date'] ?? null,
                        'collectionTime' => $pickup['toTime'] ?? null,
                        'departureTime' => $pickup['departureTime'] ?? null,
                        'deliveryTime' => $delivery['deliveryTime'] ?? null,
                        'midpointCheck' => $midpointCheck,
                        'internalNotes' => $row['attributes']['internalNotes'] ?? null,
                        
                        // Additional fields for search and modal
                        'customerNo' => $row['attributes']['customerNo'] ?? null,
                        'vehicleTypeName' => $row['attributes']['vehicleTypeName'] ?? null,
                        'status' => $row['attributes']['status'] ?? null,

                        // Add tracking status for buttons
                        'collectionCheckIn' => $tracking ? $tracking->collection_checked_in : false,
                        'driverConfirmedETA' => $tracking ? $tracking->driver_eta_confirmed : false,
                        'midpointCheckComplete' => $tracking ? $tracking->midpoint_check_completed : false,
                        'delivered' => $tracking ? $tracking->delivered : null,
                        
                        // Add computed fields for filtering
                        'isCollectionOverdue' => $this->isCollectionOverdue($pickup, $row['attributes']['orderNo']),
                        'isDeliveryOverdue' => $this->isDeliveryOverdue($delivery, $row['attributes']['orderNo']),
                        'isMidpointOverdue' => $this->isMidpointOverdue($pickup, $delivery, $row['attributes']['orderNo']),
                        'isDelivered' => $tracking && $tracking->status === 'completed',
                    ];
                })
                // Filter out completed jobs
                // Only filter out completed jobs if NOT showing delivered
                ->filter(function($item) use ($filterType) {
                    // If user wants to see delivered jobs, don't filter out completed ones
                    if ($filterType === 'delivered') {
                        return true; // Show all jobs including completed
                    }
                    
                    // For all other filters, hide completed jobs
                    $tracking = CurrentJobsTracking::where('order_id', $item['id'])->first();
                    return !$tracking || $tracking->status !== 'completed';
                });

                // NEW: Apply card-based filtering
                if ($filterType !== 'all') {
                    $transformedData = $transformedData->filter(function($item) use ($filterType) {
                        switch ($filterType) {
                            case 'collections-overdue':
                                return $item['isCollectionOverdue'];
                            case 'deliveries-overdue':
                                return $item['isDeliveryOverdue'];
                            case 'midpoint-overdue':
                                return $item['isMidpointOverdue'];
                            case 'delivered':
                                return $item['isDelivered'];
                            default:
                                return true;
                        }
                    });
                }

                // Apply search filter if provided
                if (!empty($searchValue)) {
                    $transformedData = $transformedData->filter(function($item) use ($searchValue) {
                        $searchLower = strtolower($searchValue);
                        
                        return str_contains(strtolower($item['customerUserId'] ?? ''), $searchLower) ||
                            str_contains(strtolower($item['orderNo'] ?? ''), $searchLower) ||
                            str_contains(strtolower($item['vehicleTypeName'] ?? ''), $searchLower) ||
                            str_contains(strtolower($item['status'] ?? ''), $searchLower) ||
                            str_contains(strtolower($item['carrierNo'] ?? ''), $searchLower) ||
                            str_contains(strtolower($item['internalNotes'] ?? ''), $searchLower);
                    });
                }

                // Apply client-side sorting for fields not sortable by API
                if (in_array($sortField, ['customerUserId', 'carrierNo', 'newExisting', 'collectionDate', 'collectionTime', 'departureTime', 'orderPrice', 'deliveryTime', 'midpointCheck', 'internalNotes', 'collectionCheckIn', 'driverConfirmedETA', 'midpointCheckComplete'])) {
                    $transformedData = $transformedData->sortBy(function($item) use ($sortField) {
                        // For price fields, convert to numeric for proper sorting
                        if (in_array($sortField, ['orderPrice'])) {
                            return (float) ($item[$sortField] ?? 0);
                        }
                        // For date/time fields
                        if (in_array($sortField, ['collectionDate', 'collectionTime', 'departureTime', 'deliveryTime'])) {
                            return $item[$sortField] ?? '';
                        }
                        // For text fields, convert to lowercase
                        return strtolower($item[$sortField] ?? '');
                    });
                    
                    if ($orderDirection === 'desc') {
                        $transformedData = $transformedData->reverse();
                    }
                    
                    $transformedData = $transformedData->values(); // Reset keys
                }

                // Handle pagination - limit to 100 records total
                $totalRecords = min($transformedData->count(), 100);
                $recordsToTake = min($length, $totalRecords - $start);
                $recordsToTake = max(0, $recordsToTake); // Ensure non-negative
                
                $paginatedData = $transformedData->slice($start, $recordsToTake)->values();

                return response()->json([
                    'draw' => intval($draw),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $paginatedData->toArray()
                ]);

            } catch (\Exception $e) {
                Log::error("DataTables error: " . $e->getMessage());
                return response()->json([
                    'draw'            => $request->input('draw', 1),
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => [],
                    'error'           => 'Something went wrong while fetching data: ' . $e->getMessage()
                ], 500);
            }
        }

        // Get the stored filter from session (for initial page load)
        $storedFilter = session('current_jobs_filter', 'all');

        // Calculate dynamic dashboard counters
        $countData = $this->calculateDashboardCounters();
        return view('admin.schedular.current-jobs', compact('countData', 'storedFilter'));
    }

    // NEW: Helper methods for filtering logic
    private function isCollectionOverdue($pickup, $orderId)
    {
        if (!$pickup || !isset($pickup['toTime']) || $pickup['date'] !== date('Y-m-d')) {
            return false;
        }

        try {
            $tracking = CurrentJobsTracking::where('order_id', $orderId)->first();
            $pickupDateTime = Carbon::createFromFormat('Y-m-d H:i', $pickup['date'] . ' ' . $pickup['toTime']);
            if (!$tracking || !$tracking->collection_checked_in) {
                return Carbon::now()->greaterThan($pickupDateTime);
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    private function isDeliveryOverdue($delivery, $orderId)
    {
        if (!$delivery || !isset($delivery['deliveryTime']) || !isset($delivery['date'])) {
            return false;
        }

        try {
            $tracking = CurrentJobsTracking::where('order_id', $orderId)->first();
            $deliveryDateTime = Carbon::createFromFormat('Y-m-d H:i', $delivery['date'] . ' ' . $delivery['deliveryTime']);
            if (!$tracking || !$tracking->driver_eta_confirmed) {
                return Carbon::now()->greaterThan($deliveryDateTime);
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    private function isMidpointOverdue($pickup, $delivery, $orderId)
    {
        if (!$pickup || !$delivery || !isset($pickup['toTime']) || !isset($delivery['deliveryTime'])) {
            return false;
        }

        try {
            $collectionTime = Carbon::createFromFormat('Y-m-d H:i', $pickup['date'] . ' ' . $pickup['toTime']);
            $deliveryTime = Carbon::createFromFormat('Y-m-d H:i', $delivery['date'] . ' ' . $delivery['deliveryTime']);
            $tracking = CurrentJobsTracking::where('order_id', $orderId)->first();
            
            if ($deliveryTime->diffInHours($collectionTime) >= 2) {
                // $midpointTime = $collectionTime->copy()->addMinutes($deliveryTime->diffInMinutes($collectionTime) / 2);
                $midpointTime = $collectionTime->copy()->addMinutes($deliveryTime->diffInMinutes($collectionTime) / 2)->format('H:i');
                if (!$tracking || !$tracking->midpoint_check_completed) {
                    return $midpointTime;
                }
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function calculateDashboardCounters()
    {
        try {
            $client = new Client();
            $apiUrl = env('TRANSPORT_API_URL'); 
            $apiKey = env('TRANSPORT_API_KEY');
        
            // Get sorting parameters
            $orderColumn = 0;
            $orderDirection = 'desc';

            // Map column index to field name - FIXED FOR 9 COLUMNS (removed some columns)
            $columns = ['orderNo', 'collectionTime', 'departureTime', 'deliveryTime', 'midpointCheck', 'internalNotes', 'collectionCheckIn', 'driverConfirmedETA', 'midpointCheckComplete', 'delivered'];
            $sortField = $columns[$orderColumn] ?? 'orderNo';

            // Build API query
            $apiQuery = [];

            // Set sorting - handle different field mappings for API
            if ($sortField === 'orderNo') {
                $apiQuery['sort'] = ($orderDirection === 'desc') ? '-orderNo' : 'orderNo';
            }
            
            $apiQuery['filter[status]'] = 'planned';
            $today = date('Y-m-d');
            // $apiQuery['filter[date]'] = $today;

            $response = $client->get($apiUrl . 'orders', [
                'headers' => [
                    'Authorization' => 'Basic ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'query' => $apiQuery,
            ]);

            $res = json_decode($response->getBody()->getContents(), true);
            $records = collect($res['data'] ?? []);


            $orders = $records->filter(function($order) use ($today) {
                $destinations = $order['attributes']['destinations'] ?? [];
                $pickup = collect($destinations)->firstWhere('taskType', 'pickup');
                $delivery = collect($destinations)->firstWhere('taskType', 'delivery');
                $status = $order['attributes']['status'] ?? '';
                
                // Check if pickup date is today
                $pickupToday = $pickup && isset($pickup['date']) && $pickup['date'] === $today;
                
                // Check if delivery date is today
                $deliveryToday = $delivery && isset($delivery['date']) && $delivery['date'] === $today;
                
                // Only include orders where:
                // 1. pickup date is today OR delivery date is today
                // 2. status is NOT "pending-acceptation" or "quote"
                return ($pickupToday || $deliveryToday) &&
                    !in_array($status, ['pending-acceptation', 'quote']);
            });

            // Get order IDs from filtered orders
            $orderIds = $orders->pluck('id')->toArray();

            // Get completed jobs count ONLY from today's filtered orders
            $completedJobsToday = CurrentJobsTracking::whereIn('order_id', $orderIds)
                ->where('status', 'completed')
                ->whereDate('completed_at', Carbon::today())
                ->count();
            
            $currentTime = Carbon::now();
            $currentDate = $currentTime->format('Y-m-d');
            
            // Initialize counters
            $totalJobs = $orders->count(); // Remove completed jobs from total
            $collectionsOverdue = 0;
            $deliveriesOverdue = 0;
            $midPointCheckInOverdue = 0;

            foreach ($orders as $order) {
                $destinations = $order['attributes']['destinations'] ?? [];
                $pickup = collect($destinations)->firstWhere('taskType', 'pickup');
                $delivery = collect($destinations)->firstWhere('taskType', 'delivery');
                $tracking = CurrentJobsTracking::where('order_id', $order['id'])->first();

                // Check collections overdue
                if ($pickup && isset($pickup['toTime']) && $pickup['date'] === $currentDate) {
                    try {
                        $pickupDateTime = Carbon::createFromFormat('Y-m-d H:i', $pickup['date'] . ' ' . $pickup['toTime']);
                        if ($currentTime->greaterThan($pickupDateTime)) {
                            if (!$tracking || !$tracking->collection_checked_in) {
                                $collectionsOverdue++;
                            }
                        }
                    } catch (\Exception $e) {
                        // Skip if date/time parsing fails
                    }
                }

                // Check deliveries overdue
                if ($delivery && isset($delivery['deliveryTime']) && isset($delivery['date'])) {
                    try {
                        $deliveryDateTime = Carbon::createFromFormat('Y-m-d H:i', $delivery['date'] . ' ' . $delivery['deliveryTime']);
                        if ($currentTime->greaterThan($deliveryDateTime)) {
                            if (!$tracking || !$tracking->driver_eta_confirmed) {
                                $deliveriesOverdue++;
                            }
                        }
                    } catch (\Exception $e) {
                        // Skip if date/time parsing fails
                    }
                }

                // Check midpoint check overdue
                if ($pickup && $delivery && isset($pickup['toTime']) && isset($delivery['deliveryTime'])) {
                    try {
                        $collectionTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $pickup['date'] . ' ' . $pickup['toTime']);
                        $deliveryTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $delivery['date'] . ' ' . $delivery['deliveryTime']);
                        
                        if ($deliveryTime->diffInHours($collectionTime) >= 2) {
                            if (!$tracking || !$tracking->midpoint_check_completed) {
                                $midPointCheckInOverdue++;
                            }
                        }
                    } catch (\Exception $e) {
                    }
                }
            }

            return [
                'totalJobs' => $totalJobs, // This will now match datatable exactly
                'collectionsOverdue' => $collectionsOverdue,
                'deliveriesOverdue' => $deliveriesOverdue,
                'midPointCheckInOverdue' => $midPointCheckInOverdue,
                'delivered' => $completedJobsToday,
            ];

        } catch (\Exception $e) {
            Log::error("Dashboard counters calculation error: " . $e->getMessage());
            
            // Return default values on error
            return [
                'totalJobs' => 0,
                'collectionsOverdue' => 0,
                'deliveriesOverdue' => 0,
                'midPointCheckInOverdue' => 0,
                'delivered' => 0,
            ];
        }
    }

    public function updateOrderStatus(Request $request)
    {
        try {
            $orderId = $request->input('orderId');
            $actionType = $request->input('actionType');
            $deliveredStatus = $request->input('deliveredStatus'); // For delivery status

            // Get the full order data from third-party API to store
            $client = new Client();
            $apiUrl = env('TRANSPORT_API_URL');
            $apiKey = env('TRANSPORT_API_KEY');

            $response = $client->get($apiUrl . 'orders/' . $orderId, [
                'headers' => [
                    'Authorization' => 'Basic ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
            ]);

            $orderData = json_decode($response->getBody()->getContents(), true);
            
            // Get or create tracking record
            $tracking = CurrentJobsTracking::getOrCreateForOrder($orderId, $orderData);

            // Update based on action type
            switch ($actionType) {
                case 'collection-checkin':
                    $tracking->markCollectionCheckedIn();
                    $message = 'Collection check-in marked as complete';
                    break;
                    
                case 'driver-eta':
                    $tracking->markDriverETAConfirmed();
                    $message = 'Driver ETA confirmed';
                    break;
                    
                case 'midpoint-check':
                    $tracking->markMidpointCheckCompleted();
                    $message = 'Mid-point check marked as complete';
                    break;
                    
                case 'delivered':
                    // Handle delivery status and mark as completed
                    $tracking->markDelivered($deliveredStatus);
                    $tracking->markCompleted(); // Mark job as completed when delivered is set
                    
                    if ($deliveredStatus == 1) {
                        $message = 'Delivery marked as Completed!';
                    } 
                    break;
                    
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid action type']);
            }

            // Check if job is now completed (only for delivered action)
            $jobCompleted = ($actionType === 'delivered');

            return response()->json([
                'success' => true, 
                'message' => $message,
                'completed' => $jobCompleted
            ]);

        } catch (\Exception $e) {
            Log::error("Update order status error: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCustomer(Request $request)
    {
        try {
            $customerNo = $request->input('customerNo');
            $carrierNo = $request->input('carrierNo');
            $carrierName = "-";

            $client = new Client();
            $apiUrl = env('TRANSPORT_API_URL');
            $apiKey = env('TRANSPORT_API_KEY');

            $response = $client->get($apiUrl . "customers/{$customerNo}", [
                'headers' => [
                    'Authorization' => 'Basic ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
            ]);

            $customerData = json_decode($response->getBody()->getContents(), true);
            $companyName = $customerData['data']['attributes']['companyName'] ? $customerData['data']['attributes']['companyName'] . " ($customerNo)" :  " - {$customerNo}";

            // For getting total Order Counts
            $response3 = $client->get($apiUrl . 'orders', [
                'headers' => [
                    'Authorization' => 'Basic ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'query' => [
                    'filter[customerNo]' => $customerNo,
                ],
            ]);

            $res = json_decode($response3->getBody()->getContents(), true);
            $orders = $res['meta'] ?? [];
            $totalOrders = $orders['total'];
            
            // For getting carrier/driver name
            if(!empty($carrierNo)){
                $response2 = $client->get($apiUrl . 'carriers', [
                    'headers' => [
                        'Authorization' => 'Basic ' . $apiKey,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json',
                    ],
                    'query' => [
                        'filter[carrierNo]' => $carrierNo,
                    ],
                ]);

                $carrierData = json_decode($response2->getBody()->getContents(), true);
                $carrierName = $carrierData['data'][0]['attributes']['name'] ?? "-";
            }

            return response()->json([
                'success' => true,
                'companyName' => $companyName,
                'carrierName' => $carrierName,
                'newExist'    => $totalOrders > 1 ? 'Existing' : 'New'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'companyName' => "Customer #{$customerNo}"
            ]);
        }
    }

    public function show($id)
    {
        $client = new Client();
        $apiUrl = env('TRANSPORT_API_URL');
        $apiKey = env('TRANSPORT_API_KEY');

        try {
            // Fetch order from API
            $response = $client->get($apiUrl . 'orders/' . $id, [
                'headers' => [
                    'Authorization' => 'Basic ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $order = $data['data'] ?? null;

            if (!$order) {
                return redirect()->route('admin.orders.index')
                    ->with('error', 'Order not found.');
            }

            // Get customer number from order
            $customerNo = $order['attributes']['customerNo'] ?? null;
            $customer = [];
            $totalOrders = 0;

            if ($customerNo) {
                try {
                    // Fetch customer data
                    $customerResponse = $client->get($apiUrl . "customers/{$customerNo}", [
                        'headers' => [
                            'Authorization' => 'Basic ' . $apiKey,
                            'Content-Type'  => 'application/json',
                            'Accept'        => 'application/json',
                        ],
                    ]);

                    $customerData = json_decode($customerResponse->getBody()->getContents(), true);
                    $customer = $customerData['data']['attributes'] ?? [];
                    // Add company name to order for display
                    $companyName = $customer['companyName'] ?? null;
                    $order['companyName'] = $companyName ? "{$companyName} ({$customerNo})" : "- {$customerNo}";

                    $customer['createdAt'] = $customerData['data']['createdAt'];
                    $customer['updatedAt'] = $customerData['data']['updatedAt'];

                } catch (\Exception $e) {
                    \Log::warning('Failed to fetch customer data for customerNo ' . $customerNo . ': ' . $e->getMessage());
                    $order['companyName'] = "Customer #{$customerNo}";
                }

                try {
                    // Fetch total orders count for this customer
                    $ordersResponse = $client->get($apiUrl . 'orders', [
                        'headers' => [
                            'Authorization' => 'Basic ' . $apiKey,
                            'Content-Type'  => 'application/json',
                            'Accept'        => 'application/json',
                        ],
                        'query' => [
                            'filter[customerNo]' => $customerNo,
                            'sort' => '-createdAt'
                        ]
                    ]);
                    $ordersData = json_decode($ordersResponse->getBody()->getContents(), true);
                    
                    // Get FIRST (oldest) order
                    if(!empty($ordersData['data']) && count($ordersData['data']) > 0){
                        $firstOrder = end($ordersData['data']);
                        $firstOrderDate = isset($firstOrder['createdAt']) 
                            ? \Carbon\Carbon::parse($firstOrder['createdAt'])->format('d-m-Y H:i')
                            : null;
                    }

                    $meta = $ordersData['meta'] ?? [];
                    // Check if meta has total count
                    if (isset($meta['total']) && is_numeric($meta['total'])) {
                        $totalOrders = (int) $meta['total'];
                    }

                } catch (\Exception $e) {
                    \Log::warning('Failed to fetch orders count for customerNo ' . $customerNo . ': ' . $e->getMessage());
                    $totalOrders = 0;
                }
            }

            // Flatten order attributes for easier access in view
            $order = array_merge($order, $order['attributes'] ?? []);

            $destinations = [];   
            foreach ($order['destinations'] as $value) {
                if($value['taskType'] == 'pickup'){
                    $destinations['collections'] = $value;   
                }
                elseif ($value['taskType'] == 'delivery') {
                    $destinations['delivery'] = $value;   
                }
                else{
                    $destinations = [];
                }
            }

            return view('admin.orders.view', compact('order', 'customer', 'totalOrders', 'destinations', 'firstOrderDate'));

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Handle 404 and other 4xx errors
            if ($e->getResponse()->getStatusCode() === 404) {
                return redirect()->route('admin.orders.index')
                    ->with('error', 'Order not found in the system.');
            }
        }
    }

    public function getOrderCount($customer_no)
    {
        try {
            $customerNo = $customer_no;
            
            if (!$customerNo) {
                return response()->json([
                    'error' => 'Customer Number required'
                ], 400);
            }

            $client = new Client();
            $apiUrl = env('TRANSPORT_API_URL'); 
            $apiKey = env('TRANSPORT_API_KEY');

            $response = $client->get($apiUrl . 'orders', [
                'headers' => [
                    'Authorization' => 'Basic ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'query' => [
                    'filter[customerNo]' => $customerNo,
                ],
            ]);

            $res = json_decode($response->getBody()->getContents(), true);
            $orders = $res['meta'] ?? [];
            $totalOrders = $orders['total'];

            return $totalOrders;

        } catch (\Exception $e) {
            Log::error("Error fetching order count for customer {$customerNo}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Could not fetch order count'
            ], 500);
        }
    }

    public function getNotifications(Request $request)
    {
        try {
            $client = new Client();
            $apiUrl = env('TRANSPORT_API_URL'); 
            $apiKey = env('TRANSPORT_API_KEY');

            // Get sorting parameters
            $apiQuery['sort'] = '-orderNo';
            $apiQuery['filter[status]'] = 'planned';
            $today = date('Y-m-d');
            
            $response = $client->get($apiUrl . 'orders', [
                'headers' => [
                    'Authorization' => 'Basic ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'query' => $apiQuery,
            ]);

            $res = json_decode($response->getBody()->getContents(), true);
            $records = collect($res['data'] ?? []);

            // Filter for recent orders (not just today)
            // $orders = $records->filter(function($order) {
            //     $destinations = $order['attributes']['destinations'] ?? [];
            //     $pickup = collect($destinations)->firstWhere('taskType', 'pickup');
            //     $status = $order['attributes']['status'] ?? '';
                
            //     // Include orders from last few days that are not quotes/pending
            //     return $pickup && 
            //         isset($pickup['date']) && 
            //         Carbon::parse($pickup['date'])->gte(Carbon::now()->subDays(3)) && // Last 3 days
            //         !in_array($status, ['pending-acceptation', 'quote']);
            // });

            $orders = $records->filter(function($order) use ($today) {
                $destinations = $order['attributes']['destinations'] ?? [];
                $pickup = collect($destinations)->firstWhere('taskType', 'pickup');
                $delivery = collect($destinations)->firstWhere('taskType', 'delivery');
                $status = $order['attributes']['status'] ?? '';
                
                // Check if pickup date is today
                $pickupToday = $pickup && isset($pickup['date']) && $pickup['date'] === $today;
                
                // Check if delivery date is today
                $deliveryToday = $delivery && isset($delivery['date']) && $delivery['date'] === $today;
                
                // Only include orders where:
                // 1. pickup date is today OR delivery date is today
                // 2. status is NOT "pending-acceptation" or "quote"
                return ($pickupToday || $deliveryToday) &&
                    !in_array($status, ['pending-acceptation', 'quote']);
            });

            $currentTime = Carbon::now();
            $notifications = [];
            $totalCount = 0;

            foreach ($orders as $order) {
                $destinations = $order['attributes']['destinations'] ?? [];
                $pickup = collect($destinations)->firstWhere('taskType', 'pickup');
                $delivery = collect($destinations)->firstWhere('taskType', 'delivery');
                $orderNo = $order['attributes']['orderNo'] ?? 'Unknown';

                // Check if this order is already completed
                $tracking = CurrentJobsTracking::where('order_id', $order['id'])->first();
                if ($tracking && $tracking->status === 'completed') {
                    continue; // Skip completed orders
                }

                // Check Collections Overdue (ANY DATE in the past)
                if ($pickup && isset($pickup['toTime']) && isset($pickup['date'])) {
                    try {
                        $pickupDateTime = Carbon::createFromFormat('Y-m-d H:i', $pickup['date'] . ' ' . $pickup['toTime']);
                        if ($currentTime->greaterThan($pickupDateTime)) {
                            // Only show if collection is not checked in yet
                            if (!$tracking || !$tracking->driver_eta_confirmed) {
                                $overdueHours = $currentTime->diffInHours($pickupDateTime);
                                $overdueMinutes = $currentTime->diffInMinutes($pickupDateTime);
                                
                                $overdueText = $overdueHours > 24 ? 
                                    floor($overdueHours / 24) . ' days' : 
                                    ($overdueHours > 0 ? $overdueHours . ' hrs' : $overdueMinutes . ' min');

                                $dateFormatted = \Carbon\Carbon::parse($pickup['date'])->format('d/m/Y');
                                $notifications[] = [
                                    'type' => 'collection_overdue',
                                    'title' => 'Collection Overdue',
                                    'message' => "Order #{$orderNo} collection was due {$dateFormatted} at {$pickup['toTime']}",
                                    'time' => Carbon::parse($pickup['date'])->format('M d'),
                                    'overdue_by' => $overdueText,
                                    'order_id' => $order['id'],
                                    'order_no' => $orderNo,
                                    'icon' => 'bx-time-five',
                                    'color' => 'danger',
                                    'priority' => $overdueMinutes // For sorting
                                ];
                                $totalCount++;
                            }
                        }
                    } catch (\Exception $e) {
                        // Skip if date parsing fails
                    }
                }

                // Check Deliveries Overdue (ANY DATE in the past)
                if ($delivery && isset($delivery['deliveryTime']) && isset($delivery['date'])) {
                    try {
                        $deliveryDateTime = Carbon::createFromFormat('Y-m-d H:i', $delivery['date'] . ' ' . $delivery['deliveryTime']);
                        if ($currentTime->greaterThan($deliveryDateTime)) {
                            // Only show if not delivered yet
                            if (!$tracking || !$tracking->delivered) {
                                $overdueHours = $currentTime->diffInHours($deliveryDateTime);
                                $overdueMinutes = $currentTime->diffInMinutes($deliveryDateTime);
                                
                                $overdueText = $overdueHours > 24 ? 
                                    floor($overdueHours / 24) . ' days' : 
                                    ($overdueHours > 0 ? $overdueHours . ' hrs' : $overdueMinutes . ' min');

                                $dateFormatted = \Carbon\Carbon::parse($delivery['date'])->format('d/m/Y');
                                $notifications[] = [
                                    'type' => 'delivery_overdue',
                                    'title' => 'Delivery Overdue',
                                    'message' => "Order #{$orderNo} delivery was due {$dateFormatted} at {$delivery['deliveryTime']}",
                                    'time' => Carbon::parse($delivery['date'])->format('M d'),
                                    'overdue_by' => $overdueText,
                                    'order_id' => $order['id'],
                                    'order_no' => $orderNo,
                                    'icon' => 'bx-truck',
                                    'color' => 'warning',
                                    'priority' => $overdueMinutes // For sorting
                                ];
                                $totalCount++;
                            }
                        }
                    } catch (\Exception $e) {
                        // Skip if date parsing fails
                    }
                }

                // Check Mid-Point Check Overdue (ANY DATE in the past)
                if ($pickup && $delivery && isset($pickup['toTime']) && isset($delivery['deliveryTime'])) {
                    try {
                        $collectionTime = Carbon::createFromFormat('Y-m-d H:i', $pickup['date'] . ' ' . $pickup['toTime']);
                        $deliveryTime = Carbon::createFromFormat('Y-m-d H:i', $delivery['date'] . ' ' . $delivery['deliveryTime']);
                        
                        // Only if journey is 2+ hours
                        if ($deliveryTime->diffInHours($collectionTime) >= 2) {
                            $midpointTime = $collectionTime->copy()->addMinutes($deliveryTime->diffInMinutes($collectionTime) / 2);
                            
                            if ($currentTime->greaterThan($midpointTime)) {
                                // Check if midpoint check is already completed
                                if (!$tracking || !$tracking->midpoint_check_completed) {
                                    $overdueHours = $currentTime->diffInHours($midpointTime);
                                    $overdueMinutes = $currentTime->diffInMinutes($midpointTime);
                                    
                                    $overdueText = $overdueHours > 24 ? 
                                        floor($overdueHours / 24) . ' days' : 
                                        ($overdueHours > 0 ? $overdueHours . ' hrs' : $overdueMinutes . ' min');

                                    $dateFormatted = \Carbon\Carbon::parse($pickup['date'])->format('d/m/Y');
                                    $notifications[] = [
                                        'type' => 'midpoint_overdue',
                                        'title' => 'Mid-Point Check Overdue',
                                        'message' => "Order #{$orderNo} mid-point check was due {$dateFormatted} at {$midpointTime->format('H:i')}",
                                        'time' => Carbon::parse($pickup['date'])->format('M d'),
                                        'overdue_by' => $overdueText,
                                        'order_id' => $order['id'],
                                        'order_no' => $orderNo,
                                        'icon' => 'bx-clipboard',
                                        'color' => 'info',
                                        'priority' => $overdueMinutes // For sorting
                                    ];
                                    $totalCount++;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // Skip if date parsing fails
                    }
                }
            }

            // Sort notifications by priority (most overdue first)
            // $notifications = collect($notifications)->sortByDesc('priority')->take(20)->values();
            $notifications = collect($notifications)->sortBy('priority')->take(20)->values();


            return response()->json([
                'success' => true,
                'total_count' => $totalCount,
                'notifications' => $notifications,
                'last_updated' => Carbon::now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error("Notification fetch error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'total_count' => 0,
                'notifications' => [],
                'error' => 'Failed to fetch notifications'
            ], 500);
        }
    }
    
}