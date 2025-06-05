<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
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
                $length = $request->input('length', 25); // Changed from 100 to 25
                $searchValue = $request->input('search.value', '');

                // Date Range Filtering
                if ($request->filled('fromDate')) {
                    $apiQuery['filter[createdAt][gte]'] = Carbon::parse($request->input('fromDate'))->startOfDay()->format('Y-m-d\TH:i:s');
                }
                if ($request->filled('toDate')) {
                    $apiQuery['filter[createdAt][lte]'] = Carbon::parse($request->input('toDate'))->endOfDay()->format('Y-m-d\TH:i:s');
                }
                if (!$request->filled('fromDate') && !$request->filled('toDate')) {
                    $apiQuery['filter[createdAt][gte]'] = Carbon::now()->subDays(7)->format('Y-m-d\TH:i:s');
                }

                $response = $client->get($apiUrl . 'customers', [
                    'headers' => [
                        'Authorization' => 'Basic ' . $apiKey,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json',
                    ],
                    'query' => $apiQuery,
                ]);

                $res = json_decode($response->getBody()->getContents(), true);
                $customers = collect($res['data'] ?? []);
                $meta = $res['meta'] ?? [];

                // Transform data
                $transformedData = $customers->map(function($row) {
                    return [
                        'id' => $row['id'] ?? null,
                        'createdAt' => Carbon::parse($row['createdAt'])->format('d-m-Y H:i'),
                        'customerNo' => '<a href="/admin/customers/' . ($row['id'] ?? '') . '">' . ($row['attributes']['customerNo'] ?? 'N/A') . '</a>',
                        'companyName' => '<a href="/admin/customers/' . ($row['id'] ?? '') . '">' . ($row['attributes']['companyName'] ?? 'N/A') . '</a>',
                        'address' => in_array($row['attributes']['businessAddress']['address'] ?? 'N/A', ['', 'N/A']) ? '-' : $row['attributes']['businessAddress']['address'],
                        'industry' => in_array($row['attributes']['additionalField1'] ?? 'N/A', ['SDT Contact Us', 'CSD Instant Quote', 'Quote', 'N/A', 'Aircall CSD', 'MSDC Instant Quote','Aircall SDT']) ? '-' : $row['attributes']['additionalField1'],
                        'numberOfOrders' => rand(0, 100), // Replace with actual logic
                    ];
                });

                // Apply search filter if provided
                if (!empty($searchValue)) {
                    $transformedData = $transformedData->filter(function($item) use ($searchValue) {
                        $searchLower = strtolower($searchValue);
                        // Remove HTML tags for searching
                        $customerNo = strip_tags($item['customerNo']);
                        $companyName = strip_tags($item['companyName']);
                        
                        return str_contains(strtolower($companyName), $searchLower) ||
                            str_contains(strtolower($customerNo), $searchLower) ||
                            str_contains(strtolower($item['address']), $searchLower) ||
                            str_contains(strtolower($item['industry']), $searchLower);
                    });
                }

                // Handle frontend pagination (25 per page from 100 API results)
                $recordsFromThisPage = $start % 100; // Position within current API page
                $recordsToTake = min($length, $transformedData->count() - $recordsFromThisPage);
                $paginatedData = $transformedData->slice($recordsFromThisPage, $recordsToTake)->values();

                return response()->json([
                    'draw' => intval($draw),
                    'recordsTotal' => $meta['total'] ?? 0,
                    'recordsFiltered' => $meta['total'] ?? 0,
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

        return view('admin.customers.index');
    }

    /**
     * Autocomplete search for customers (FAST - no last order dates)
     */
    public function autocomplete(Request $request)
    {
        try {
            $query = $request->input('query', '');
            
            if (strlen($query) < 2) {
                return response()->json([
                    'data' => [],
                    'message' => 'Query too short'
                ]);
            }

            $client = new Client();
            $apiUrl = env('TRANSPORT_API_URL'); 
            $apiKey = env('TRANSPORT_API_KEY');

            $apiQuery = [
                'filter[companyName]' => '%' . $query . '%',
                // 'filter[customerNo]' => '%' . $query . '%',
            ];

            $response = $client->get($apiUrl . 'customers', [
                'headers' => [
                    'Authorization' => 'Basic ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'query' => $apiQuery,
            ]);

            $res = json_decode($response->getBody()->getContents(), true);
            $customers = collect($res['data'] ?? []);
            
            // Transform data WITHOUT last order dates (for speed)
            $transformedData = $customers->map(function($row) {
                $customerNo = $row['attributes']['customerNo'] ?? null;
                $companyName = $row['attributes']['companyName'] ?? null;
                
                return [
                    'id' => $row['id'] ?? null,
                    'customerNo' => $customerNo,
                    'companyName' => $companyName,
                    'displayText' => $companyName ?: $customerNo
                ];
            })->filter(function($item) {
                return $item['customerNo'] || $item['companyName'];
            });

            return response()->json([
                'data' => $transformedData->values()->toArray(),
                'total' => $transformedData->count()
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

    public function getLastOrder(Request $request)
    {
        try {
            $customerId = $request->input('customer_id');
            
            if (!$customerId) {
                return response()->json([
                    'error' => 'Customer ID required'
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
                    'filter[customerNo]' => $customerId,
                    'sort' => '-createdAt', // Sort by newest first
                ],
            ]);

            $res = json_decode($response->getBody()->getContents(), true);
            $orders = $res['data'] ?? [];

            if (!empty($orders)) {
                $lastOrder = $orders[0];
                $lastOrderDate = $lastOrder['createdAt'] ?? null;
                
                if ($lastOrderDate) {
                    $formattedDate = Carbon::parse($lastOrderDate)->format('d/m/Y');
                    return response()->json([
                        'success' => true,
                        'last_order_date' => $formattedDate,
                        'raw_date' => $lastOrderDate
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'last_order_date' => 'No orders',
                'raw_date' => null
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching last order for customer {$customerId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Could not fetch last order'
            ], 500);
        }
    }

    public function show($id)
    {
        $client = new Client();
        $apiUrl = env('TRANSPORT_API_URL');
        $apiKey = env('TRANSPORT_API_KEY');

        try {
            // Fetch customer from API
            $response = $client->get($apiUrl . 'customers/' . $id, [
                'headers' => [
                    'Authorization' => 'Basic ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            $customer = $data['data'] ?? null;

            if (!$customer || empty($customer)) {
                return redirect()->route('admin.customers.index')
                    ->with('error', 'Customer not found.');
            }

            // Fetch orders for this customer using customerNo
            $orders = [];
            $customerNo = $customer['attributes']['customerNo'] ?? null;
            
            if ($customerNo) {
                try {
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

                    $ordersBody = $ordersResponse->getBody()->getContents();
                    $ordersData = json_decode($ordersBody, true);
                    $orders = $ordersData['data'] ?? [];
                    
                } catch (\Exception $e) {
                    // If orders API fails, continue with empty orders array
                    \Log::warning('Failed to fetch orders for customer ' . $customerNo . ': ' . $e->getMessage());
                }
            }

            return view('admin.customers.view', compact('customer', 'orders'));

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Handle 404 error
            if ($e->getResponse()->getStatusCode() === 404) {
                return redirect()->route('admin.customers.index')
                    ->with('error', 'Customer not found in the system.');
            }
        }
    }
}