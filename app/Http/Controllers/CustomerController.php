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
    /**
     * Display a listing of the resource.
     */
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
    //             $length = $request->input('length', 100);
    //             $searchValue = $request->input('search.value', '');

    //             // Calculate API page (API uses 1-based indexing)
    //             $apiPage = floor($start / 100) + 1;

    //             $apiQuery = ['page' => $apiPage];

    //             // Date Range Filtering
    //             if ($request->filled('fromDate')) {
    //                 $apiQuery['filter[createdAt][gte]'] = Carbon::parse($request->input('fromDate'))->startOfDay()->format('Y-m-d\TH:i:s');
    //             }
    //             if ($request->filled('toDate')) {
    //                 $apiQuery['filter[createdAt][lte]'] = Carbon::parse($request->input('toDate'))->endOfDay()->format('Y-m-d\TH:i:s');
    //             }
    //             if (!$request->filled('fromDate') && !$request->filled('toDate')) {
    //                 $apiQuery['filter[createdAt][gte]'] = Carbon::now()->subDays(7)->format('Y-m-d\TH:i:s');
    //             }

    //             Log::info('API Request - Page: ' . $apiPage . ', Start: ' . $start . ', Length: ' . $length);
    //             Log::info('API Query Parameters:', $apiQuery);

    //             $response = $client->get($apiUrl . 'customers', [
    //                 'headers' => [
    //                     'Authorization' => 'Basic ' . $apiKey,
    //                     'Content-Type'  => 'application/json',
    //                     'Accept'        => 'application/json',
    //                 ],
    //                 'query' => $apiQuery,
    //             ]);

    //             $res = json_decode($response->getBody()->getContents(), true);
    //             $customers = collect($res['data'] ?? []);
    //             $meta = $res['meta'] ?? [];

    //             // Transform data
    //             $transformedData = $customers->map(function($row) {
    //                 return [
    //                     'id' => $row['id'] ?? null,
    //                     'createdAt' => Carbon::parse($row['createdAt'])->format('d-m-Y H:i'),
    //                     'customerNo' => '<a href="/admin/customers/' . ($row['id'] ?? '') . '">' . ($row['attributes']['customerNo'] ?? 'N/A') . '</a>',
    //                     'companyName' => '<a href="/admin/customers/' . ($row['id'] ?? '') . '">' . ($row['attributes']['companyName'] ?? 'N/A') . '</a>',
    //                     'address' => in_array($row['attributes']['businessAddress']['address'] ?? 'N/A', ['', 'N/A']) ? '-' : $row['attributes']['businessAddress']['address'],
    //                     'industry' => in_array($row['attributes']['additionalField1'] ?? 'N/A', ['SDT Contact Us', 'CSD Instant Quote', 'Quote', 'N/A', 'Aircall CSD', 'MSDC Instant Quote','Aircall SDT']) ? '-' : $row['attributes']['additionalField1'],
    //                     'numberOfOrders' => rand(0, 100), // Replace with actual logic
    //                 ];
    //             });

    //             // Apply search filter if provided
    //             if (!empty($searchValue)) {
    //                 $transformedData = $transformedData->filter(function($item) use ($searchValue) {
    //                     $searchLower = strtolower($searchValue);
    //                     // Remove HTML tags for searching
    //                     $customerNo = strip_tags($item['customerNo']);
    //                     $companyName = strip_tags($item['companyName']);
                        
    //                     return str_contains(strtolower($companyName), $searchLower) ||
    //                         str_contains(strtolower($customerNo), $searchLower) ||
    //                         str_contains(strtolower($item['address']), $searchLower) ||
    //                         str_contains(strtolower($item['industry']), $searchLower);
    //                 });
    //             }

    //             return response()->json([
    //                 'draw' => intval($draw),
    //                 'recordsTotal' => $meta['total'] ?? 0,
    //                 'recordsFiltered' => $meta['total'] ?? 0, // Since API handles filtering
    //                 'data' => $transformedData->values()->toArray()
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

    //     return view('admin.customers.index');
    // }

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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $client = new Client();
        $apiUrl = env('TRANSPORT_API_URL');
        $apiKey = env('TRANSPORT_API_KEY');

        // Fetch all orders from API with optional date filter
        $response = $client->get($apiUrl . 'customers/'.$id, [
            'headers' => [
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
            // Optional filter; you can dynamically pass today's date
            'query' => [
                // 'filter[createdAt][gte]' => Carbon::now()->subDays(7)->format('Y-m-d\TH:i:s'),
            ],
        ]);

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);
        $customer = collect($data['data'] ?? []);

        if (!$customer) {
            abort(404);
        }

        return view('admin.customers.view', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
