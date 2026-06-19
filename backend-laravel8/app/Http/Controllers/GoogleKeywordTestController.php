<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GoogleKeywordTestController extends Controller
{
    /**
     * Display the Keyword Planner Test Tool dashboard.
     */
    public function index(Request $request)
    {
        // 1. Check if tool is active in admin settings
        $enabled = true;
        try {
            $enabledVal = DB::table('app_settings')
                ->where('key', 'feature_google_keyword_planner_enabled')
                ->value('value');
            if ($enabledVal !== null) {
                $enabled = filter_var($enabledVal, FILTER_VALIDATE_BOOLEAN);
            }
        } catch (\Exception $e) {
            Log::warning('DB connection failed in index(): ' . $e->getMessage());
        }

        if (!$enabled) {
            abort(403, 'The Google Keyword Planner testing tool is currently disabled by the Admin.');
        }

        // 2. Load query parameters (pre-filled by Chrome extension)
        $asin = $request->query('asin');
        $bsr = $request->query('bsr');
        $sales = $request->query('sales');
        $category = $request->query('category', 'default');
        $marketplace = $request->query('marketplace', 'amazon.com');
        $keyword = $request->query('keyword');

        return view('admin.google_keyword_test', compact(
            'asin', 'bsr', 'sales', 'category', 'marketplace', 'keyword'
        ));
    }

    /**
     * Run simulation and calculate expected before/after metrics and projections.
     */
    public function simulate(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|max:255',
            'sales' => 'nullable|integer|min:0',
            'bsr' => 'nullable|integer|min:0',
            'category' => 'required|string',
            'marketplace' => 'required|string',
        ]);

        $keyword = trim($request->input('keyword'));
        $category = $request->input('category');
        $rawMarketplace = $request->input('marketplace');
        $marketplace = $this->normalizeMarketplace($rawMarketplace);

        $sales = $request->has('sales') && $request->input('sales') !== null && $request->input('sales') !== '' ? (int) $request->input('sales') : 0;
        $bsr = $request->has('bsr') && $request->input('bsr') !== null && $request->input('bsr') !== '' ? (int) $request->input('bsr') : 0;

        // Perform BSR check: if sales is not available/provided (<= 0) and BSR is > 0, estimate it
        $isSalesEstimated = false;
        if ($sales <= 0 && $bsr > 0) {
            $sales = $this->estimateSalesFromBSR($marketplace, $bsr, $category);
            $isSalesEstimated = true;
        }

        // 1. We use a standard baseline CVR (10%) for normalization
        $cvr = 0.10;

        // 2. "Before" calculation (Standard Amazon Programmatic formula: Sales / CVR / 0.95)
        $beforeAmazonVolume = (int) round(($sales / $cvr) / 0.95);

        // 3. Fetch Google API keys from settings
        $devToken = '';
        $clientId = '';
        $clientSecret = '';
        $refreshToken = '';
        $customerId = '';

        try {
            $settings = DB::table('app_settings')
                ->whereIn('key', [
                    'google_ads_developer_token',
                    'google_ads_client_id',
                    'google_ads_client_secret',
                    'google_ads_refresh_token',
                    'google_ads_customer_id'
                ])
                ->pluck('value', 'key');

            $devToken = $settings->get('google_ads_developer_token') ?? '';
            $clientId = $settings->get('google_ads_client_id') ?? '';
            $clientSecret = $settings->get('google_ads_client_secret') ?? '';
            $refreshToken = $settings->get('google_ads_refresh_token') ?? '';
            $customerId = str_replace('-', '', $settings->get('google_ads_customer_id') ?? '');
        } catch (\Exception $e) {
            Log::warning('Failed to load Google Ads settings from database: ' . $e->getMessage());
        }

        // Fetch related Google Keywords & search volumes from Google Ads API
        $suggestions = [];
        $mode = 'real';

        if (!$devToken || !$clientId || !$clientSecret || !$refreshToken || !$customerId) {
            return response()->json([
                'success' => false,
                'error' => 'Google Ads API credentials are not configured in settings.'
            ], 422);
        }

        try {
            $suggestions = $this->fetchRealGoogleKeywords($keyword, $devToken, $clientId, $clientSecret, $refreshToken, $customerId);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Google Ads API Error: ' . $e->getMessage()
            ], 422);
        }

        // 4. Calculate "After" (Blended Intent-Weighted Amazon Volume)
        $keywordResults = [];
        $headTermGoogleVol = 1000;
        
        // Identify head term volume from suggestions for relative scaling
        foreach ($suggestions as $s) {
            if (strtolower($s['text']) === strtolower($keyword)) {
                $headTermGoogleVol = $s['google_volume'];
                break;
            }
        }
        if ($headTermGoogleVol <= 0) {
            $headTermGoogleVol = $suggestions[0]['google_volume'] ?? 1000;
        }

        foreach ($suggestions as $s) {
            $phrase = $s['text'];
            $googleVol = $s['google_volume'];
            $trends = $s['trends'];

            // Calculate Amazon Intent Ratio (AIR)
            // AIR represents the buy-intent density relative to google search interest.
            // Dynamic formula using a standard 10% conversion rate normalization: Sales / (Google volume * 0.10)
            $dynamicAir = ($googleVol > 0) ? ($sales / ($googleVol * $cvr)) : 0.15;
            // Apply limits as caps
            $air = min(max($dynamicAir, 0.05), 1.0); // Capped between 5% and 100%

            // Distribute shared sales relative to Google search ratio to fix long-tail inflation
            $ratio = $googleVol / $headTermGoogleVol;
            $adjustedAmazonVolume = (int) round($googleVol * $air);
            
            // Dampen long-tail search volume relative to the head query
            if (str_word_count($phrase) >= 3) {
                $dampeningFactor = match(str_word_count($phrase)) {
                    3 => 0.75,
                    4 => 0.50,
                    default => 0.30
                };
                $adjustedAmazonVolume = (int) round($adjustedAmazonVolume * $dampeningFactor);
            }

            // Estimate CPC/PPC bid based on intent ratio
            $cpcEstimate = round(0.50 + ($air * 2.50), 2);

            $keywordResults[] = [
                'keyword' => $phrase,
                'google_volume' => $googleVol,
                'amazon_intent_ratio' => round($air * 100, 1) . '%',
                'before_volume' => $beforeAmazonVolume, // old method gave same volume to overlap products
                'after_volume' => $adjustedAmazonVolume,
                'suggested_bid' => $cpcEstimate,
                'trends' => $trends
            ];
        }

        // 5. Generate 12-Month Sales Projections
        $projections = $this->calculateProjections($sales, $keywordResults[0]['trends'] ?? []);

        return response()->json([
            'success' => true,
            'mode' => $mode,
            'cvr' => ($cvr * 100) . '%',
            'before_amazon_volume' => $beforeAmazonVolume,
            'keywords' => $keywordResults,
            'projections' => $projections,
            'is_sales_estimated' => $isSalesEstimated,
            'estimated_sales' => $sales
        ]);
    }

    /**
     * Get CVR based on category and marketplace.
     */
    private function getCVR($category, $marketplace): float
    {
        $isEg = strpos(strtolower($marketplace), '.eg') !== false;
        $cat = strtolower(trim($category));

        if (strpos($cat, 'elect') !== false) {
            return $isEg ? 0.06 : 0.065;
        }
        if (strpos($cat, 'home') !== false || strpos($cat, 'kitchen') !== false) {
            return $isEg ? 0.10 : 0.12;
        }
        if (strpos($cat, 'fash') !== false || strpos($cat, 'cloth') !== false) {
            return $isEg ? 0.08 : 0.10;
        }
        if (strpos($cat, 'groc') !== false || strpos($cat, 'food') !== false) {
            return $isEg ? 0.18 : 0.25;
        }
        return $isEg ? 0.10 : 0.11;
    }



    /**
     * Calculate 12-Month sales projections using Google volume indexes.
     */
    private function calculateProjections(int $currentSales, array $trends): array
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $currentMonthIdx = (int) date('n') - 1; // 0-11

        // Default trend seasonality indices if google doesn't provide
        $indices = [1.2, 1.0, 0.9, 0.8, 0.85, 0.95, 1.1, 1.0, 1.15, 1.0, 0.9, 1.3];
        
        // If google trends exist, convert volumes to indices
        if (!empty($trends)) {
            $sum = array_sum(array_column($trends, 'volume'));
            if ($sum > 0) {
                foreach ($trends as $idx => $t) {
                    $indices[$idx] = ($t['volume'] / $sum) * 12.0;
                }
            }
        }

        $currentMonthIndexValue = $indices[$currentMonthIdx] ?? 1.0;
        if ($currentMonthIndexValue <= 0) $currentMonthIndexValue = 1.0;

        $projected = [];
        foreach ($months as $i => $name) {
            $factor = $indices[$i] ?? 1.0;
            // Expected sales = Current Sales * (Target Month index / Current Month index)
            $expectedSales = (int) round($currentSales * ($factor / $currentMonthIndexValue));
            
            $projected[] = [
                'month' => $name,
                'index_factor' => round($factor, 2),
                'expected_sales' => max($expectedSales, 0),
                'google_search_index' => (int) round($factor * 100)
            ];
        }

        return $projected;
    }


    /**
     * Uses OAuth credentials to query Google Ads API GenerateKeywordIdeas endpoint.
     */
    private function fetchRealGoogleKeywords(string $keyword, string $devToken, string $clientId, string $clientSecret, string $refreshToken, string $customerId): array
    {
        // 1. Get Access Token via OAuth Refresh Token grant
        $authResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => trim($clientId),
            'client_secret' => trim($clientSecret),
            'refresh_token' => trim($refreshToken),
            'grant_type' => 'refresh_token',
        ]);

        if (!$authResponse->successful()) {
            $errorDesc = $authResponse->json('error_description') ?? $authResponse->json('error') ?? $authResponse->body();
            throw new \Exception('OAuth Token Failure (' . $errorDesc . '). Check your credentials in settings.');
        }

        $accessToken = $authResponse->json('access_token');
        if (!$accessToken) {
            throw new \Exception('OAuth token response is missing access_token.');
        }

        // 2. Invoke Google Ads REST API to generate keyword ideas
        // endpoint format: https://googleads.googleapis.com/v15/customers/{customerId}:generateKeywordIdeas
        $apiVersion = 'v15';
        $endpoint = "https://googleads.googleapis.com/{$apiVersion}/customers/{$customerId}:generateKeywordIdeas";

        $payload = [
            'keywordSeed' => [
                'keywords' => [$keyword]
            ],
            // Request English language (1000) and US location (2840) as general baseline reference
            'language' => 'languageConstants/1000',
            'geoModifiers' => ['geoTargetConstants/2840'],
            'keywordPlanNetwork' => 'GOOGLE_SEARCH',
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'developer-token' => $devToken,
            'Content-Type' => 'application/json',
        ])->post($endpoint, $payload);

        if (!$response->successful()) {
            Log::error('Google Ads API generateKeywordIdeas failure', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('generateKeywordIdeas request failed with status ' . $response->status());
        }

        $results = $response->json('results');
        if (empty($results)) {
            throw new \Exception('No keyword suggestions found for "' . $keyword . '" in Google Ads.');
        }

        $suggestions = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Limit to top 8 suggestions
        $count = 0;
        foreach ($results as $item) {
            if ($count >= 8) break;
            $text = $item['text'] ?? null;
            if (!$text) continue;

            $metrics = $item['keywordIdeaMetrics'] ?? [];
            $avgSearchVolume = (int) ($metrics['avgMonthlySearches'] ?? 1000);
            
            // Build trend points from Google Ads API volume trends
            $monthlyMetrics = $metrics['monthlySearchVolumes'] ?? [];
            $trends = [];
            
            if (!empty($monthlyMetrics)) {
                // Map Google's 12-month array
                foreach ($monthlyMetrics as $mIdx => $mVol) {
                    $monthLabel = $months[$mIdx] ?? 'Month';
                    $trends[] = [
                        'month' => $monthLabel,
                        'volume' => (int) ($mVol['monthlySearches'] ?? $avgSearchVolume)
                    ];
                }
            } else {
                // Generate a baseline index if monthly trends are absent
                $indices = [1.2, 1.0, 0.9, 0.8, 0.85, 0.95, 1.1, 1.0, 1.15, 1.0, 0.9, 1.3];
                foreach ($months as $mIdx => $mName) {
                    $trends[] = [
                        'month' => $mName,
                        'volume' => (int) round($avgSearchVolume * $indices[$mIdx])
                    ];
                }
            }

            $suggestions[] = [
                'text' => $text,
                'google_volume' => $avgSearchVolume,
                'trends' => $trends
            ];
            $count++;
        }

        return $suggestions;
    }

    /**
     * Normalize marketplace identifiers coming from browser hostnames.
     */
    private function normalizeMarketplace(string $marketplace): string
    {
        $m = strtolower(trim($marketplace));
        $m = preg_replace('#^https?://#', '', $m);
        $m = explode('/', $m)[0] ?? $m;
        $m = preg_replace('#^(www\.|smile\.|m\.)#', '', $m);

        $known = ['amazon.co.uk', 'amazon.com', 'amazon.eg', 'amazon.de', 'amazon.sa', 'amazon.ae'];
        foreach ($known as $k) {
            if (Str::endsWith($m, $k)) {
                return $k;
            }
        }

        return $m;
    }

    /**
     * Normalize category string to match database structure.
     */
    private function normalizeCategory(string $category): string
    {
        $cat = strtolower(trim($category));
        if (strpos($cat, 'elect') !== false) {
            return 'electronics';
        }
        if (strpos($cat, 'home') !== false || strpos($cat, 'kitc') !== false) {
            return 'home_and_kitchen';
        }
        if (strpos($cat, 'health') !== false || strpos($cat, 'beauty') !== false || strpos($cat, 'cosm') !== false) {
            return 'health_and_beauty';
        }
        return 'default';
    }

    /**
     * Get algorithm constants.
     */
    private function getConstants(string $marketplace, string $category): array
    {
        $normalizedCategory = $this->normalizeCategory($category);
        $constant = null;

        try {
            $constant = Cache::remember("const_{$marketplace}_{$normalizedCategory}", 3600, function () use ($marketplace, $normalizedCategory) {
                return DB::table('algorithm_constants')
                    ->where('marketplace', $marketplace)
                    ->where('category', $normalizedCategory)
                    ->where('is_active', true)
                    ->first();
            });

            if (!$constant) {
                $constant = DB::table('algorithm_constants')
                    ->where('marketplace', $marketplace)
                    ->where('category', 'default')
                    ->where('is_active', true)
                    ->first();
            }
        } catch (\Exception $e) {
            Log::warning('DB query failed in getConstants: ' . $e->getMessage());
        }

        if (!$constant) {
            // Hardcoded fallback
            if ($marketplace === 'amazon.eg') {
                return [
                    'C' => 1100,
                    'P' => 0.68,
                    'floor' => 2,
                    'ceiling' => 8000,
                ];
            } elseif ($marketplace === 'amazon.sa') {
                return [
                    'C' => 9000,
                    'P' => 0.57,
                    'floor' => 3,
                    'ceiling' => 24000,
                ];
            } elseif ($marketplace === 'amazon.ae') {
                return [
                    'C' => 7500,
                    'P' => 0.57,
                    'floor' => 3,
                    'ceiling' => 20000,
                ];
            }
            
            return [
                'C' => 50000,
                'P' => 0.68,
                'floor' => 5,
                'ceiling' => 120000,
            ];
        }

        return [
            'C' => $constant->C,
            'P' => $constant->P,
            'floor' => $constant->floor,
            'ceiling' => $constant->ceiling,
        ];
    }

    /**
     * Estimate monthly sales from Amazon BSR.
     */
    private function estimateSalesFromBSR(string $marketplace, int $bsr, string $category): int
    {
        if ($bsr <= 0) {
            return 0;
        }

        $constants = $this->getConstants($marketplace, $category);

        $sales = $constants['C'] / pow($bsr, $constants['P']);
        $sales = max(min(round($sales), $constants['ceiling']), $constants['floor']);

        // Cap at 49: If Amazon didn't show a sales badge, it means sales < 50
        // So even if BSR formula estimates higher, we know it's under 50
        $sales = min($sales, 49);

        return (int) $sales;
    }
}
