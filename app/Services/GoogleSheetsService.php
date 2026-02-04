<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class GoogleSheetsService
{
    protected ?Client $client = null;
    protected ?Sheets $sheetsService = null;
    protected string $spreadsheetId;
    protected string $sheetName;
    protected int $cacheTtl;
    protected array $columns;

    public function __construct()
    {
        $this->spreadsheetId = config('google-sheets.sheet_id');
        $this->sheetName = config('google-sheets.sheet_name', 'Sheet1');
        $this->cacheTtl = config('google-sheets.cache_ttl', 300);
        $this->columns = config('google-sheets.columns');
    }

    /**
     * Get the resolved credentials path
     */
    protected function getCredentialsPath(): string
    {
        $path = config('google-sheets.credentials_path');

        // If it's a relative path, resolve it from base_path
        if (!str_starts_with($path, '/') && !preg_match('/^[A-Za-z]:/', $path)) {
            $path = base_path($path);
        }

        return $path;
    }

    /**
     * Initialize Google Client and Sheets Service
     */
    protected function initializeClient(): void
    {
        if ($this->client !== null) {
            return;
        }

        $credentialsPath = $this->getCredentialsPath();

        if (!file_exists($credentialsPath)) {
            throw new Exception("Google credentials file not found at: {$credentialsPath}");
        }

        $this->client = new Client();
        $this->client->setApplicationName('CRM Assistant');
        $this->client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $this->client->setAuthConfig($credentialsPath);

        // Disable SSL verification for local development (Windows/WAMP)
        $httpClient = new \GuzzleHttp\Client([
            'verify' => false,
        ]);
        $this->client->setHttpClient($httpClient);

        $this->sheetsService = new Sheets($this->client);
    }

    /**
     * Get sales data from Google Sheets with caching
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return Collection
     */
    public function getSalesData(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $cacheKey = 'google_sheets_sales_data';

        // Get all data from cache or fetch fresh
        $allData = Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->fetchFromGoogleSheets();
        });

        // Filter by date if provided
        if ($startDate || $endDate) {
            $allData = $allData->filter(function ($row) use ($startDate, $endDate) {
                $rowDate = $row['date'] ?? null;
                if (!$rowDate) {
                    return false;
                }

                if ($startDate && $rowDate->lt($startDate->startOfDay())) {
                    return false;
                }

                if ($endDate && $rowDate->gt($endDate->endOfDay())) {
                    return false;
                }

                return true;
            });
        }

        return $allData->values();
    }

    /**
     * Fetch raw data from Google Sheets
     *
     * @return Collection
     */
    protected function fetchFromGoogleSheets(): Collection
    {
        try {
            $this->initializeClient();

            $range = $this->sheetName . '!A:J';
            $response = $this->sheetsService->spreadsheets_values->get(
                $this->spreadsheetId,
                $range
            );

            $values = $response->getValues();

            if (empty($values)) {
                return collect([]);
            }

            // Skip header row and parse data
            $data = collect(array_slice($values, 1))
                ->map(function ($row) {
                    return $this->parseSheetRow($row);
                })
                ->filter(function ($row) {
                    // Filter out invalid rows (must have date and CSD ID)
                    return $row['date'] !== null && $row['csd_id'] !== null;
                });

            Log::info("Fetched {$data->count()} rows from Google Sheets");

            return $data;

        } catch (Exception $e) {
            Log::error('Google Sheets API Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse a single row from the sheet
     *
     * @param array $row
     * @return array
     */
    protected function parseSheetRow(array $row): array
    {
        $cols = $this->columns;

        // Parse date (DD/MM/YYYY format)
        $dateValue = $row[$cols['date']] ?? null;
        $parsedDate = null;
        if ($dateValue) {
            try {
                $parsedDate = Carbon::createFromFormat('d/m/Y', $dateValue)->startOfDay();
            } catch (Exception $e) {
                // Try alternative format
                try {
                    $parsedDate = Carbon::parse($dateValue)->startOfDay();
                } catch (Exception $e) {
                    $parsedDate = null;
                }
            }
        }

        // Parse sale amount (remove £ symbol and convert to float)
        $saleValue = $row[$cols['sale']] ?? '0';
        $saleAmount = $this->parseCurrency($saleValue);

        // Parse drivers cost
        $driversCost = $this->parseCurrency($row[$cols['drivers_cost']] ?? '0');

        // Parse insurance added
        $insuranceAdded = $this->parseCurrency($row[$cols['insurance']] ?? '0');

        // Parse drivers cost saved
        $driversSaved = $this->parseCurrency($row[$cols['drivers_saved']] ?? '0');

        // Parse CSD ID (user_id)
        $csdId = isset($row[$cols['csd_id']]) ? (int) $row[$cols['csd_id']] : null;

        return [
            'date' => $parsedDate,
            'order_number' => $row[$cols['order_number']] ?? null,
            'sale' => $saleAmount,
            'drivers_cost' => $driversCost,
            'user_name' => $row[$cols['user']] ?? null,
            'business_type' => strtoupper(trim($row[$cols['new_existing']] ?? '')),
            'source' => $row[$cols['source']] ?? null,
            'insurance_added' => $insuranceAdded,
            'drivers_cost_saved' => $driversSaved,
            'csd_id' => $csdId,
        ];
    }

    /**
     * Parse currency string to float
     *
     * @param string $value
     * @return float
     */
    protected function parseCurrency(string $value): float
    {
        // Remove currency symbols and whitespace
        $cleaned = preg_replace('/[£$€,\s]/', '', $value);
        return (float) ($cleaned ?: 0);
    }

    /**
     * Clear the cache to force fresh data fetch
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('google_sheets_sales_data');
        Log::info('Google Sheets cache cleared');
    }

    /**
     * Get the current cache TTL setting
     *
     * @return int
     */
    public function getCacheTtl(): int
    {
        return $this->cacheTtl;
    }

    /**
     * Check if Google Sheets is properly configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        $credentialsPath = $this->getCredentialsPath();
        $sheetId = config('google-sheets.sheet_id');

        return !empty($sheetId) && file_exists($credentialsPath);
    }
}
