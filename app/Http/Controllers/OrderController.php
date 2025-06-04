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

                // Transform data - FIXED: Return raw data for frontend processing
                $transformedData = $orders->map(function($row) {
                    return [
                        'id' => $row['id'] ?? null,
                        'createdAt' => Carbon::parse($row['createdAt'])->format('d-m-Y H:i'),
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
                            str_contains(strtolower($item['vehicleTypeName'] ?? ''), $searchLower);
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

        // Fetch all orders from API with optional date filter
        $response = $client->get($apiUrl . 'orders/'.$id, [
            'headers' => [
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
            // Optional filter; you can dynamically pass today's date
            'query' => [
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $order = collect($data['data'] ?? []);
        $customerNo = $order['attributes']['customerNo'];

        $res = $client->get($apiUrl . "customers/{$customerNo}", [
            'headers' => [
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ]);

        $customerData = json_decode($res->getBody()->getContents(), true);
        $companyName = $customerData['data']['attributes']['companyName'] ? $customerData['data']['attributes']['companyName'] . " ($customerNo)" :  " - {$customerNo}";
        $order['companyName'] = $companyName;

        if (!$order) {
            abort(404);
        }

        return view('admin.orders.view', compact('order'));
    }

}