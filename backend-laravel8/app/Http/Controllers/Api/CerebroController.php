<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CerebroController extends Controller
{
    /**
     * Save completed Cerebro analysis
     * POST /api/cerebro/analyze
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'marketplace' => 'required|string|max:30',
            'asins' => 'required|array|min:1|max:10',
            'asins.*' => 'required|string|max:20',
            'name' => 'nullable|string|max:255',
            'duration_seconds' => 'nullable|integer',
            'keywords' => 'required|array|min:1',
            'keywords.*.keyword' => 'required|string|max:500',
            'keywords.*.search_volume' => 'nullable|integer|min:0',
            'keywords.*.cerebro_iq_score' => 'nullable|numeric|min:0',
            'keywords.*.competing_products' => 'nullable|integer|min:0',
            'keywords.*.title_density' => 'nullable|integer|min:0',
            'keywords.*.word_count' => 'nullable|integer|min:1',
            'keywords.*.cpr_8day' => 'nullable|integer|min:0',
            'keywords.*.cpr_total' => 'nullable|integer|min:0',
            'keywords.*.keyword_sales' => 'nullable|integer|min:0',
            'keywords.*.organic_ranks' => 'nullable|array',
            'keywords.*.sponsored_ranks' => 'nullable|array',
            'keywords.*.asins_ranking' => 'nullable|integer|min:0',
            'keywords.*.avg_organic_rank' => 'nullable|numeric',
            'keywords.*.min_organic_rank' => 'nullable|integer',
            'keywords.*.max_organic_rank' => 'nullable|integer',
            'keywords.*.match_type' => 'nullable|in:organic,sponsored,both,amazon_rec',
        ]);

        $userId = Auth::id() ?? 1; // Default for testing

        // Create analysis record
        $analysisId = DB::table('cerebro_analyses')->insertGetId([
            'user_id' => $userId,
            'name' => $validated['name'] ?? $this->generateAnalysisName($validated['asins']),
            'marketplace' => $validated['marketplace'],
            'asins' => json_encode($validated['asins']),
            'asin_count' => count($validated['asins']),
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
                'cerebro_iq_score' => $kw['cerebro_iq_score'] ?? 0,
                'competing_products' => $kw['competing_products'] ?? 0,
                'title_density' => $kw['title_density'] ?? 0,
                'cpr_8day' => $kw['cpr_8day'] ?? 0,
                'cpr_total' => $kw['cpr_total'] ?? 0,
                'keyword_sales' => $kw['keyword_sales'] ?? 0,
                'organic_ranks' => json_encode($kw['organic_ranks'] ?? []),
                'sponsored_ranks' => json_encode($kw['sponsored_ranks'] ?? []),
                'asins_ranking' => $kw['asins_ranking'] ?? 0,
                'avg_organic_rank' => $kw['avg_organic_rank'] ?? null,
                'min_organic_rank' => $kw['min_organic_rank'] ?? null,
                'max_organic_rank' => $kw['max_organic_rank'] ?? null,
                'match_type' => $kw['match_type'] ?? 'organic',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in batches of 100
            if (count($keywordRecords) >= 100) {
                DB::table('cerebro_keywords')->insert($keywordRecords);
                $keywordRecords = [];
            }
        }

        // Insert remaining
        if (!empty($keywordRecords)) {
            DB::table('cerebro_keywords')->insert($keywordRecords);
        }

        return response()->json([
            'success' => true,
            'analysis_id' => $analysisId,
            'message' => 'Analysis saved successfully',
            'total_keywords' => count($validated['keywords']),
        ]);
    }

    /**
     * Get analysis history for current user
     * GET /api/cerebro/history
     */
    public function history(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $perPage = $request->input('per_page', 20);

        $analyses = DB::table('cerebro_analyses')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Decode ASINs JSON
        foreach ($analyses as $analysis) {
            $analysis->asins = json_decode($analysis->asins, true);
        }

        return response()->json($analyses);
    }

    /**
     * Get single analysis with keywords
     * GET /api/cerebro/{id}
     */
    public function show(Request $request, $id)
    {
        $userId = Auth::id() ?? 1;

        $analysis = DB::table('cerebro_analyses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$analysis) {
            return response()->json(['error' => 'Analysis not found'], 404);
        }

        $analysis->asins = json_decode($analysis->asins, true);

        // Build keywords query with filters
        $query = DB::table('cerebro_keywords')
            ->where('analysis_id', $id);

        // Apply filters
        $query = $this->applyFilters($query, $request);

        // Sorting
        $sortBy = $request->input('sort', 'cerebro_iq_score');
        $sortDir = $request->input('dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = $request->input('per_page', 50);
        $keywords = $query->paginate($perPage);

        // Decode JSON columns
        foreach ($keywords as $kw) {
            $kw->organic_ranks = json_decode($kw->organic_ranks, true);
            $kw->sponsored_ranks = json_decode($kw->sponsored_ranks, true);
        }

        return response()->json([
            'analysis' => $analysis,
            'keywords' => $keywords,
        ]);
    }

    /**
     * Delete analysis
     * DELETE /api/cerebro/{id}
     */
    public function destroy($id)
    {
        $userId = Auth::id() ?? 1;

        $deleted = DB::table('cerebro_analyses')
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
     * GET /api/cerebro/{id}/export
     */
    public function export(Request $request, $id)
    {
        $userId = Auth::id() ?? 1;

        $analysis = DB::table('cerebro_analyses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$analysis) {
            return response()->json(['error' => 'Analysis not found'], 404);
        }

        $asins = json_decode($analysis->asins, true);

        $keywords = DB::table('cerebro_keywords')
            ->where('analysis_id', $id)
            ->orderBy('cerebro_iq_score', 'desc')
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
            'ASINs Ranking',
            'Avg Rank',
            'Min Rank',
            'Max Rank',
            'Match Type',
        ];

        // Add ASIN rank columns
        foreach ($asins as $asin) {
            $headers[] = "Rank: $asin";
        }

        $rows = [];
        foreach ($keywords as $kw) {
            $organicRanks = json_decode($kw->organic_ranks, true) ?? [];
            
            $row = [
                $kw->keyword,
                $kw->search_volume,
                $kw->cerebro_iq_score,
                $kw->title_density,
                $kw->competing_products,
                $kw->word_count,
                $kw->cpr_8day,
                $kw->cpr_total,
                $kw->keyword_sales,
                $kw->asins_ranking,
                $kw->avg_organic_rank ?? '',
                $kw->min_organic_rank ?? '',
                $kw->max_organic_rank ?? '',
                $kw->match_type,
            ];

            // Add per-ASIN ranks
            foreach ($asins as $asin) {
                $row[] = $organicRanks[$asin] ?? '';
            }

            $rows[] = $row;
        }

        // Generate CSV content
        $csvContent = implode(',', array_map(fn($h) => "\"$h\"", $headers)) . "\n";
        foreach ($rows as $row) {
            $csvContent .= implode(',', array_map(fn($c) => "\"$c\"", $row)) . "\n";
        }

        return response($csvContent, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="cerebro_analysis_' . $id . '.csv"');
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request)
    {
        // Volume filter
        if ($request->filled('volume_min')) {
            $query->where('search_volume', '>=', $request->input('volume_min'));
        }
        if ($request->filled('volume_max')) {
            $query->where('search_volume', '<=', $request->input('volume_max'));
        }

        // IQ Score filter
        if ($request->filled('iq_min')) {
            $query->where('cerebro_iq_score', '>=', $request->input('iq_min'));
        }
        if ($request->filled('iq_max')) {
            $query->where('cerebro_iq_score', '<=', $request->input('iq_max'));
        }

        // Word count filter
        if ($request->filled('words_min')) {
            $query->where('word_count', '>=', $request->input('words_min'));
        }
        if ($request->filled('words_max')) {
            $query->where('word_count', '<=', $request->input('words_max'));
        }

        // Title density filter
        if ($request->filled('title_density_max')) {
            $query->where('title_density', '<=', $request->input('title_density_max'));
        }

        // Competing products filter
        if ($request->filled('competing_max')) {
            $query->where('competing_products', '<=', $request->input('competing_max'));
        }

        // Organic rank filter
        if ($request->filled('rank_min')) {
            $query->where('min_organic_rank', '>=', $request->input('rank_min'));
        }
        if ($request->filled('rank_max')) {
            $query->where('min_organic_rank', '<=', $request->input('rank_max'));
        }

        // ASINs ranking filter
        if ($request->filled('asins_ranking_min')) {
            $query->where('asins_ranking', '>=', $request->input('asins_ranking_min'));
        }

        // Match type filter
        if ($request->filled('match_type') && $request->input('match_type') !== 'all') {
            $query->where('match_type', $request->input('match_type'));
        }

        // Include phrase
        if ($request->filled('include_phrase')) {
            $phrases = explode(',', $request->input('include_phrase'));
            $query->where(function ($q) use ($phrases) {
                foreach ($phrases as $phrase) {
                    $q->orWhere('keyword', 'like', '%' . trim($phrase) . '%');
                }
            });
        }

        // Exclude phrase
        if ($request->filled('exclude_phrase')) {
            $phrases = explode(',', $request->input('exclude_phrase'));
            foreach ($phrases as $phrase) {
                $query->where('keyword', 'not like', '%' . trim($phrase) . '%');
            }
        }

        // Quick filter presets
        if ($request->filled('quick_filter')) {
            switch ($request->input('quick_filter')) {
                case 'top_keywords':
                    $query->where('search_volume', '>=', 1000)
                          ->where('min_organic_rank', '<=', 20);
                    break;
                case 'opportunity':
                    $query->where('cerebro_iq_score', '>=', 3)
                          ->where('title_density', '<=', 5);
                    break;
                case 'low_competition':
                    $query->where('competing_products', '<=', 10000)
                          ->where('search_volume', '>=', 500);
                    break;
                case 'long_tail':
                    $query->where('word_count', '>=', 4)
                          ->where('search_volume', '>=', 100);
                    break;
            }
        }

        return $query;
    }

    /**
     * Generate default analysis name
     */
    private function generateAnalysisName(array $asins): string
    {
        if (count($asins) === 1) {
            return "Analysis: {$asins[0]}";
        }
        return "Analysis: {$asins[0]} + " . (count($asins) - 1) . " more";
    }
}
