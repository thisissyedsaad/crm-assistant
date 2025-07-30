<?php

namespace App\Http\Controllers\Schedular;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class CurrentJobsController extends Controller
{

    public function index(Request $request)
    {
        $totalJobs = 44;
        $collectionsOverdue = 12;
        $deliveriesOverdue = 23;
        $delivered = 13;

        $countData = [
            'totalJobs' => $totalJobs,
            'collectionsOverdue' => $collectionsOverdue,
            'deliveriesOverdue' => $deliveriesOverdue,
            'delivered' => $delivered,
        ];

        if ($request->ajax()) {

            try {
                $client = new Client();
                $apiUrl = env('TRANSPORT_API_URL'); 
                $apiKey = env('TRANSPORT_API_KEY');

                // DataTables parameters
                $draw = $request->input('draw', 1);
                $start = $request->input('start', 0);
                $length = $request->input('length', 25);
                $searchValue = $request->input('search.value', '');

                // Get sorting parameters
                $orderColumn = $request->input('order.0.column', 0);
                $orderDirection = $request->input('order.0.dir', 'desc');
                
                // Map column index to field name - FIXED FOR 15 COLUMNS
                $columns = ['updatedAt', 'orderNo', 'customerUserId', 'carrierNo', 'newExisting', 'collectionDate', 'collectionTime', 'departureTime', 'orderPrice', 'deliveryTime', 'midpointCheck', 'internalNotes', 'collectionCheckIn', 'driverConfirmedETA', 'midpointCheckComplete'];
                $sortField = $columns[$orderColumn] ?? 'updatedAt';

                // Build API query
                $apiQuery = [];

                // Date Range Filtering
                if ($request->filled('fromDate')) {
                    $apiQuery['filter[createdAt][gte]'] = Carbon::parse($request->input('fromDate'))->startOfDay()->format('Y-m-d\TH:i:s');
                }
                if ($request->filled('toDate')) {
                    $apiQuery['filter[createdAt][lte]'] = Carbon::parse($request->input('toDate'))->endOfDay()->format('Y-m-d\TH:i:s');
                }
                if (!$request->filled('fromDate') && !$request->filled('toDate')) {
                    $apiQuery['filter[createdAt][gte]'] = Carbon::now('UTC')->subDays(2)->format('Y-m-d\TH:i:s\Z');
                }

                // Set sorting - handle different field mappings for API
                if ($sortField === 'updatedAt') {
                    $apiQuery['sort'] = ($orderDirection === 'desc') ? '-updatedAt' : 'updatedAt';
                } elseif ($sortField === 'orderNo') {
                    $apiQuery['sort'] = ($orderDirection === 'desc') ? '-orderNo' : 'orderNo';
                } elseif ($sortField === 'status') {
                    $apiQuery['sort'] = ($orderDirection === 'desc') ? '-status' : 'status';
                }

                // Limit to maximum 100 records
                $apiQuery['filter[date]'] = $today = date('Y-m-d');

                $response = $client->get($apiUrl . 'orders', [
                    'headers' => [
                        'Authorization' => 'Basic ' . $apiKey,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json',
                    ],
                    'query' => $apiQuery,
                ]);

                $res = json_decode($response->getBody()->getContents(), true);
                $orders = collect($res['data'] ?? []);
                $meta = $res['meta'] ?? [];

                // FIXED Transform data
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
                            
                            if ($deliveryTime->diffInHours($collectionTime) >= 3) {
                                $midpointCheck = $collectionTime->addMinutes($deliveryTime->diffInMinutes($collectionTime) / 2)->format('H:i');
                            }
                        } catch (\Exception $e) {
                            $midpointCheck = null;
                        }
                    }

                    // Format collection date properly
                    $collectionDate = null;
                    if (isset($pickup['date'])) {
                        try {
                            $collectionDate = \Carbon\Carbon::parse($pickup['date'])->format('d-m-Y');
                        } catch (\Exception $e) {
                            $collectionDate = $pickup['date'];
                        }
                    }

                    // Get carrier/driver name from carrierNo
                    $driverName = null;
                    if (isset($row['attributes']['carrierNo'])) {
                        $driverName = $row['attributes']['carrierNo'];
                    }

                    // Get customer info
                    $userDisplay = null;
                    if (isset($row['attributes']['usernameCreated'])) {
                        $userDisplay = $row['attributes']['usernameCreated'] ?? '-';
                    }

                    // $orderCount = $this->getOrderCount($row['attributes']['customerNo']);
                    // $carrierName = $this->getCarrierName($row['attributes']['carrierNo']);

                    return [
                        'id' => $row['id'] ?? null,
                        'updatedAt' => isset($row['updatedAt']) ? Carbon::parse($row['updatedAt'])->format('d-m-Y H:i') : null,
                        'orderNo' => $row['attributes']['orderNo'] ?? null,
                        'customerUserId' => $userDisplay,
                        'carrierNo' => $driverName,
                        // 'newExisting' => $orderCount > 1 ? "Existing" : "New",
                        'collectionDate' => $collectionDate,
                        'collectionTime' => $pickup['toTime'] ?? null,
                        'departureTime' => $pickup['departureTime'] ?? null,
                        'orderPrice' => $row['attributes']['orderPrice'] ?? null,
                        'deliveryTime' => $delivery['deliveryTime'] ?? null,
                        'midpointCheck' => $midpointCheck,
                        'internalNotes' => $row['attributes']['internalNotes'] ?? null,
                        
                        // Additional fields for search and modal
                        'customerNo' => $row['attributes']['customerNo'] ?? null,
                        'vehicleTypeName' => $row['attributes']['vehicleTypeName'] ?? null,
                        'status' => $row['attributes']['status'] ?? null,
                    ];
                });

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

        return view('admin.schedular.current-jobs', compact('countData'));
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
}