<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CerebroWebController extends Controller
{
    /**
     * Display list of all Cerebro analyses for current user
     */
    public function index()
    {
        $userId = Auth::id();
        
        // Get paginated analyses
        $analyses = DB::table('cerebro_analyses')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get stats
        $stats = [
            'total_analyses' => DB::table('cerebro_analyses')
                ->where('user_id', $userId)
                ->count(),
            'total_keywords' => DB::table('cerebro_keywords')
                ->join('cerebro_analyses', 'cerebro_keywords.analysis_id', '=', 'cerebro_analyses.id')
                ->where('cerebro_analyses.user_id', $userId)
                ->count(),
            'total_asins' => DB::table('cerebro_analyses')
                ->where('user_id', $userId)
                ->sum('asin_count'),
            'this_month' => DB::table('cerebro_analyses')
                ->where('user_id', $userId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
        
        return view('cerebro.index', compact('analyses', 'stats'));
    }

    /**
     * Display a specific analysis with keywords
     */
    public function show(Request $request, $id)
    {
        $userId = Auth::id();
        
        // Get analysis
        $analysis = DB::table('cerebro_analyses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
        
        if (!$analysis) {
            abort(404, 'Analysis not found');
        }
        
        // Build keywords query
        $query = DB::table('cerebro_keywords')
            ->where('analysis_id', $id);
        
        // Apply filters
        if ($request->filled('filter')) {
            switch ($request->input('filter')) {
                case 'top':
                    $query->where('search_volume', '>=', 1000)
                          ->where('min_organic_rank', '<=', 20);
                    break;
                case 'opportunity':
                    $query->where('cerebro_iq_score', '>=', 3)
                          ->where('title_density', '<=', 5);
                    break;
                case 'low_comp':
                    $query->where('competing_products', '<=', 10000)
                          ->where('search_volume', '>=', 500);
                    break;
            }
        }
        
        // Search
        if ($request->filled('search')) {
            $query->where('keyword', 'like', '%' . $request->input('search') . '%');
        }
        
        // Sorting
        $sortBy = $request->input('sort', 'cerebro_iq_score');
        $sortDir = $request->input('dir', 'desc');
        $query->orderBy($sortBy, $sortDir);
        
        // Paginate
        $keywords = $query->paginate(50);
        
        // Get stats for this analysis
        $stats = [
            'avg_iq' => DB::table('cerebro_keywords')
                ->where('analysis_id', $id)
                ->avg('cerebro_iq_score'),
            'avg_volume' => DB::table('cerebro_keywords')
                ->where('analysis_id', $id)
                ->avg('search_volume'),
        ];
        
        return view('cerebro.show', compact('analysis', 'keywords', 'stats'));
    }

    /**
     * Export analysis to CSV
     */
    public function export($id)
    {
        $userId = Auth::id();
        
        $analysis = DB::table('cerebro_analyses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
        
        if (!$analysis) {
            abort(404, 'Analysis not found');
        }
        
        $asins = json_decode($analysis->asins, true);
        
        $keywords = DB::table('cerebro_keywords')
            ->where('analysis_id', $id)
            ->orderBy('cerebro_iq_score', 'desc')
            ->get();
        
        // Build CSV headers
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
        
        // Add ASIN columns
        foreach ($asins as $asin) {
            $headers[] = "Rank: $asin";
        }
        
        // Build rows
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
            
            foreach ($asins as $asin) {
                $row[] = $organicRanks[$asin] ?? '';
            }
            
            $rows[] = $row;
        }
        
        // Generate CSV
        $csvContent = implode(',', array_map(fn($h) => "\"$h\"", $headers)) . "\n";
        foreach ($rows as $row) {
            $csvContent .= implode(',', array_map(fn($c) => "\"$c\"", $row)) . "\n";
        }
        
        $filename = 'cerebro_' . str_replace([' ', ':'], '_', $analysis->name ?? 'analysis') . '_' . $id . '.csv';
        
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
        
        $deleted = DB::table('cerebro_analyses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();
        
        if (!$deleted) {
            return redirect()->route('cerebro.index')->with('error', 'Analysis not found');
        }
        
        return redirect()->route('cerebro.index')->with('success', 'Analysis deleted successfully');
    }
    
    /**
     * List all keyword folders
     */
    public function folders()
    {
        $userId = Auth::id();
        
        $folders = \App\Models\CerebroFolder::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('cerebro.folders', compact('folders'));
    }
    
    /**
     * Show folder detail with keywords
     */
    public function folderShow(Request $request, $id)
    {
        $userId = Auth::id();
        
        $folder = \App\Models\CerebroFolder::where('user_id', $userId)
            ->findOrFail($id);
        
        // Build query
        $query = $folder->keywords();
        
        // Apply filters
        if ($request->filled('min_volume')) {
            $query->where('search_volume', '>=', $request->min_volume);
        }
        if ($request->filled('max_volume')) {
            $query->where('search_volume', '<=', $request->max_volume);
        }
        if ($request->filled('min_iq')) {
            $query->where('cerebro_iq_score', '>=', $request->min_iq);
        }
        if ($request->filled('search')) {
            $query->where('keyword', 'like', '%' . $request->search . '%');
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'search_volume');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);
        
        // Paginate
        $keywords = $query->paginate(50);
        
        return view('cerebro.folder-show', compact('folder', 'keywords'));
    }
}
