<?php

namespace App\Http\Controllers\Schedular;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CurrentJobsTracking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CompletedJobsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                // DataTables parameters
                $draw = $request->input('draw', 1);
                $start = $request->input('start', 0);
                $length = $request->input('length', 25);
                $searchValue = $request->input('search.value', '');

                // Get sorting parameters
                $orderColumn = $request->input('order.0.column', 0);
                $orderDirection = $request->input('order.0.dir', 'desc');
                
                // Map column index to field name - Same as current jobs (13 columns)
                $columns = ['updated_at', 'orderNo', 'customerUserId', 'collectionDate', 'collectionTime', 'departureTime', 'orderPrice', 'deliveryTime', 'midpointCheck', 'internalNotes', 'collectionCheckIn', 'driverConfirmedETA', 'midpointCheckComplete'];
                $sortField = $columns[$orderColumn] ?? 'updated_at';

                // Build query for completed jobs only
                $query = CurrentJobsTracking::where('status', 'completed');

                // Date Range Filtering
                if ($request->filled('fromDate')) {
                    $query->whereDate('updated_at', '>=', Carbon::parse($request->input('fromDate'))->startOfDay());
                }
                if ($request->filled('toDate')) {
                    $query->whereDate('updated_at', '<=', Carbon::parse($request->input('toDate'))->endOfDay());
                }

                // Apply search filter if provided
                if (!empty($searchValue)) {
                    $query->where(function($q) use ($searchValue) {
                        $q->whereRaw("JSON_EXTRACT(order_data, '$.attributes.orderNo') LIKE ?", ["%{$searchValue}%"])
                          ->orWhereRaw("JSON_EXTRACT(order_data, '$.attributes.internalNotes') LIKE ?", ["%{$searchValue}%"])
                          ->orWhereRaw("JSON_EXTRACT(order_data, '$.attributes.customerNo') LIKE ?", ["%{$searchValue}%"])
                          ->orWhereRaw("JSON_EXTRACT(order_data, '$.attributes.usernameCreated') LIKE ?", ["%{$searchValue}%"]);
                    });
                }

                // Apply sorting
                if ($sortField === 'updated_at') {
                    $query->orderBy('updated_at', $orderDirection);
                } else {
                    // For JSON fields, we'll sort by updated_at as fallback
                    $query->orderBy('updated_at', $orderDirection);
                }

                // Get total count before pagination
                $totalRecords = $query->count();

                // Apply pagination
                $completedJobs = $query->skip($start)->take($length)->get();

                // Transform data for DataTable
                $transformedData = $completedJobs->map(function($job) {
                    // Extract data from JSON
                    $orderData = $job->order_data;
                    $attributes = $orderData['data']['attributes'] ?? [];
                    $destinations = $attributes['destinations'] ?? [];
                    
                    // Get pickup and delivery destinations
                    $pickup = collect($destinations)->firstWhere('taskType', 'pickup');
                    $delivery = collect($destinations)->firstWhere('taskType', 'delivery');

                    // Calculate collection data
                    $collectionDate = null;
                    $collectionTime = null;
                    if ($pickup) {
                        $collectionDate = isset($pickup['date']) ? Carbon::parse($pickup['date'])->format('d-m-Y') : null;
                        $collectionTime = $pickup['toTime'] ?? $pickup['fromTime'] ?? null;
                    }

                    // Get delivery info
                    $deliveryTime = null;
                    if ($delivery) {
                        $deliveryTime = $delivery['deliveryTime'] ?? $delivery['toTime'] ?? null;
                    }

                    // Calculate midpoint check
                    $midpointCheck = null;
                    if ($pickup && $delivery && isset($pickup['toTime']) && isset($delivery['deliveryTime'])) {
                        try {
                            $collectionDateTime = Carbon::createFromFormat('Y-m-d H:i', $pickup['date'] . ' ' . $pickup['toTime']);
                            $deliveryDateTime = Carbon::createFromFormat('Y-m-d H:i', $delivery['date'] . ' ' . $delivery['deliveryTime']);
                            
                            if ($deliveryDateTime->diffInHours($collectionDateTime) >= 3) {
                                $midpointCheck = $collectionDateTime->addMinutes($deliveryDateTime->diffInMinutes($collectionDateTime) / 2)->format('H:i');
                            }
                        } catch (\Exception $e) {
                            $midpointCheck = null;
                        }
                    }

                    // Get customer info
                    $userDisplay = null;
                    if (isset($attributes['usernameCreated'])) {
                        $userDisplay = $attributes['usernameCreated'] ?? '-';
                    }

                    return [
                        'id' => $job->order_id,
                        'updatedAt' => $job->updated_at ? $job->updated_at->format('d-m-Y H:i') : null,
                        'orderNo' => $attributes['orderNo'] ?? null,
                        'customerUserId' => $userDisplay,
                        'carrierNo' => isset($attributes['carrierNo']) ? $attributes['carrierNo'] : null,
                        'collectionDate' => $collectionDate,
                        'collectionTime' => $collectionTime,
                        'departureTime' => $pickup['departureTime'] ?? null,
                        'orderPrice' => $attributes['orderPrice'] ?? null,
                        'deliveryTime' => $deliveryTime,
                        'midpointCheck' => $midpointCheck,
                        'internalNotes' => $attributes['internalNotes'] ?? null,
                        
                        // Show completed status for all three
                        'collectionCheckIn' => $job->collection_checked_in ? 'Completed' : 'Not Done',
                        'driverConfirmedETA' => $job->driver_eta_confirmed ? 'Confirmed' : 'Not Done',
                        'midpointCheckComplete' => $job->midpoint_check_completed ? 'Complete' : 'Not Done',
                        
                        // Additional fields for modal
                        'customerNo' => $attributes['customerNo'] ?? null,
                        'vehicleTypeName' => $attributes['vehicleTypeName'] ?? null,
                        'status' => 'Completed', // All are completed
                        'completedAt' => $job->updated_at ? $job->updated_at->format('d-m-Y H:i') : null,
                    ];
                });

                return response()->json([
                    'draw' => intval($draw),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $transformedData->toArray()
                ]);

            } catch (\Exception $e) {
                Log::error("Completed Jobs DataTables error: " . $e->getMessage());
                return response()->json([
                    'draw' => $request->input('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Something went wrong while fetching completed jobs: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('admin.schedular.completed-jobs');
    }

    // Get customer details for modal (reuse from current jobs)
    public function getCustomer(Request $request)
    {
        try {
            $customerNo = $request->input('customerNo');
            $carrierNo = $request->input('carrierNo');
            $carrierName = "-";

            $client = new \GuzzleHttp\Client();
            $apiUrl = env('TRANSPORT_API_URL') ?: 'https://mytransport.co.uk/collectsameday/api/v1/';
            $apiKey = env('TRANSPORT_API_KEY') ?: 'Y3JtYXNzaXN0YW50Ok0yMDFrd3FxISE=';

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
            if(!empty($carrierNo) && $carrierNo > 0){
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
                $carrierName = $carrierData['data'][0]['attributes']['name'] ?? "Driver #$carrierNo";
            }

            return response()->json([
                'success' => true,
                'companyName' => $companyName,
                'carrierName' => $carrierName,
                'newExist' => $totalOrders > 1 ? 'Existing' : 'New'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'companyName' => "Customer #{$customerNo}",
                'carrierName' => "Driver #$carrierNo",
                'newExist' => 'Unknown'
            ]);
        }
    }
}