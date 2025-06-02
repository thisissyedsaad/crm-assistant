<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $client = new Client();
        $apiUrl = env('TRANSPORT_API_URL');
        $apiKey = env('TRANSPORT_API_KEY');

        // Fetch all orders from API with optional date filter
        $response = $client->get($apiUrl . 'orders', [
            'headers' => [
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
            // Optional filter; you can dynamically pass today's date
            'query' => [
                'filter[createdAt][gte]' => Carbon::now()->subDays(7)->format('Y-m-d\TH:i:s'),
            ],
        ]);

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);
        $orders = collect($data['data'] ?? []);
        return view('admin.orders.index', compact('orders'));
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
    public function show(string $id)
    {
        //
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
