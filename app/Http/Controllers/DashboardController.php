<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;

class DashboardController extends Controller
{
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
                // 'filter[date][gte]' => '2025-06-02',
                'filter[date][gte]' => Carbon::today()->format('Y-m-d'),
            ],
        ]);

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        $orders = collect($data['data'] ?? []);

        // Group orders by customerNo
        $ordersGrouped = $orders
            ->filter(fn ($order) => isset($order['attributes']['customerNo'], $order['attributes']['date']))
            ->groupBy(fn ($order) => $order['attributes']['customerNo']);

        // Init counters
        $newOrdersToday = 0;
        $existingOrdersToday = 0;
        $newOrdersMonth = 0;
        $existingOrdersMonth = 0;

        $today = Carbon::today()->toDateString();
        $thisMonth = Carbon::now()->month;

        foreach ($ordersGrouped as $customerOrders) {
            $orderDates = collect($customerOrders)->pluck('attributes.date');

            // Count how many of this customer's orders are for today
            $ordersToday = $orderDates->filter(fn ($date) => Carbon::parse($date)->isSameDay($today))->count();

            if ($ordersToday > 0) {
                if ($customerOrders->count() === 1) {
                    $newOrdersToday++;
                } else {
                    $existingOrdersToday++;
                }
            }

            // Count how many orders this month
            $ordersThisMonth = $orderDates->filter(fn ($date) => Carbon::parse($date)->month === $thisMonth)->count();

            if ($ordersThisMonth > 0) {
                if ($customerOrders->count() === 1) {
                    $newOrdersMonth++;
                } else {
                    $existingOrdersMonth++;
                }
            }
        }

        return view('dashboard', compact(
            'newOrdersToday',
            'existingOrdersToday',
            'newOrdersMonth',
            'existingOrdersMonth'
        ));
}
}