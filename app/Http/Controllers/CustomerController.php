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
                })->filter(function($item) {
                    // FILTER OUT records with +44 in companyName
                    return !str_contains($item['companyName'], '+44');
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
    // public function autocomplete(Request $request)
    // {
    //     try {
    //         $query = $request->input('query', '');
            
    //         if (strlen($query) < 2) {
    //             return response()->json([
    //                 'data' => [],
    //                 'message' => 'Query too short'
    //             ]);
    //         }

    //         $client = new Client();
    //         $apiUrl = env('TRANSPORT_API_URL'); 
    //         $apiKey = env('TRANSPORT_API_KEY');

    //         $apiQuery = [
    //             'filter[companyName]' => '%' . $query . '%',
    //             // 'filter[customerNo]' => '%' . $query . '%',
    //         ];

    //         $response = $client->get($apiUrl . 'customers', [
    //             'headers' => [
    //                 'Authorization' => 'Basic ' . $apiKey,
    //                 'Content-Type'  => 'application/json',
    //                 'Accept'        => 'application/json',
    //             ],
    //             'query' => $apiQuery,
    //         ]);

    //         $res = json_decode($response->getBody()->getContents(), true);
    //         $customers = collect($res['data'] ?? []);
            
    //         // Transform data WITHOUT last order dates (for speed)
    //         $transformedData = $customers->map(function($row) {
    //             $customerNo = $row['attributes']['customerNo'] ?? null;
    //             $companyName = $row['attributes']['companyName'] ?? null;
                
    //             return [
    //                 'id' => $row['id'] ?? null,
    //                 'customerNo' => $customerNo,
    //                 'companyName' => $companyName,
    //                 'displayText' => $companyName ?: $customerNo
    //             ];
    //         })->filter(function($item) {
    //             return $item['customerNo'] || $item['companyName'];
    //         });

    //         return response()->json([
    //             'data' => $transformedData->values()->toArray(),
    //             'total' => $transformedData->count()
    //         ]);

    //     } 
    //     catch (\GuzzleHttp\Exception\ClientException $e) {
    //         $responseBody = $e->getResponse()->getBody()->getContents();
    //         $responseJson = json_decode($responseBody, true);
    //         return response()->json([
    //             'data' => [],
    //             'error' => $responseJson['message']
    //         ]);
    //     }
    // }

    // public function autocomplete(Request $request)
    // {
    //     try {
    //         $query = $request->input('query', '');
            
    //         if (strlen($query) < 2) {
    //             return response()->json([
    //                 'data' => [],
    //                 'message' => 'Query too short'
    //             ]);
    //         }

    //         $client = new Client();
    //         $apiUrl = env('TRANSPORT_API_URL'); 
    //         $apiKey = env('TRANSPORT_API_KEY');

    //         // Create SMART patterns that actually work
    //         $words = explode(' ', trim($query));
    //         $patterns = [];
            
    //         // Add original query
    //         $patterns[] = $query;
            
    //         // Add version without spaces
    //         $patterns[] = str_replace(' ', '', $query);
            
    //         // Add version with different separators
    //         $patterns[] = str_replace(' ', '-', $query);
    //         $patterns[] = str_replace(' ', '_', $query);
            
    //         // For each word, also add spaced version (only for short words)
    //         foreach ($words as $word) {
    //             if (strlen(trim($word)) >= 2 && strlen(trim($word)) <= 4) {
    //                 $spacedWord = implode(' ', str_split(trim($word)));
    //                 $patterns[] = str_replace($word, $spacedWord, $query);
    //             }
    //         }
            
    //         // Remove duplicates and empty patterns
    //         $patterns = array_unique(array_filter($patterns));
            
    //         $allResults = collect();
            
    //         foreach ($patterns as $pattern) {
    //             $apiQuery = [
    //                 'filter[companyName]' => '%' . $pattern . '%',
    //             ];

    //             try {
    //                 $response = $client->get($apiUrl . 'customers', [
    //                     'headers' => [
    //                         'Authorization' => 'Basic ' . $apiKey,
    //                         'Content-Type'  => 'application/json',
    //                         'Accept'        => 'application/json',
    //                     ],
    //                     'query' => $apiQuery,
    //                 ]);

    //                 $res = json_decode($response->getBody()->getContents(), true);
    //                 $customers = collect($res['data'] ?? []);
    //                 $allResults = $allResults->merge($customers);
    //             } catch (\Exception $e) {
    //                 // Continue with other patterns if one fails
    //                 continue;
    //             }
    //         }

    //         // Remove duplicates by ID
    //         $uniqueResults = $allResults->unique('id');
            
    //         // Transform data
    //         $transformedData = $uniqueResults->map(function($row) {
    //             $customerNo = $row['attributes']['customerNo'] ?? null;
    //             $companyName = $row['attributes']['companyName'] ?? null;
                
    //             return [
    //                 'id' => $row['id'] ?? null,
    //                 'customerNo' => $customerNo,
    //                 'companyName' => $companyName,
    //                 'displayText' => $companyName ?: $customerNo
    //             ];
    //         })->filter(function($item) {
    //             return $item['customerNo'] || $item['companyName'];
    //         });

    //         return response()->json([
    //             'data' => $transformedData->values()->toArray(),
    //             'total' => $transformedData->count()
    //         ]);

    //     } catch (\GuzzleHttp\Exception\ClientException $e) {
    //         $responseBody = $e->getResponse()->getBody()->getContents();
    //         $responseJson = json_decode($responseBody, true);
    //         return response()->json([
    //             'data' => [],
    //             'error' => $responseJson['message']
    //         ]);
    //     }
    // }

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

        // Create BIDIRECTIONAL patterns (both ways)
        $patterns = [];
        
        // Add original query
        $patterns[] = $query;
        
        // Add version without spaces
        $patterns[] = str_replace(' ', '', $query);
        
        // Add version with different separators
        $patterns[] = str_replace(' ', '-', $query);
        $patterns[] = str_replace(' ', '_', $query);
        
        // BIDIRECTIONAL LOGIC: Handle both directions
        $words = explode(' ', trim($query));
        
        foreach ($words as $word) {
            $cleanWord = trim($word);
            
            if (strlen($cleanWord) >= 2 && strlen($cleanWord) <= 4) {
                // If word has spaces, create without spaces version
                if (strpos($cleanWord, ' ') !== false) {
                    $withoutSpaces = str_replace(' ', '', $cleanWord);
                    $patterns[] = str_replace($cleanWord, $withoutSpaces, $query);
                } else {
                    // If word has no spaces, create spaced version
                    $spacedWord = implode(' ', str_split($cleanWord));
                    $patterns[] = str_replace($cleanWord, $spacedWord, $query);
                }
            }
        }
        
        // ADDITIONAL: Handle the entire query transformation (FIXED)
        // If query has spaces between single letters, also try without spaces
        if (preg_match('/\b\w\s+\w\b/', $query)) {
            $compactQuery = preg_replace('/\b(\w)\s+(\w)\b/', '$1$2', $query);
            $patterns[] = $compactQuery;
        }
        
        // If query has compact letters, also try with spaces (FIXED)
        $expandedQuery = preg_replace_callback('/\b(\w{2,4})\b/', function($matches) {
            return implode(' ', str_split($matches[1]));
        }, $query);
        
        if ($expandedQuery !== $query) {
            $patterns[] = $expandedQuery;
        }
        
        // Remove duplicates and empty patterns
        $patterns = array_unique(array_filter($patterns));
        
        $allResults = collect();
        
        foreach ($patterns as $pattern) {
            $apiQuery = [
                'filter[companyName]' => '%' . $pattern . '%',
            ];

            try {
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
                $allResults = $allResults->merge($customers);
            } catch (\Exception $e) {
                continue;
            }
        }

        // Remove duplicates by ID
        $uniqueResults = $allResults->unique('id');
        
        // Transform data
        $transformedData = $uniqueResults->map(function($row) {
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

    } catch (\GuzzleHttp\Exception\ClientException $e) {
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

            // Initialize variables
            $orders = [];
            $totalOrders = 0;
            $customerAOV = 0;
            $customerNo = $customer['attributes']['customerNo'] ?? null;
            
            if ($customerNo) {
                try {
                    \Log::info("Starting AOV calculation for customer: {$customerNo}");
                    
                    // FETCH ALL ORDERS WITH PAGINATION LOOP
                    $allOrders = collect();
                    $currentPage = 1;
                    $maxPages = 50; // Safety limit: max 50 pages (5000 orders)
                    $totalRevenue = 0;
                    $orderCount = 0;
                    
                    do {
                        \Log::info("Fetching page {$currentPage} for customer {$customerNo}");
                        
                        $ordersResponse = $client->get($apiUrl . 'orders', [
                            'headers' => [
                                'Authorization' => 'Basic ' . $apiKey,
                                'Content-Type'  => 'application/json',
                                'Accept'        => 'application/json',
                            ],
                            'query' => [
                                'filter[customerNo]' => $customerNo,
                                'sort' => '-createdAt',
                                'page' => $currentPage
                            ]
                        ]);

                        $ordersBody = $ordersResponse->getBody()->getContents();
                        $ordersData = json_decode($ordersBody, true);
                        $pageOrders = collect($ordersData['data'] ?? []);
                        
                        \Log::info("Page {$currentPage} returned {$pageOrders->count()} orders for customer {$customerNo}");
                        
                        // If no orders on this page, we're done
                        if ($pageOrders->isEmpty()) {
                            \Log::info("No more orders found. Stopping at page {$currentPage}");
                            break;
                        }
                        
                        // Add to collection
                        $allOrders = $allOrders->merge($pageOrders);
                        
                        // Calculate revenue from this page
                        foreach ($pageOrders as $order) {
                            $orderPrice = $order['attributes']['orderPrice'] ?? 0;
                            if (is_numeric($orderPrice) && $orderPrice > 0) {
                                $totalRevenue += (float) $orderPrice;
                                $orderCount++;
                            }
                        }
                        
                        // If less than 100 records, this is the last page
                        if ($pageOrders->count() < 100) {
                            \Log::info("Last page reached (< 100 records). Total pages: {$currentPage}");
                            break;
                        }
                        
                        $currentPage++;
                        
                    } while ($currentPage <= $maxPages);
                    
                    // Calculate final AOV
                    $customerAOV = $orderCount > 0 ? round($totalRevenue / $orderCount, 2) : 0;
                    $totalOrders = $allOrders->count();
                    
                    // Get first 10 orders for display in Order History tab
                    $orders = $allOrders->toArray();
                    
                    \Log::info("Customer {$customerNo}: Total orders = {$totalOrders}, Total revenue = £{$totalRevenue}, AOV = £{$customerAOV}");
                    
                    // Get meta from first page if needed (for compatibility)
                    if ($currentPage === 2) { // Only 1 page was fetched
                        $ordersResponse = $client->get($apiUrl . 'orders', [
                            'headers' => [
                                'Authorization' => 'Basic ' . $apiKey,
                                'Content-Type'  => 'application/json',
                                'Accept'        => 'application/json',
                            ],
                            'query' => [
                                'filter[customerNo]' => $customerNo,
                                'sort' => '-createdAt',
                                'page' => 1
                            ]
                        ]);
                        $ordersBody = $ordersResponse->getBody()->getContents();
                        $ordersData = json_decode($ordersBody, true);
                        $orders = array_slice($ordersData['data'] ?? [], 0, 10);
                    }
                    
                } catch (\Exception $e) {
                    // If orders API fails, continue with empty data
                    \Log::error('Failed to fetch orders for customer ' . $customerNo . ': ' . $e->getMessage());
                    $orders = [];
                    $totalOrders = 0;
                    $customerAOV = 0;
                }
            }

            return view('admin.customers.view', compact('customer', 'orders', 'totalOrders', 'customerAOV'));

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Handle 404 error
            if ($e->getResponse()->getStatusCode() === 404) {
                return redirect()->route('admin.customers.index')
                    ->with('error', 'Customer not found in the system.');
            }
            
            \Log::error('Customer show error: ' . $e->getMessage());
            return redirect()->route('admin.customers.index')
                ->with('error', 'Error loading customer details.');
        }
    }
}