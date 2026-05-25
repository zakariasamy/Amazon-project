<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ReverseAsinController extends Controller
{
    /**
     * Get keywords that an ASIN ranks for
     */
    public function getKeywords(string $asin, Request $request)
    {
        $marketplace = $request->get('marketplace', 'amazon.com');

        // Get stored keywords for this ASIN
        $keywords = DB::table('asin_keywords')
            ->where('asin', $asin)
            ->where('marketplace', $marketplace)
            ->orderBy('position')
            ->orderByDesc('last_seen_at')
            ->limit(100)
            ->get();

        // Get product info if available
        $product = DB::table('product_cache')
            ->where('asin', $asin)
            ->where('marketplace', $marketplace)
            ->first(['title', 'category', 'bsr']);

        return response()->json([
            'success' => true,
            'asin' => $asin,
            'marketplace' => $marketplace,
            'product' => $product,
            'keywords' => $keywords,
            'keyword_count' => $keywords->count()
        ]);
    }

    /**
     * Submit keyword ranking data (when ASIN is found in search results)
     */
    public function submitRanking(Request $request)
    {
        $validated = $request->validate([
            'asin' => 'required|string|max:20',
            'marketplace' => 'required|string|max:30',
            'keyword' => 'required|string|max:255',
            'position' => 'required|integer|min:1|max:100',
            'is_sponsored' => 'boolean',
            'page' => 'integer|min:1|max:10'
        ]);

        $now = now();

        // Upsert the keyword ranking
        DB::table('asin_keywords')->updateOrInsert(
            [
                'asin' => $validated['asin'],
                'marketplace' => $validated['marketplace'],
                'keyword' => strtolower(trim($validated['keyword']))
            ],
            [
                'position' => $validated['position'],
                'is_sponsored' => $validated['is_sponsored'] ?? false,
                'page' => $validated['page'] ?? 1,
                'times_seen' => DB::raw('COALESCE(times_seen, 0) + 1'),
                'last_seen_at' => $now,
                'updated_at' => $now
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Keyword ranking submitted'
        ]);
    }

    /**
     * Suggest keywords for an ASIN based on title
     * Can accept title as query param if product not in cache
     */
    public function suggestKeywords(string $asin, Request $request)
    {
        $marketplace = $request->get('marketplace', 'amazon.com');
        $titleFromRequest = $request->get('title'); // Allow passing title directly

        // Try to get product from cache first
        $product = DB::table('product_cache')
            ->where('asin', $asin)
            ->where('marketplace', $marketplace)
            ->first(['title', 'category']);

        $title = $titleFromRequest ?? ($product->title ?? null);
        $category = $product->category ?? null;

        // If no title from cache or request, return empty but success
        if (!$title) {
            return response()->json([
                'success' => true,
                'asin' => $asin,
                'message' => 'No title available. Pass ?title=ProductTitle to extract keywords.',
                'title_keywords' => [],
                'related_keywords' => [],
                'total_suggestions' => 0
            ]);
        }

        // Extract keywords from title
        $titleKeywords = $this->extractKeywordsFromTitle($title);

        // Get related keywords from cache (based on category)
        $relatedKeywords = [];
        if ($category) {
            $relatedKeywords = DB::table('keyword_cache')
                ->where('marketplace', $marketplace)
                ->where('category', 'like', "%{$category}%")
                ->orderByDesc('search_count')
                ->limit(20)
                ->pluck('keyword')
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'asin' => $asin,
            'title_keywords' => $titleKeywords,
            'related_keywords' => $relatedKeywords,
            'total_suggestions' => count($titleKeywords) + count($relatedKeywords)
        ]);
    }

    /**
     * Extract keywords from product title
     */
    private function extractKeywordsFromTitle(string $title): array
    {
        // Clean the title
        $cleaned = strtolower(preg_replace('/[^\w\s\-]/', ' ', $title));
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));

        // Common stop words to filter out
        $stopWords = ['for', 'with', 'and', 'the', 'by', 'in', 'of', 'to', 'a', 'an', 'is', 'it', 'on', 'as', 'at'];
        
        $words = array_filter(
            explode(' ', $cleaned),
            fn($w) => strlen($w) > 2 && !in_array($w, $stopWords)
        );
        $words = array_values($words);

        $keywords = [];

        // Single significant words
        foreach (array_slice($words, 0, 5) as $word) {
            if (strlen($word) > 3) {
                $keywords[] = $word;
            }
        }

        // 2-word combinations
        for ($i = 0; $i < count($words) - 1 && count($keywords) < 15; $i++) {
            $keywords[] = $words[$i] . ' ' . $words[$i + 1];
        }

        // 3-word combinations
        for ($i = 0; $i < count($words) - 2 && count($keywords) < 20; $i++) {
            $keywords[] = $words[$i] . ' ' . $words[$i + 1] . ' ' . $words[$i + 2];
        }

        return array_unique($keywords);
    }

    /**
     * Save complete reverse ASIN analysis results
     */
    public function saveResults(Request $request)
    {
        $validated = $request->validate([
            'asin' => 'required|string|max:20',
            'marketplace' => 'required|string|max:30',
            'title' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:200',
            'keywords_tested' => 'required|integer|min:0',
            'keywords_found' => 'required|integer|min:0',
            'keywords' => 'present|array',  // Allow empty arrays
            'source' => 'nullable|string|max:50'
        ]);

        $resultId = DB::table('reverse_asin_results')->insertGetId([
            'asin' => $validated['asin'],
            'marketplace' => $validated['marketplace'],
            'title' => $validated['title'] ?? null,
            'category' => $validated['category'] ?? null,
            'keywords_tested' => $validated['keywords_tested'],
            'keywords_found' => $validated['keywords_found'],
            'keywords_data' => json_encode($validated['keywords']),
            'source' => $validated['source'] ?? 'carousel_analysis',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reverse ASIN results saved',
            'result_id' => $resultId
        ]);
    }

    /**
     * Get analysis history for an ASIN
     */
    public function getHistory(string $asin, Request $request)
    {
        $marketplace = $request->get('marketplace', 'amazon.com');
        $limit = min($request->get('limit', 10), 50);

        $history = DB::table('reverse_asin_results')
            ->where('asin', $asin)
            ->where('marketplace', $marketplace)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        // Decode keywords_data JSON for each result
        $history = $history->map(function ($item) {
            $item->keywords = json_decode($item->keywords_data, true);
            unset($item->keywords_data);
            return $item;
        });

        return response()->json([
            'success' => true,
            'asin' => $asin,
            'marketplace' => $marketplace,
            'history' => $history,
            'total' => $history->count()
        ]);
    }

    /**
     * Analyze keywords with search volume estimation
     * This enriches keyword data with volume and sales from search_analyses history
     */
    public function analyzeKeywords(Request $request)
    {
        $validated = $request->validate([
            'asin' => 'required|string|max:20',
            'marketplace' => 'required|string|max:30',
            'keywords' => 'required|array|min:1|max:100',
            'keywords.*.keyword' => 'required|string|max:255',
            'keywords.*.position' => 'nullable|integer|min:1',
            'keywords.*.found' => 'required|boolean'
        ]);

        $marketplace = $validated['marketplace'];
        $keywords = $validated['keywords'];
        $analyzedKeywords = [];

        foreach ($keywords as $kw) {
            $keyword = $kw['keyword'];
            
            // Try to get REAL search volume data from search_analyses table
            $searchData = DB::table('search_analyses')
                ->where('keyword', $keyword)
                ->where('marketplace', $marketplace)
                ->orderByDesc('created_at')
                ->first();
            
            // Also check cache
            $cachedData = Cache::get("keyword_volume:{$marketplace}:{$keyword}");
            
            // Get values from real data or cache
            $estimatedVolume = null;
            $totalSales = null;
            $demandLevel = 'unknown';
            
            if ($searchData) {
                // Real data from search_analyses table
                $volumeData = json_decode($searchData->volume_data, true);
                $estimatedVolume = $volumeData['estimated'] ?? $searchData->estimated_volume ?? null;
                $totalSales = $volumeData['sales_metrics']['total_monthly_sales'] ?? null;
                $demandLevel = $searchData->demand_level ?? $volumeData['demand_level'] ?? 'unknown';
            } elseif ($cachedData) {
                // From cache
                $estimatedVolume = $cachedData['estimated_volume'] ?? $cachedData['estimated'] ?? null;
                $totalSales = $cachedData['sales_metrics']['total_monthly_sales'] ?? null;
                $demandLevel = $cachedData['demand_level'] ?? 'unknown';
            }
            
            $analyzedKeywords[] = [
                'keyword' => $keyword,
                'position' => $kw['position'] ?? null,
                'found' => $kw['found'],
                'estimated_volume' => $estimatedVolume,
                'total_sales' => $totalSales,
                'demand_level' => $demandLevel
            ];
        }

        // Sort: found keywords first (by position), then not found
        usort($analyzedKeywords, function($a, $b) {
            if ($a['found'] && !$b['found']) return -1;
            if (!$a['found'] && $b['found']) return 1;
            if ($a['found'] && $b['found']) return ($a['position'] ?? 999) - ($b['position'] ?? 999);
            return 0;
        });

        return response()->json([
            'success' => true,
            'asin' => $validated['asin'],
            'marketplace' => $marketplace,
            'keywords' => $analyzedKeywords,
            'summary' => [
                'total_keywords' => count($analyzedKeywords),
                'keywords_found' => count(array_filter($analyzedKeywords, fn($k) => $k['found'])),
                'keywords_not_found' => count(array_filter($analyzedKeywords, fn($k) => !$k['found']))
            ],
            'analyzed_at' => now()->toISOString()
        ]);
    }
}
