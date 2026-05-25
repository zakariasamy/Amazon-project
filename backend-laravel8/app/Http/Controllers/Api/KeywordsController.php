<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class KeywordsController extends Controller
{
    /**
     * Get popular keywords for marketplace
     */
    public function popular(string $marketplace)
    {
        $keywords = Cache::remember("popular_keywords_{$marketplace}", 3600, function () use ($marketplace) {
            return DB::table('keyword_cache')
                ->where('marketplace', $marketplace)
                ->orderByDesc('search_count')
                ->limit(100)
                ->get(['keyword', 'search_count', 'category', 'last_seen_at']);
        });

        return response()->json([
            'success' => true,
            'marketplace' => $marketplace,
            'keywords' => $keywords
        ]);
    }

    /**
     * Cache keywords discovered by extension
     */
    public function cache(Request $request)
    {
        $validated = $request->validate([
            'marketplace' => 'required|string|max:30',
            'keywords' => 'required|array|max:50',
            'keywords.*.keyword' => 'required|string|max:255',
            'keywords.*.category' => 'nullable|string|max:100'
        ]);

        $cached = 0;
        foreach ($validated['keywords'] as $kw) {
            DB::table('keyword_cache')->updateOrInsert(
                [
                    'marketplace' => $validated['marketplace'],
                    'keyword' => strtolower(trim($kw['keyword']))
                ],
                [
                    'category' => $kw['category'] ?? null,
                    'search_count' => DB::raw('COALESCE(search_count, 0) + 1'),
                    'last_seen_at' => now(),
                    'updated_at' => now()
                ]
            );
            $cached++;
        }

        // Clear cache for this marketplace
        Cache::forget("popular_keywords_{$validated['marketplace']}");

        return response()->json([
            'success' => true,
            'cached' => $cached
        ]);
    }
}
