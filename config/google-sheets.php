<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Service Account Credentials
    |--------------------------------------------------------------------------
    |
    | Path to the Google Service Account JSON credentials file.
    | Download this from Google Cloud Console after creating a service account.
    |
    */
    'credentials_path' => env('GOOGLE_CREDENTIALS_PATH', storage_path('app/google-credentials.json')),

    /*
    |--------------------------------------------------------------------------
    | Google Sheets ID
    |--------------------------------------------------------------------------
    |
    | The ID of the Google Sheet containing sales data.
    | This can be found in the Google Sheet URL.
    |
    */
    'sheet_id' => env('GOOGLE_SHEETS_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Sheet Name
    |--------------------------------------------------------------------------
    |
    | The name of the specific sheet/tab within the spreadsheet.
    | Leave empty for the first sheet.
    |
    */
    'sheet_name' => env('GOOGLE_SHEETS_NAME', 'Sheet1'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (Time to Live)
    |--------------------------------------------------------------------------
    |
    | How long to cache Google Sheets data in seconds.
    | Default: 300 seconds (5 minutes)
    | Adjust this value based on how frequently data updates in the sheet.
    |
    */
    'cache_ttl' => env('GOOGLE_SHEETS_CACHE_TTL', 300),

    /*
    |--------------------------------------------------------------------------
    | Column Mapping
    |--------------------------------------------------------------------------
    |
    | Maps Google Sheet columns to application fields.
    | Update these if the sheet structure changes.
    |
    */
    'columns' => [
        'date' => 0,           // Column A - DATE
        'order_number' => 1,   // Column B - ORDER NUMBER
        'sale' => 2,           // Column C - SALE
        'drivers_cost' => 3,   // Column D - DRIVERS COST
        'user' => 4,           // Column E - USER
        'new_existing' => 5,   // Column F - NEW/EXISTING
        'source' => 6,         // Column G - SOURCE
        'insurance' => 7,      // Column H - INSURANCE ADDED
        'drivers_saved' => 8,  // Column I - DRIVERS COST SAVED
        'csd_id' => 9,         // Column J - CSD ID
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Excluded User IDs
    |--------------------------------------------------------------------------
    |
    | User IDs to exclude from the Off Target card calculation.
    | Comma-separated list of user IDs (e.g., "24,10")
    |
    */
    'excluded_user_ids' => array_filter(array_map('intval', explode(',', env('DASHBOARD_EXCLUDED_USER_IDS', '')))),

    /*
    |--------------------------------------------------------------------------
    | Staff Dashboard - Mask Numbers
    |--------------------------------------------------------------------------
    |
    | If true, numbers on staff dashboard will be masked with asterisks (*).
    | Hovering over the card will reveal the actual numbers.
    |
    */
    'staff_dashboard_mask_numbers' => env('STAFF_DASHBOARD_MASK_NUMBERS', false),
];
