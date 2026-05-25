<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    /**
     * Get category insights
     */
    public function category(string $id, Request $request)
    {
        $marketplace = $request->get('marketplace', 'amazon.com');
        
        // Get category statistics from cached products
        $stats = Cache::remember("category_stats_{$marketplace}_{$id}", 3600, function () use ($id, $marketplace) {
            $products = DB::table('product_cache')
                ->where('marketplace', $marketplace)
                ->where('category', 'like', "%{$id}%")
                ->get();

            if ($products->isEmpty()) {
                return null;
            }

            $prices = $products->pluck('price')->filter()->values();
            $bsrs = $products->pluck('bsr')->filter()->values();
            $sales = $products->pluck('monthly_sales_estimate')->filter()->values();

            return [
                'category' => $id,
                'product_count' => $products->count(),
                'price' => [
                    'min' => $prices->min(),
                    'max' => $prices->max(),
                    'avg' => round($prices->avg(), 2),
                    'median' => $this->median($prices->toArray())
                ],
                'bsr' => [
                    'min' => $bsrs->min(),
                    'max' => $bsrs->max(),
                    'avg' => round($bsrs->avg())
                ],
                'monthly_sales' => [
                    'min' => $sales->min(),
                    'max' => $sales->max(),
                    'avg' => round($sales->avg()),
                    'total' => $sales->sum()
                ]
            ];
        });

        if (!$stats) {
            return response()->json([
                'success' => false,
                'message' => 'No data available for this category'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'marketplace' => $marketplace,
            'data' => $stats
        ]);
    }

    /**
     * Get market trends
     */
    public function trends(Request $request)
    {
        $marketplace = $request->get('marketplace', 'amazon.com');
        $days = min($request->get('days', 30), 90);

        // Get trending categories
        $trendingCategories = DB::table('product_cache')
            ->where('marketplace', $marketplace)
            ->where('last_scraped_at', '>=', now()->subDays($days))
            ->select('category', DB::raw('COUNT(*) as product_count'), DB::raw('AVG(monthly_sales_estimate) as avg_sales'))
            ->groupBy('category')
            ->orderByDesc('avg_sales')
            ->limit(20)
            ->get();

        // Get top selling products
        $topProducts = DB::table('product_cache')
            ->where('marketplace', $marketplace)
            ->where('last_scraped_at', '>=', now()->subDays($days))
            ->orderByDesc('monthly_sales_estimate')
            ->limit(10)
            ->get(['asin', 'title', 'category', 'bsr', 'price', 'monthly_sales_estimate']);

        // Get feedback activity (calibration health)
        $feedbackStats = DB::table('sales_feedback')
            ->where('marketplace', $marketplace)
            ->where('created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw('COUNT(*) as total_feedback'),
                DB::raw('AVG(error_percent) as avg_error'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->first();

        return response()->json([
            'success' => true,
            'marketplace' => $marketplace,
            'period_days' => $days,
            'trending_categories' => $trendingCategories,
            'top_products' => $topProducts,
            'calibration_health' => [
                'total_feedback' => $feedbackStats->total_feedback ?? 0,
                'avg_error_percent' => round($feedbackStats->avg_error ?? 0, 2),
                'unique_contributors' => $feedbackStats->unique_users ?? 0
            ]
        ]);
    }

    /**
     * Analyze scraped product data
     */
    public function analyzeProduct(Request $request)
    {
        $validated = $request->validate([
            'asin' => 'required|string|max:20',
            'marketplace' => 'required|string|max:30',
            'title' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:200',
            'bsr' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'monthly_sales_estimate' => 'nullable|integer|min:0',
            'monthly_badge_value' => 'nullable|integer|min:0',
            'monthly_sales_source' => 'nullable|string|in:badge,bsr_estimate,user_feedback,hybrid'
        ]);

        // Upsert product cache
        DB::table('product_cache')->updateOrInsert(
            [
                'asin' => $validated['asin'],
                'marketplace' => $validated['marketplace']
            ],
            [
                'title' => $validated['title'] ?? null,
                'category' => $validated['category'] ?? null,
                'bsr' => $validated['bsr'] ?? null,
                'price' => $validated['price'] ?? null,
                'monthly_sales_estimate' => $validated['monthly_sales_estimate'] ?? null,
                'monthly_badge_value' => $validated['monthly_badge_value'] ?? null,
                'monthly_sales_source' => $validated['monthly_sales_source'] ?? 'bsr_estimate',
                'last_scraped_at' => now(),
                'updated_at' => now()
            ]
        );

        // Get competition data (similar products in category)
        $competition = null;
        if (!empty($validated['category'])) {
            $competition = DB::table('product_cache')
                ->where('marketplace', $validated['marketplace'])
                ->where('category', $validated['category'])
                ->where('asin', '!=', $validated['asin'])
                ->select(
                    DB::raw('COUNT(*) as competitor_count'),
                    DB::raw('AVG(price) as avg_price'),
                    DB::raw('MIN(bsr) as best_bsr'),
                    DB::raw('AVG(monthly_sales_estimate) as avg_sales')
                )
                ->first();
        }

        return response()->json([
            'success' => true,
            'message' => 'Product analyzed and cached',
            'competition' => $competition
        ]);
    }

    /**
     * Helper: Calculate median
     */
    private function median(array $arr)
    {
        if (empty($arr)) return 0;
        sort($arr);
        $count = count($arr);
        $mid = floor(($count - 1) / 2);
        if ($count % 2) {
            return $arr[$mid];
        }
        return ($arr[$mid] + $arr[$mid + 1]) / 2;
    }
}
