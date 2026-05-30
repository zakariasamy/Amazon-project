<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCacheController extends Controller
{
    public function batch(Request $request)
    {
        $validated = $request->validate([
            'marketplace' => ['required', 'string', 'max:30'],
            'asins' => ['required', 'array', 'max:100'],
            'asins.*' => ['required', 'string', 'max:20'],
        ]);

        $asins = collect($validated['asins'])
            ->map(fn ($asin) => strtoupper(trim($asin)))
            ->filter()
            ->unique()
            ->values();

        $products = DB::table('product_cache')
            ->where('marketplace', $validated['marketplace'])
            ->whereIn('asin', $asins)
            ->whereDate('last_scraped_at', now()->toDateString())
            ->get()
            ->map(function ($row) {
                return [
                    'asin' => $row->asin,
                    'title' => $row->title,
                    'category' => $row->category,
                    'bsr_category' => $row->category,
                    'bsr' => $row->bsr !== null ? (int) $row->bsr : null,
                    'price' => $row->price !== null ? (float) $row->price : null,
                    'monthly_sales' => $row->monthly_sales_estimate !== null ? (int) $row->monthly_sales_estimate : null,
                    'monthly_badge_value' => $row->monthly_badge_value !== null ? (int) $row->monthly_badge_value : null,
                    'monthly_sales_source' => $row->monthly_sales_source,
                    'noSalesData' => $row->bsr === null
                        && (int) ($row->monthly_sales_estimate ?? 0) <= 0
                        && (int) ($row->monthly_badge_value ?? 0) <= 0,
                    'last_scraped_at' => $row->last_scraped_at,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'marketplace' => $validated['marketplace'],
            'requested' => $asins->count(),
            'found' => $products->count(),
            'products' => $products,
            'by_asin' => $products->keyBy('asin'),
        ]);
    }
}
