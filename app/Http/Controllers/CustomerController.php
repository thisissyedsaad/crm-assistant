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
                $length = $request->input('length', 25);
                $searchValue = $request->input('search.value', '');

                // Get sorting parameters
                $orderColumn = $request->input('order.0.column', 0);
                $orderDirection = $request->input('order.0.dir', 'desc');
                
                // Map column index to field name
                $columns = ['updatedAt', 'customerNo', 'companyName', 'address', 'industry', 'numberOfOrders'];
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
                    $apiQuery['filter[createdAt][gte]'] = Carbon::now()->subDays(2)->format('Y-m-d\TH:i:s');
                }

                // Set sorting - handle different field mappings for API
                if ($sortField === 'updatedAt') {
                    $apiQuery['sort'] = ($orderDirection === 'desc') ? '-updatedAt' : 'updatedAt';
                } elseif ($sortField === 'customerNo') {
                    $apiQuery['sort'] = ($orderDirection === 'desc') ? '-customerNo' : 'customerNo';
                } elseif ($sortField === 'companyName') {
                    $apiQuery['sort'] = ($orderDirection === 'desc') ? '-companyName' : 'companyName';
                } else {
                    // Default sort for other fields
                    // $apiQuery['sort'] = '-createdAt';
                    // $apiQuery['sort'] = '-updatedAt';
                }

                // Limit to maximum 100 records
                $apiQuery['limit'] = 100;

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
                        'updatedAt' => Carbon::parse($row['updatedAt'])->format('d-m-Y H:i'),
                        'customerNo' => $row['attributes']['customerNo'] ?? 'N/A',
                        'companyName' => $row['attributes']['companyName'] ?? 'N/A',
                        'address' => in_array($row['attributes']['businessAddress']['address'] ?? 'N/A', ['', 'N/A']) ? '-' : $row['attributes']['businessAddress']['address'],
                        'industry' => in_array($row['attributes']['additionalField1'] ?? 'N/A', ['SDT Contact Us', 'CSD Instant Quote', 'Quote', 'N/A', 'Aircall CSD', 'MSDC Instant Quote','Aircall SDT']) ? '-' : $row['attributes']['additionalField1'],
                        'numberOfOrders' => 'Show', // Default to "Show" button
                    ];
                });

                // Apply search filter if provided
                if (!empty($searchValue)) {
                    $transformedData = $transformedData->filter(function($item) use ($searchValue) {
                        $searchLower = strtolower($searchValue);
                        
                        return str_contains(strtolower($item['companyName']), $searchLower) ||
                            str_contains(strtolower($item['customerNo']), $searchLower) ||
                            str_contains(strtolower($item['address']), $searchLower) ||
                            str_contains(strtolower($item['industry']), $searchLower);
                    });
                }

                // Apply client-side sorting for address, industry, and numberOfOrders (fields not sortable by API)
                if (in_array($sortField, ['address', 'industry', 'numberOfOrders'])) {
                    $transformedData = $transformedData->sortBy(function($item) use ($sortField) {
                        return strtolower($item[$sortField]);
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

    public function getOrderCount(Request $request)
    {
        try {
            $customerNo = $request->input('customer_no');
            
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
            $orders = $res['data'] ?? [];
            $totalOrders = count($orders);

            return response()->json([
                'success' => true,
                'total_orders' => $totalOrders
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching order count for customer {$customerNo}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Could not fetch order count'
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
                            'filter[customerNo]' => $customerNo,
                            'sort' => '-createdAt'
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

            $meta = $ordersData['meta'] ?? [];
            
            // Check if meta has total count
            if (isset($meta['total']) && is_numeric($meta['total'])) {
                $totalOrders = (int) $meta['total'];
            }

            return view('admin.customers.view', compact('customer', 'orders', 'totalOrders'));

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Handle 404 error
            if ($e->getResponse()->getStatusCode() === 404) {
                return redirect()->route('admin.customers.index')
                    ->with('error', 'Customer not found in the system.');
            }
        }
    }
    // public function show($id)
    // {
    //     $client = new Client();
    //     $apiUrl = env('TRANSPORT_API_URL');
    //     $apiKey = env('TRANSPORT_API_KEY');

    //     try {
    //         // Fetch customer from API
    //         $response = $client->get($apiUrl . 'customers/' . $id, [
    //             'headers' => [
    //                 'Authorization' => 'Basic ' . $apiKey,
    //                 'Content-Type'  => 'application/json',
    //                 'Accept'        => 'application/json',
    //             ],
    //         ]);

    //         $body = $response->getBody()->getContents();
    //         $data = json_decode($body, true);
    //         $customer = $data['data'] ?? null;

    //         if (!$customer || empty($customer)) {
    //             return redirect()->route('admin.customers.index')
    //                 ->with('error', 'Customer not found.');
    //         }

    //         // Fetch orders for this customer using customerNo
    //         $orders = [];
    //         $totalOrders = 0;
    //         $customerNo = $customer['attributes']['customerNo'] ?? null;
            
    //         if ($customerNo) {
    //             try {
    //                 // FAST SOLUTION: Smart 2-page check with meta fallback
    //                 $ordersResponse = $client->get($apiUrl . 'orders', [
    //                     'headers' => [
    //                         'Authorization' => 'Basic ' . $apiKey,
    //                         'Content-Type'  => 'application/json',
    //                         'Accept'        => 'application/json',
    //                     ],
    //                     'query' => [
    //                         'filter[customerNo]' => $customerNo,
    //                         'sort' => '-createdAt',
    //                         'page' => 1
    //                     ]
    //                 ]);

    //                 $ordersBody = $ordersResponse->getBody()->getContents();
    //                 $ordersData = json_decode($ordersBody, true);
    //                 $page1Orders = $ordersData['data'] ?? [];
    //                 $meta = $ordersData['meta'] ?? [];
                    
    //                 \Log::info("Customer {$customerNo}: Page 1 returned " . count($page1Orders) . " orders");
    //                 \Log::info("Customer {$customerNo}: Meta data: " . json_encode($meta));

    //                 // Strategy 1: Check if meta has total count
    //                 if (isset($meta['total']) && is_numeric($meta['total'])) {
    //                     $totalOrders = (int) $meta['total'];
    //                     $orders = array_slice($page1Orders, 0, 10); // Show first 10
    //                     \Log::info("Customer {$customerNo}: Using meta total = {$totalOrders}");
    //                 }
    //                 // Strategy 2: Smart page checking
    //                 else {
    //                     $allOrders = $page1Orders;
                        
    //                     // If page 1 has exactly 100 records, check page 2
    //                     if (count($page1Orders) === 100) {
    //                         try {
    //                             $page2Response = $client->get($apiUrl . 'orders', [
    //                                 'headers' => [
    //                                     'Authorization' => 'Basic ' . $apiKey,
    //                                     'Content-Type'  => 'application/json',
    //                                     'Accept'        => 'application/json',
    //                                 ],
    //                                 'query' => [
    //                                     'filter[customerNo]' => $customerNo,
    //                                     'sort' => '-createdAt',
    //                                     'page' => 2
    //                                 ]
    //                             ]);

    //                             $page2Body = $page2Response->getBody()->getContents();
    //                             $page2Data = json_decode($page2Body, true);
    //                             $page2Orders = $page2Data['data'] ?? [];
                                
    //                             \Log::info("Customer {$customerNo}: Page 2 returned " . count($page2Orders) . " orders");
                                
    //                             if (!empty($page2Orders)) {
    //                                 $allOrders = array_merge($allOrders, $page2Orders);
                                    
    //                                 // If page 2 also has 100 records, there might be more
    //                                 if (count($page2Orders) === 100) {
    //                                     $totalOrders = count($allOrders) . '+'; // Show as 200+
    //                                     \Log::info("Customer {$customerNo}: Estimated total = {$totalOrders} (more pages likely)");
    //                                 } else {
    //                                     $totalOrders = count($allOrders); // Exact count
    //                                     \Log::info("Customer {$customerNo}: Exact total = {$totalOrders}");
    //                                 }
    //                             } else {
    //                                 $totalOrders = count($page1Orders); // Exactly 100
    //                                 \Log::info("Customer {$customerNo}: Exact total = {$totalOrders}");
    //                             }
                                
    //                         } catch (\Exception $page2Error) {
    //                             \Log::warning("Failed to fetch page 2 for customer {$customerNo}: " . $page2Error->getMessage());
    //                             $totalOrders = count($page1Orders) . '+'; // Show as 100+
    //                         }
    //                     } else {
    //                         $totalOrders = count($page1Orders); // Less than 100, exact count
    //                         \Log::info("Customer {$customerNo}: Exact total = {$totalOrders}");
    //                     }
                        
    //                     // Always show first 10 orders for display
    //                     $orders = array_slice($allOrders, 0, 10);
    //                 }
                    
    //             } catch (\Exception $e) {
    //                 // If orders API fails, continue with empty orders array
    //                 \Log::warning('Failed to fetch orders for customer ' . $customerNo . ': ' . $e->getMessage());
    //                 $orders = [];
    //                 $totalOrders = 0;
    //             }
    //         }

    //         return view('admin.customers.view', compact('customer', 'orders', 'totalOrders'));

    //     } catch (\GuzzleHttp\Exception\ClientException $e) {
    //         // Handle 404 error
    //         if ($e->getResponse()->getStatusCode() === 404) {
    //             return redirect()->route('admin.customers.index')
    //                 ->with('error', 'Customer not found in the system.');
    //         }
            
    //         \Log::error('Customer show error: ' . $e->getMessage());
    //         return redirect()->route('admin.customers.index')
    //             ->with('error', 'Error loading customer details.');
    //     }
    // }
}