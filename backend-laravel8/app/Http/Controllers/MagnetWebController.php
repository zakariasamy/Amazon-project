<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MagnetWebController extends Controller
{
    /**
     * Display list of all Magnet analyses for current user
     */
    public function index()
    {
        $userId = Auth::id();
        
        $analyses = DB::table('magnet_analyses')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get marketplace info
        $marketplaces = $this->getMarketplaces();

        return view('magnet.index', [
            'analyses' => $analyses,
            'marketplaces' => $marketplaces,
        ]);
    }

    /**
     * Display a specific analysis with keywords
     */
    public function show(Request $request, $id)
    {
        $userId = Auth::id();

        $analysis = DB::table('magnet_analyses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$analysis) {
            abort(404, 'Analysis not found');
        }

        // Build keywords query
        $query = DB::table('magnet_keywords')
            ->where('analysis_id', $id);

        // Apply filters
        $query = $this->applyFilters($query, $request);

        // Sorting
        $sortBy = $request->input('sort', 'magnet_iq_score');
        $sortDir = $request->input('dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Get keywords with pagination
        $keywords = $query->paginate(50)->withQueryString();

        // Calculate summary stats
        $stats = DB::table('magnet_keywords')
            ->where('analysis_id', $id)
            ->selectRaw('
                COUNT(*) as total_keywords,
                AVG(search_volume) as avg_volume,
                MAX(magnet_iq_score) as top_iq_score,
                SUM(keyword_sales) as total_sales,
                AVG(avg_price) as avg_price
            ')
            ->first();

        // Get marketplace info
        $marketplaces = $this->getMarketplaces();
        $currentMarketplace = collect($marketplaces)->firstWhere('code', $analysis->marketplace);

        return view('magnet.show', [
            'analysis' => $analysis,
            'keywords' => $keywords,
            'stats' => $stats,
            'marketplace' => $currentMarketplace,
            'filters' => $request->only([
                'volume_min', 'volume_max', 'iq_min', 'iq_max',
                'words_min', 'words_max', 'title_density_max',
                'competing_max', 'match_type', 'include_phrase',
                'exclude_phrase', 'quick_filter', 'sort', 'dir'
            ]),
        ]);
    }

    /**
     * Export analysis to CSV
     */
    public function export($id)
    {
        $userId = Auth::id();

        $analysis = DB::table('magnet_analyses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$analysis) {
            abort(404, 'Analysis not found');
        }

        $keywords = DB::table('magnet_keywords')
            ->where('analysis_id', $id)
            ->orderBy('magnet_iq_score', 'desc')
            ->get();

        // Get marketplace info for currency
        $marketplaces = $this->getMarketplaces();
        $marketplace = collect($marketplaces)->firstWhere('code', $analysis->marketplace);
        $currency = $marketplace['currency'] ?? 'USD';

        // Build CSV
        $headers = [
            'Keyword',
            'Search Volume',
            'IQ Score',
            'Title Density',
            'Competing Products',
            'Word Count',
            'CPR 8-Day',
            'CPR Total',
            'Keyword Sales',
            "Avg Price ({$currency})",
            'Avg Reviews',
            'Sponsored Count',
            'Match Type',
            'Relevance Score',
        ];

        $rows = [];
        foreach ($keywords as $kw) {
            $rows[] = [
                $kw->keyword,
                $kw->search_volume,
                $kw->magnet_iq_score,
                $kw->title_density,
                $kw->competing_products,
                $kw->word_count,
                $kw->cpr_8day,
                $kw->cpr_total,
                $kw->keyword_sales,
                $kw->avg_price,
                $kw->avg_reviews,
                $kw->sponsored_count,
                $kw->match_type,
                $kw->relevance_score,
            ];
        }

        // Generate CSV content
        $csvContent = implode(',', array_map(fn($h) => "\"$h\"", $headers)) . "\n";
        foreach ($rows as $row) {
            $csvContent .= implode(',', array_map(fn($c) => "\"$c\"", $row)) . "\n";
        }

        $filename = 'magnet_' . str_replace(' ', '_', $analysis->seed_keyword) . '_' . date('Y-m-d') . '.csv';

        return response($csvContent, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Delete an analysis
     */
    public function destroy($id)
    {
        $userId = Auth::id();

        $deleted = DB::table('magnet_analyses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();

        if (!$deleted) {
            return redirect()->route('magnet.index')->with('error', 'Analysis not found');
        }

        return redirect()->route('magnet.index')->with('success', 'Analysis deleted successfully');
    }

    /**
     * Get supported marketplaces with Egypt as default
     */
    private function getMarketplaces()
    {
        return [
            ['code' => 'amazon.eg', 'name' => 'Amazon Egypt', 'currency' => 'EGP', 'flag' => '🇪🇬', 'default' => true],
            ['code' => 'amazon.com', 'name' => 'Amazon US', 'currency' => 'USD', 'flag' => '🇺🇸', 'default' => false],
            ['code' => 'amazon.co.uk', 'name' => 'Amazon UK', 'currency' => 'GBP', 'flag' => '🇬🇧', 'default' => false],
            ['code' => 'amazon.de', 'name' => 'Amazon Germany', 'currency' => 'EUR', 'flag' => '🇩🇪', 'default' => false],
            ['code' => 'amazon.ae', 'name' => 'Amazon UAE', 'currency' => 'AED', 'flag' => '🇦🇪', 'default' => false],
            ['code' => 'amazon.sa', 'name' => 'Amazon Saudi Arabia', 'currency' => 'SAR', 'flag' => '🇸🇦', 'default' => false],
        ];
    }

    /**
     * Apply filters to query (Helium 10 Magnet-style comprehensive filtering)
     */
    private function applyFilters($query, Request $request)
    {
        // Volume filter (min/max)
        if ($request->filled('volume_min')) {
            $query->where('search_volume', '>=', $request->input('volume_min'));
        }
        if ($request->filled('volume_max')) {
            $query->where('search_volume', '<=', $request->input('volume_max'));
        }

        // IQ Score filter (min/max)
        if ($request->filled('iq_min')) {
            $query->where('magnet_iq_score', '>=', $request->input('iq_min'));
        }
        if ($request->filled('iq_max')) {
            $query->where('magnet_iq_score', '<=', $request->input('iq_max'));
        }

        // Word count filter (min/max)
        if ($request->filled('words_min')) {
            $query->where('word_count', '>=', $request->input('words_min'));
        }
        if ($request->filled('words_max')) {
            $query->where('word_count', '<=', $request->input('words_max'));
        }

        // Title density filter (max only - lower is better)
        if ($request->filled('title_density_max')) {
            $query->where('title_density', '<=', $request->input('title_density_max'));
        }

        // Competing products filter (max only - lower is better)
        if ($request->filled('competing_max')) {
            $query->where('competing_products', '<=', $request->input('competing_max'));
        }

        // Keyword sales filter (min - higher is better)
        if ($request->filled('sales_min')) {
            $query->where('keyword_sales', '>=', $request->input('sales_min'));
        }

        // CPR 8-Day filter (max - lower means easier to rank)
        if ($request->filled('cpr_max')) {
            $query->where('cpr_8day', '<=', $request->input('cpr_max'));
        }

        // Average price filter (min/max)
        if ($request->filled('price_min')) {
            $query->where('avg_price', '>=', $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('avg_price', '<=', $request->input('price_max'));
        }

        // Average reviews filter (min/max)
        if ($request->filled('reviews_min')) {
            $query->where('avg_reviews', '>=', $request->input('reviews_min'));
        }
        if ($request->filled('reviews_max')) {
            $query->where('avg_reviews', '<=', $request->input('reviews_max'));
        }

        // Sponsored count filter (max)
        if ($request->filled('sponsored_max')) {
            $query->where('sponsored_count', '<=', $request->input('sponsored_max'));
        }

        // Relevance score filter (min)
        if ($request->filled('relevance_min')) {
            $query->where('relevance_score', '>=', $request->input('relevance_min'));
        }

        // Match type filter
        if ($request->filled('match_type') && $request->input('match_type') !== 'all') {
            $query->where('match_type', $request->input('match_type'));
        }

        // Include phrase (comma-separated, any must match)
        if ($request->filled('include_phrase')) {
            $phrases = explode(',', $request->input('include_phrase'));
            $query->where(function ($q) use ($phrases) {
                foreach ($phrases as $phrase) {
                    $trimmed = trim($phrase);
                    if ($trimmed) {
                        $q->orWhere('keyword', 'like', '%' . $trimmed . '%');
                    }
                }
            });
        }

        // Exclude phrase (comma-separated, none must match)
        if ($request->filled('exclude_phrase')) {
            $phrases = explode(',', $request->input('exclude_phrase'));
            foreach ($phrases as $phrase) {
                $trimmed = trim($phrase);
                if ($trimmed) {
                    $query->where('keyword', 'not like', '%' . $trimmed . '%');
                }
            }
        }

        // Quick filter presets (Helium 10-style smart filters)
        if ($request->filled('quick_filter')) {
            switch ($request->input('quick_filter')) {
                case 'high_volume':
                    // Keywords with significant search volume
                    $query->where('search_volume', '>=', 1000);
                    break;
                    
                case 'opportunity':
                    // High opportunity = good IQ score + low title density
                    $query->where('magnet_iq_score', '>=', 3)
                          ->where('title_density', '<=', 5);
                    break;
                    
                case 'low_competition':
                    // Lower competition with decent volume
                    $query->where('competing_products', '<=', 10000)
                          ->where('search_volume', '>=', 500);
                    break;
                    
                case 'long_tail':
                    // Long-tail keywords (4+ words with some volume)
                    $query->where('word_count', '>=', 4)
                          ->where('search_volume', '>=', 100);
                    break;
                    
                case 'easy_wins':
                    // Easy to rank keywords: low CPR, low competition, decent volume
                    $query->where('cpr_8day', '<=', 10)
                          ->where('competing_products', '<=', 5000)
                          ->where('search_volume', '>=', 300)
                          ->where('title_density', '<=', 10);
                    break;
                    
                case 'high_value':
                    // High-value keywords: good sales, good price point
                    $query->where('keyword_sales', '>=', 50)
                          ->where('avg_price', '>=', 15);
                    break;
                    
                case 'trending':
                    // High relevance (for the seed) + volume
                    $query->where('relevance_score', '>=', 70)
                          ->where('search_volume', '>=', 500);
                    break;
            }
        }

        return $query;
    }

    /**
     * Proxy: Fetch Amazon autocomplete suggestions (bypasses CORS)
     */
    public function proxySuggestions(Request $request)
    {
        $prefix = $request->input('prefix', '');
        $marketplace = $request->input('marketplace', 'eg');
        
        $marketplaceData = [
            'eg' => ['mid' => 'ARBP9OOSHTCHU', 'lop' => 'en_AE'],
            'com' => ['mid' => 'ATVPDKIKX0DER', 'lop' => 'en_US'],
            'co.uk' => ['mid' => 'A1F83G8C2ARO7P', 'lop' => 'en_GB'],
            'de' => ['mid' => 'A1PA6795UKMFR9', 'lop' => 'de_DE'],
            'ae' => ['mid' => 'A2VIGQ35RCS4UG', 'lop' => 'en_AE'],
            'sa' => ['mid' => 'A17E79C6D8DWNP', 'lop' => 'ar_SA'],
        ];

        $mkp = $marketplaceData[$marketplace] ?? $marketplaceData['eg'];
        
        $url = "https://www.amazon.{$marketplace}/suggestions?" . http_build_query([
            'limit' => '11',
            'prefix' => $prefix,
            'suggestion-type' => 'KEYWORD',
            'page-type' => 'Search',
            'alias' => 'aps',
            'site-variant' => 'desktop',
            'version' => '3',
            'event' => 'onfocuswithsearchterm',
            'wc' => '',
            'lop' => $mkp['lop'],
            'fb' => '1',
            'mid' => $mkp['mid'],
            'client-info' => 'search-ui'
        ]);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/json',
            ])->timeout(10)->get($url);

            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['suggestions' => []], 200);
        } catch (\Exception $e) {
            return response()->json(['suggestions' => [], 'error' => $e->getMessage()], 200);
        }
    }

    /**
     * Proxy: Fetch Amazon search page HTML (bypasses CORS)
     */
    public function proxySearchPage(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $marketplace = $request->input('marketplace', 'www.amazon.eg');
        
        $url = "https://{$marketplace}/s?k=" . urlencode($keyword);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(15)->get($url);

            if ($response->successful()) {
                // Extract titles and prices using regex
                $html = $response->body();
                
                // Check for CAPTCHA
                if (strpos($html, 'api-services-support@amazon.com') !== false) {
                    return response()->json(['error' => 'CAPTCHA', 'html' => '']);
                }

                // Extract titles
                $titles = [];
                preg_match_all('/<span[^>]*class="[^"]*a-text-normal[^"]*"[^>]*>(.*?)<\/span>/i', $html, $matchesT);
                if (!empty($matchesT[1])) {
                    foreach ($matchesT[1] as $t) {
                        $title = trim(html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                        if (strlen($title) > 10) {
                            $titles[] = $title;
                        }
                    }
                }

                // Extract prices
                $prices = [];
                preg_match_all('/<span class="a-price-whole">([0-9.,]+)<\/span>/', $html, $matchesP);
                if (!empty($matchesP[1])) {
                    foreach ($matchesP[1] as $p) {
                        $prices[] = (float)str_replace([',', '.'], '', $p);
                    }
                }

                // Extract review counts
                $reviews = [];
                preg_match_all('/<span class="a-size-base s-underline-text">([0-9,]+)<\/span>/', $html, $matchesR);
                if (!empty($matchesR[1])) {
                    foreach ($matchesR[1] as $r) {
                        $reviews[] = (int)str_replace(',', '', $r);
                    }
                }

                return response()->json([
                    'success' => true,
                    'titles' => array_slice(array_unique($titles), 0, 48),
                    'prices' => $prices,
                    'reviews' => $reviews,
                    'total_results' => count($titles)
                ]);
            }

            return response()->json(['error' => 'Failed to fetch', 'titles' => []]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'titles' => []]);
        }
    }

    /**
     * Save analysis results from client-side analyzer
     */
    public function saveAnalysis(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'seed_keyword' => 'required|string',
            'marketplace' => 'required|string',
            'keywords' => 'required|array',
            'duration_seconds' => 'nullable|integer'
        ]);

        try {
            // Create analysis record
            $analysisId = DB::table('magnet_analyses')->insertGetId([
                'user_id' => $userId,
                'name' => 'Magnet: ' . $data['seed_keyword'],
                'marketplace' => $data['marketplace'],
                'seed_keyword' => $data['seed_keyword'],
                'status' => 'completed',
                'total_keywords' => count($data['keywords']),
                'progress_percent' => 100,
                'duration_seconds' => $data['duration_seconds'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
                'completed_at' => now(),
            ]);

            // Save keywords
            $keywordData = [];
            foreach ($data['keywords'] as $kw) {
                $keywordData[] = [
                    'analysis_id' => $analysisId,
                    'keyword' => $kw['keyword'] ?? '',
                    'search_volume' => $kw['search_volume'] ?? 0,
                    'magnet_iq_score' => $kw['magnet_iq_score'] ?? 0,
                    'competing_products' => $kw['competing_products'] ?? 0,
                    'title_density' => $kw['title_density'] ?? 0,
                    'word_count' => $kw['word_count'] ?? str_word_count($kw['keyword'] ?? ''),
                    'cpr_8day' => $kw['cpr_8day'] ?? 0,
                    'cpr_total' => $kw['cpr_total'] ?? 0,
                    'keyword_sales' => $kw['keyword_sales'] ?? 0,
                    'avg_price' => $kw['avg_price'] ?? 0,
                    'avg_reviews' => $kw['avg_reviews'] ?? 0,
                    'sponsored_count' => $kw['sponsored_count'] ?? 0,
                    'match_type' => $kw['match_type'] ?? 'suggestion',
                    'relevance_score' => $kw['relevance_score'] ?? 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($keywordData, 100) as $chunk) {
                DB::table('magnet_keywords')->insert($chunk);
            }

            return response()->json([
                'success' => true,
                'analysis_id' => $analysisId,
                'redirect_url' => route('magnet.show', $analysisId)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
