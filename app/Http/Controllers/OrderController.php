<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function index(Request $request)
    {
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
                
                // Map column index to field name
                $columns = ['updatedAt', 'orderNo', 'vehicleTypeName', 'orderPrice', 'orderPurchasePrice', 'status', 'internalNotes'];
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
                } else {
                    // Default sort for fields that don't support API sorting
                    // $apiQuery['sort'] = '-createdAt';
                    // $apiQuery['sort'] = '-updatedAt';
                }

                // Limit to maximum 100 records
                $apiQuery['limit'] = 100;

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

                // Transform data
                $transformedData = $orders->map(function($row) {
                    return [
                        'id' => $row['id'] ?? null,
                        'updatedAt' => Carbon::parse($row['updatedAt'])->format('d-m-Y H:i'),
                        'orderNo' => $row['attributes']['orderNo'] ?? null,
                        'customerNo' => $row['attributes']['customerNo'] ?? null,
                        'vehicleTypeName' => $row['attributes']['vehicleTypeName'] ?? null,
                        'orderPrice' => $row['attributes']['orderPrice'] ?? null,
                        'orderPurchasePrice' => $row['attributes']['orderPurchasePrice'] ?? null,
                        'internalNotes' => $row['attributes']['internalNotes'] ?? null,
                        'status' => $row['attributes']['status'] ?? null,
                    ];
                });

                // Apply search filter if provided
                if (!empty($searchValue)) {
                    $transformedData = $transformedData->filter(function($item) use ($searchValue) {
                        $searchLower = strtolower($searchValue);
                        
                        return str_contains(strtolower($item['customerNo'] ?? ''), $searchLower) ||
                            str_contains(strtolower($item['orderNo'] ?? ''), $searchLower) ||
                            str_contains(strtolower($item['vehicleTypeName'] ?? ''), $searchLower) ||
                            str_contains(strtolower($item['status'] ?? ''), $searchLower);
                    });
                }

                // Apply client-side sorting for fields not sortable by API
                if (in_array($sortField, ['vehicleTypeName', 'orderPrice', 'orderPurchasePrice', 'internalNotes'])) {
                    $transformedData = $transformedData->sortBy(function($item) use ($sortField) {
                        // For price fields, convert to numeric for proper sorting
                        if (in_array($sortField, ['orderPrice', 'orderPurchasePrice'])) {
                            return (float) ($item[$sortField] ?? 0);
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

        return view('admin.orders.index');
    }

    public function getCustomer(Request $request)
    {
        try {
            $customerNo = $request->input('customerNo');
            
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

            return response()->json([
                'success' => true,
                'companyName' => $companyName
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
                            'filter[customerNo]' => $customerNo
                        ]
                    ]);

                    $ordersData = json_decode($ordersResponse->getBody()->getContents(), true);
                    $totalOrders = count($ordersData['data'] ?? []);

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
            $customer['createdAt'] = $customerData['data']['createdAt'];
            $customer['updatedAt'] = $customerData['data']['updatedAt'];

            return view('admin.orders.view', compact('order', 'customer', 'totalOrders', 'destinations'));

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Handle 404 and other 4xx errors
            if ($e->getResponse()->getStatusCode() === 404) {
                return redirect()->route('admin.orders.index')
                    ->with('error', 'Order not found in the system.');
            }
        }
    }

    public function autocomplete(Request $request)
    {
        try {
            $query = $request->get('query');
            
            if (strlen($query) < 2) {
                return response()->json(['data' => []]);
            }

            $client = new Client();
            $apiUrl = env('TRANSPORT_API_URL');
            $apiKey = env('TRANSPORT_API_KEY');

            $apiQuery = [
                'filter[orderNo]' => $query // Direct filter on orderNo
            ];

            $response = $client->get($apiUrl . 'orders', [
                'headers' => [
                    'Authorization' => 'Basic ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'query' => $apiQuery
            ]);

            $ordersData = json_decode($response->getBody()->getContents(), true);
            $orders = collect($ordersData['data'] ?? []);

            $results = $orders->take(15)->map(function($order) {
                $orderNo = $order['attributes']['orderNo'] ?? 'N/A';
                $createdDate = isset($order['createdAt']) ? 
                    \Carbon\Carbon::parse($order['createdAt'])->format('d/m/Y') : 'N/A';

                return [
                    'id' => $order['id'],
                    'orderNo' => $orderNo,
                    'createdDate' => $createdDate
                ];
            });

            return response()->json([
                'data' => $results->values()->toArray()
            ]);

        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $responseJson = json_decode($responseBody, true);
            return response()->json([
                'data' => [],
                'error' => $responseJson['message']
            ]);
        }
    }
}