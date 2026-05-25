<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MagnetController extends Controller
{
    /**
     * Save completed Magnet analysis
     * POST /api/magnet/analyze
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'marketplace' => 'required|string|max:30',
            'seed_keyword' => 'required|string|max:500',
            'name' => 'nullable|string|max:255',
            'duration_seconds' => 'nullable|integer',
            'keywords' => 'required|array|min:1',
            'keywords.*.keyword' => 'required|string|max:500',
            'keywords.*.search_volume' => 'nullable|integer|min:0',
            'keywords.*.magnet_iq_score' => 'nullable|numeric|min:0',
            'keywords.*.competing_products' => 'nullable|integer|min:0',
            'keywords.*.title_density' => 'nullable|integer|min:0',
            'keywords.*.word_count' => 'nullable|integer|min:1',
            'keywords.*.cpr_8day' => 'nullable|integer|min:0',
            'keywords.*.cpr_total' => 'nullable|integer|min:0',
            'keywords.*.keyword_sales' => 'nullable|integer|min:0',
            'keywords.*.avg_price' => 'nullable|numeric|min:0',
            'keywords.*.avg_reviews' => 'nullable|integer|min:0',
            'keywords.*.sponsored_count' => 'nullable|integer|min:0',
            'keywords.*.match_type' => 'nullable|in:autocomplete,related,title,suggestion,seed',
            'keywords.*.relevance_score' => 'nullable|integer|min:0|max:100',
        ]);

        $userId = Auth::id();

        // Create analysis record
        $analysisId = DB::table('magnet_analyses')->insertGetId([
            'user_id' => $userId,
            'name' => $validated['name'] ?? 'Magnet: ' . $validated['seed_keyword'],
            'marketplace' => $validated['marketplace'],
            'seed_keyword' => $validated['seed_keyword'],
            'status' => 'completed',
            'total_keywords' => count($validated['keywords']),
            'progress_percent' => 100,
            'duration_seconds' => $validated['duration_seconds'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
            'completed_at' => now(),
        ]);

        // Insert keywords in batches
        $keywordRecords = [];
        foreach ($validated['keywords'] as $kw) {
            $keywordRecords[] = [
                'analysis_id' => $analysisId,
                'keyword' => $kw['keyword'],
                'word_count' => $kw['word_count'] ?? str_word_count($kw['keyword']),
                'search_volume' => $kw['search_volume'] ?? 0,
                'magnet_iq_score' => $kw['magnet_iq_score'] ?? 0,
                'competing_products' => $kw['competing_products'] ?? 0,
                'title_density' => $kw['title_density'] ?? 0,
                'cpr_8day' => $kw['cpr_8day'] ?? 0,
                'cpr_total' => $kw['cpr_total'] ?? 0,
                'keyword_sales' => $kw['keyword_sales'] ?? 0,
                'avg_price' => $kw['avg_price'] ?? 0,
                'avg_reviews' => $kw['avg_reviews'] ?? 0,
                'sponsored_count' => $kw['sponsored_count'] ?? 0,
                'match_type' => $kw['match_type'] ?? 'autocomplete',
                'relevance_score' => $kw['relevance_score'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in batches of 100
            if (count($keywordRecords) >= 100) {
                DB::table('magnet_keywords')->insert($keywordRecords);
                $keywordRecords = [];
            }
        }

        // Insert remaining
        if (!empty($keywordRecords)) {
            DB::table('magnet_keywords')->insert($keywordRecords);
        }

        return response()->json([
            'success' => true,
            'analysis_id' => $analysisId,
            'message' => 'Magnet analysis saved successfully',
            'total_keywords' => count($validated['keywords']),
        ]);
    }

    /**
     * Get analysis history for current user
     * GET /api/magnet/history
     */
    public function history(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $perPage = $request->input('per_page', 20);

        $analyses = DB::table('magnet_analyses')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($analyses);
    }

    /**
     * Get single analysis with keywords
     * GET /api/magnet/{id}
     */
    public function show(Request $request, $id)
    {
        $userId = Auth::id() ?? 1;

        $analysis = DB::table('magnet_analyses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$analysis) {
            return response()->json(['error' => 'Analysis not found'], 404);
        }

        // Build keywords query with filters
        $query = DB::table('magnet_keywords')
            ->where('analysis_id', $id);

        // Apply filters
        $query = $this->applyFilters($query, $request);

        // Sorting
        $sortBy = $request->input('sort', 'magnet_iq_score');
        $sortDir = $request->input('dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = $request->input('per_page', 50);
        $keywords = $query->paginate($perPage);

        return response()->json([
            'analysis' => $analysis,
            'keywords' => $keywords,
        ]);
    }

    /**
     * Delete analysis
     * DELETE /api/magnet/{id}
     */
    public function destroy($id)
    {
        $userId = Auth::id() ?? 1;

        $deleted = DB::table('magnet_analyses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Analysis not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Analysis deleted']);
    }

    /**
     * Export analysis to CSV
     * GET /api/magnet/{id}/export
     */
    public function export(Request $request, $id)
    {
        $userId = Auth::id() ?? 1;

        $analysis = DB::table('magnet_analyses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$analysis) {
            return response()->json(['error' => 'Analysis not found'], 404);
        }

        $keywords = DB::table('magnet_keywords')
            ->where('analysis_id', $id)
            ->orderBy('magnet_iq_score', 'desc')
            ->get();

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
            'Avg Price',
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

        return response($csvContent, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="magnet_analysis_' . $id . '.csv"');
    }

    /**
     * Get supported marketplaces with Egypt as default
     * GET /api/magnet/marketplaces
     */
    public function marketplaces()
    {
        return response()->json([
            'default' => 'amazon.eg',
            'marketplaces' => [
                ['code' => 'amazon.eg', 'name' => 'Amazon Egypt', 'currency' => 'EGP', 'flag' => '🇪🇬'],
                ['code' => 'amazon.com', 'name' => 'Amazon US', 'currency' => 'USD', 'flag' => '🇺🇸'],
                ['code' => 'amazon.co.uk', 'name' => 'Amazon UK', 'currency' => 'GBP', 'flag' => '🇬🇧'],
                ['code' => 'amazon.de', 'name' => 'Amazon Germany', 'currency' => 'EUR', 'flag' => '🇩🇪'],
                ['code' => 'amazon.ae', 'name' => 'Amazon UAE', 'currency' => 'AED', 'flag' => '🇦🇪'],
                ['code' => 'amazon.sa', 'name' => 'Amazon Saudi Arabia', 'currency' => 'SAR', 'flag' => '🇸🇦'],
            ],
        ]);
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
}
