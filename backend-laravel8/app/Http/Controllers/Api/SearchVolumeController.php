<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SearchVolumeController extends Controller
{
    /**
     * CVR (Conversion Rate) by marketplace and category
     * Used to reverse-engineer search volume from sales
     */
    private const CVR_TABLE = [
        'amazon.com' => [
            'Electronics' => 0.065,
            'Cell Phones' => 0.070,
            'Fashion' => 0.100,
            'Home & Kitchen' => 0.120,
            'Beauty' => 0.085,
            'Health & Household' => 0.140,
            'Sports & Outdoors' => 0.100,
            'Toys & Games' => 0.120,
            'Grocery' => 0.250,
            'Pet Supplies' => 0.140,
            'Books' => 0.100,
            'default' => 0.110,
        ],
        'amazon.eg' => [
            'Electronics' => 0.060,
            'Cell Phones' => 0.075,
            'Fashion' => 0.080,
            'Home & Kitchen' => 0.100,
            'Beauty' => 0.090,
            'Health & Household' => 0.120,
            'Sports & Outdoors' => 0.070,
            'Toys & Games' => 0.100,
            'Grocery' => 0.180,
            'Pet Supplies' => 0.100,
            'Books' => 0.080,
            'default' => 0.100,
        ],
    ];

    /**
     * Estimate search volume from SERP product data
     * Formula: SearchVolume = (Σ Sales_i / CVR) / ClickShare
     */
    public function estimate(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
            'marketplace' => 'required|string|max:30',
            'products' => 'required|array|min:1|max:100',
            'products.*.asin' => 'required|string|max:20',
            'products.*.position' => 'required|integer|min:1|max:100',
            'products.*.bsr' => 'nullable|integer|min:0',
            'products.*.price' => 'nullable|numeric|min:0',
            'products.*.reviews' => 'nullable|integer|min:0',
            'products.*.rating' => 'nullable|numeric|min:0|max:5',
            'products.*.is_sponsored' => 'nullable|boolean',
            'products.*.monthly_sales' => 'nullable|integer|min:0',
            'products.*.category' => 'nullable|string|max:200',
        ]);

        $result = $this->performEstimation(
            $validated['keyword'],
            $validated['marketplace'],
            $validated['products']
        );

        return response()->json($result);
    }

    public function batchEstimate(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1|max:20',
            'items.*.keyword' => 'required|string|max:255',
            'items.*.marketplace' => 'required|string|max:30',
            'items.*.products' => 'required|array|min:1|max:100',
            'items.*.products.*.asin' => 'required|string|max:20',
            'items.*.products.*.position' => 'required|integer|min:1|max:100',
            'items.*.products.*.bsr' => 'nullable|integer|min:0',
            'items.*.products.*.price' => 'nullable|numeric|min:0',
            'items.*.products.*.reviews' => 'nullable|integer|min:0',
            'items.*.products.*.rating' => 'nullable|numeric|min:0|max:5',
            'items.*.products.*.is_sponsored' => 'nullable|boolean',
            'items.*.products.*.monthly_sales' => 'nullable|integer|min:0',
            'items.*.products.*.category' => 'nullable|string|max:200',
        ]);

        $results = [];
        foreach ($validated['items'] as $item) {
            $results[] = $this->performEstimation(
                $item['keyword'],
                $item['marketplace'],
                $item['products']
            );
        }

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    protected function performEstimation($keyword, $marketplace, $products)
    {
        // Save fresh BSR data to cache for analytics (but don't READ from cache - BSR changes frequently)
        $updates = [];
        $now = now();
        
        foreach ($products as &$product) {
            $asin = $product['asin'] ?? null;
            if (!$asin) continue;
            
            $hasNewBsr = !empty($product['bsr']);
            
            if ($hasNewBsr) {
                // Save fresh BSR to cache for historical tracking
                $updates[] = [
                    'asin' => $asin,
                    'marketplace' => $marketplace,
                    'bsr' => $product['bsr'],
                    'category' => $product['category'] ?? null,
                    'price' => !empty($product['price']) ? $product['price'] : 0,
                    'monthly_sales_estimate' => !empty($product['monthly_sales']) ? $product['monthly_sales'] : 0,
                    'last_scraped_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now // used only on insert
                ];
            }
            // NOTE: We no longer read BSR from cache - frontend enriches with fresh data
        }
        unset($product);
        
        // Perform bulk upsert to save fresh BSR data
        if (!empty($updates)) {
            DB::table('product_cache')->upsert(
                $updates, 
                ['asin', 'marketplace'], 
                ['bsr', 'category', 'price', 'monthly_sales_estimate', 'last_scraped_at', 'updated_at']
            );
        }

        // Count products with actual sales data
        $productsWithSales = count(array_filter($products, fn($p) => !empty($p['monthly_sales']) && $p['monthly_sales'] > 0));
        $productsWithBsr = count(array_filter($products, fn($p) => !empty($p['bsr']) && $p['bsr'] > 0));
        
        // Check cache if we have limited sales data (e.g., from reverse ASIN module)
        // A previous market analysis might have better data
        $cachedVolume = null;
        if ($productsWithSales < 5 && $productsWithBsr < 5) {
            // First try search_analyses for detailed data
            $cachedData = DB::table('search_analyses')
                ->where('keyword', $keyword)
                ->where('marketplace', $marketplace)
                ->where('created_at', '>=', now()->subDays(7)) // Only use recent cache (7 days)
                ->orderByDesc('search_volume')
                ->first();
            
            if ($cachedData && $cachedData->search_volume > 0) {
                $cachedVolume = [
                    'estimated' => (int) $cachedData->search_volume,
                    'range' => [
                        'min' => (int) round($cachedData->search_volume * 0.7),
                        'max' => (int) round($cachedData->search_volume * 1.3)
                    ],
                    'confidence' => 'medium',
                    'confidence_score' => 0.6,
                    'demand_level' => $cachedData->demand_level ?? 'low',
                    'source' => 'cache',
                    'products_used' => $cachedData->products_count ?? 0,
                    'cached_at' => $cachedData->created_at
                ];
            }
            
            // Fallback to keyword_cache if search_analyses has no data
            if (!$cachedVolume) {
                $keywordCache = DB::table('keyword_cache')
                    ->where('keyword', $keyword)
                    ->where('marketplace', $marketplace)
                    ->where('updated_at', '>=', now()->subDays(14)) // Allow older cache
                    ->first();
                
                if ($keywordCache && $keywordCache->search_volume_estimate > 0) {
                    $demandLevel = 'low';
                    if ($keywordCache->search_volume_estimate >= 3000) $demandLevel = 'high';
                    elseif ($keywordCache->search_volume_estimate >= 1000) $demandLevel = 'medium';
                    
                    $cachedVolume = [
                        'estimated' => (int) $keywordCache->search_volume_estimate,
                        'range' => [
                            'min' => (int) round($keywordCache->search_volume_estimate * 0.7),
                            'max' => (int) round($keywordCache->search_volume_estimate * 1.3)
                        ],
                        'confidence' => 'low',
                        'confidence_score' => 0.4,
                        'demand_level' => $demandLevel,
                        'source' => 'keyword_cache',
                        'cached_at' => $keywordCache->updated_at
                    ];
                }
            }
        }

        // DEBUG: Log individual product data for debugging
        $debugProducts = [];
        foreach ($products as $i => $product) {
            $debugProducts[] = [
                'position' => $i + 1,
                'asin' => $product['asin'] ?? 'N/A',
                'monthly_sales' => $product['monthly_sales'] ?? null,
                'bsr' => $product['bsr'] ?? null,
                'is_sponsored' => $product['is_sponsored'] ?? false,
            ];
        }

        // Enrich products with estimated sales
        $enrichedProducts = $this->enrichProductsWithEstimates($marketplace, $products);

        // Calculate product statistics
        $productStats = $this->calculateProductStats($enrichedProducts);

        // Calculate search volume from current data
        $result = $this->calculateSearchVolume($marketplace, $products);
        
        // Use cached volume if it's significantly better than current calculation
        // (Current calculation with limited data may underestimate)
        if ($cachedVolume && $cachedVolume['estimated'] > $result['estimated'] * 1.5) {
            $result = $cachedVolume;
        }

        // Calculate keyword difficulty
        $difficulty = $this->calculateKeywordDifficulty($products);

        // Calculate ad density
        $adMetrics = $this->calculateAdMetrics($products);

        // Generate insights
        $insights = $this->generateInsights($result, $difficulty, $adMetrics);

        // Cache keyword data for analytics
        $this->cacheKeywordData($keyword, $marketplace, $result, $difficulty);

        // Save full analysis history (ENSURE products data is saved)
        $this->saveSearchAnalysis($keyword, $marketplace, $result, $result['demand_level'] ?? 'low', $enrichedProducts);

        return [
            'success' => true,
            'keyword' => $keyword,
            'marketplace' => $marketplace,
            
            'search_volume' => $result,
            'difficulty' => $difficulty,
            'ad_metrics' => $adMetrics,
            'insights' => $insights,
            
            // Product statistics for display
            'product_stats' => $productStats,
            
            // Return enriched products for table display
            'products' => $enrichedProducts,
            
            'products_analyzed' => count($products),
            'calculated_at' => now()->toISOString(),
            
            // DEBUG: Include product breakdown for debugging
            'debug' => [
                'product_sales' => $debugProducts,
                'total_products_received' => count($products),
                'products_with_sales' => count(array_filter($products, fn($p) => !empty($p['monthly_sales']) && $p['monthly_sales'] > 0)),
                'products_with_bsr' => count(array_filter($products, fn($p) => !empty($p['bsr']) && $p['bsr'] > 0)),
            ]
        ];
    }

    /**
     * Calculate product statistics from enriched products
     */
    private function calculateProductStats(array $enrichedProducts): array
    {
        if (empty($enrichedProducts)) {
            return [
                'total_revenue' => 0,
                'average_revenue' => 0,
                'average_price' => 0,
                'average_bsr' => 0,
                'average_reviews' => 0,
                'total_sales' => 0,
                'product_count' => 0,
            ];
        }

        $totalRevenue = 0;
        $totalPrice = 0;
        $totalBsr = 0;
        $totalReviews = 0;
        $totalSales = 0;
        $priceCount = 0;
        $bsrCount = 0;
        $reviewCount = 0;
        $salesCount = 0;

        foreach ($enrichedProducts as $product) {
            // Revenue
            $revenue = $product['revenue'] ?? 0;
            $totalRevenue += $revenue;
            
            // Price
            $price = $product['price'] ?? 0;
            if ($price > 0) {
                $totalPrice += $price;
                $priceCount++;
            }
            
            // BSR
            $bsr = $product['bsr'] ?? 0;
            if ($bsr > 0) {
                $totalBsr += $bsr;
                $bsrCount++;
            }
            
            // Reviews
            $reviews = $product['reviews'] ?? 0;
            if ($reviews > 0) {
                $totalReviews += $reviews;
                $reviewCount++;
            }
            
            // Sales
            $sales = $product['monthly_sales'] ?? 0;
            if ($sales > 0) {
                $totalSales += $sales;
                $salesCount++;
            }
        }

        $productCount = count($enrichedProducts);

        return [
            'total_revenue' => round($totalRevenue),
            'average_revenue' => $productCount > 0 ? round($totalRevenue / $productCount) : 0,
            'average_price' => $priceCount > 0 ? round($totalPrice / $priceCount, 2) : 0,
            'average_bsr' => $bsrCount > 0 ? round($totalBsr / $bsrCount) : 0,
            'average_reviews' => $reviewCount > 0 ? round($totalReviews / $reviewCount) : 0,
            'total_sales' => round($totalSales),
            'average_sales' => $salesCount > 0 ? round($totalSales / $salesCount) : 0,
            'product_count' => $productCount,
        ];
    }

    /**
     * Enrich products with estimated sales and calculated fields
     */
    private function enrichProductsWithEstimates(string $marketplace, array $products): array
    {
        $enrichedProducts = [];
        
        foreach ($products as $product) {
            $monthlySales = $product['monthly_sales'] ?? null;
            $isEstimated = false;
            
            // Ensure BSR is a valid integer (handle string 'null' or empty values)
            $bsr = null;
            if (isset($product['bsr']) && $product['bsr'] !== null && $product['bsr'] !== 'null' && $product['bsr'] !== '') {
                $bsr = intval($product['bsr']);
                if ($bsr <= 0) $bsr = null;
            }
            
            // Estimate sales from BSR if not provided
            if (!$monthlySales && $bsr !== null && $bsr > 0) {
                $category = $product['category'] ?? 'default';
                $monthlySales = $this->estimateSalesFromBSR($marketplace, $bsr, $category);
                $isEstimated = true;
            }
            
            // Calculate revenue
            $price = floatval($product['price'] ?? 0);
            $revenue = $monthlySales ? round($price * $monthlySales) : 0;
            
            // Estimate FBA fees (simplified - 15% referral + base fulfillment)
            $referralFee = $price * 0.15;
            $fulfillmentFee = $marketplace === 'amazon.eg' ? 25 : 3.50;
            $estimatedFees = round($referralFee + $fulfillmentFee, 2);
            
            $enrichedProducts[] = [
                'asin' => $product['asin'] ?? '',
                'position' => $product['position'] ?? 0,
                'title' => $product['title'] ?? '',
                'brand' => $product['brand'] ?? null,
                'category' => $product['category'] ?? null,
                'price' => $price,
                'monthly_sales' => $monthlySales ?? 0,
                'is_sales_estimated' => $isEstimated,
                'is_new_product' => $bsr === null && !$monthlySales, // No BSR and no sales = new product
                'revenue' => $revenue,
                'bsr' => $bsr,
                'bsr_category' => $product['bsr_category'] ?? $product['category'] ?? null, // Use category even without BSR
                'reviews' => intval($product['reviews'] ?? 0),
                'rating' => floatval($product['rating'] ?? 0),
                'is_sponsored' => boolval($product['is_sponsored'] ?? false),
                'is_fba' => !boolval($product['is_fbm'] ?? false),
                'estimated_fees' => $estimatedFees,
                'image' => $product['image'] ?? null,
            ];
        }
        
        return $enrichedProducts;
    }

    /**
     * Calculate estimated monthly search volume
     * Uses position-weighted click attribution with organic products prioritized
     */
    private function calculateSearchVolume(string $marketplace, array $products): array
    {
        if (empty($products)) {
            return [
                'estimated' => 0,
                'range' => ['min' => 0, 'max' => 0],
                'confidence' => 'very_low',
                'confidence_score' => 0.1,
                'source' => 'no_data'
            ];
        }

        // Step 1: Separate organic and sponsored products
        $organic = [];
        $sponsored = [];
        foreach ($products as $product) {
            if ($product['is_sponsored'] ?? false) {
                $sponsored[] = $product;
            } else {
                $organic[] = $product;
            }
        }

        // Step 2: Reorder - organic first, sponsored at end
        $reordered = array_merge($organic, $sponsored);

        $clickShare = 0.95; // Page 1 captures ~95% of clicks
        $weightedSales = 0;
        $totalSales = 0;
        $maxSales = 0;
        $minSales = null;
        $validProducts = 0;
        $confidenceSum = 0;
        $debugBSR = [];

        // Step 3: Calculate with position weights
        foreach ($reordered as $i => $product) {
            $position = $i + 1;
            
            // Get sales estimate for this product
            $monthlySales = $product['monthly_sales'] ?? null;
            
            if (!$monthlySales && isset($product['bsr']) && $product['bsr'] > 0) {
                // Estimate sales from BSR if not provided
                $category = $product['category'] ?? 'default';
                $monthlySales = $this->estimateSalesFromBSR($marketplace, $product['bsr'], $category);
                
                $debugBSR[] = [
                    'asin' => $product['asin'] ?? 'unknown',
                    'bsr' => $product['bsr'],
                    'category' => $category,
                    'sales' => $monthlySales
                ];
            }

            if (!$monthlySales || $monthlySales <= 0) {
                continue;
            }

            // Track sales stats
            $totalSales += $monthlySales;
            $maxSales = max($maxSales, $monthlySales);
            if ($minSales === null || $monthlySales < $minSales) {
                $minSales = $monthlySales;
            }

            // Position weight - scalable to 60+ products
            $positionWeight = $this->getPositionWeight($position);
            
            // Type weight: Organic = 1.0, Sponsored = 0.5 (less reliable sales source)
            $typeWeight = ($product['is_sponsored'] ?? false) ? 0.5 : 1.0;
            
            // Weighted sales contribution
            $weightedSales += $monthlySales * $positionWeight * $typeWeight;

            // Position-based confidence
            $positionConfidence = max(0.5, 1 - (0.02 * ($position - 1)));
            $confidenceSum += $positionConfidence;
            $validProducts++;
        }

        if ($validProducts === 0) {
            return [
                'estimated' => 100,
                'range' => ['min' => 50, 'max' => 200],
                'confidence' => 'very_low',
                'confidence_score' => 0.1,
                'source' => 'fallback'
            ];
        }

        // Get average CVR for marketplace
        $avgCVR = $this->getCVR($marketplace, 'default');

        // Step 4: Calculate search volume from weighted sales
        // weightedSales accounts for position distribution
        $searchVolume = round($weightedSales / $avgCVR / $clickShare);

        // Confidence calculation
        $avgPositionConfidence = $confidenceSum / $validProducts;
        $sampleConfidence = min($validProducts / 20, 1.0);
        $confidenceScore = round($avgPositionConfidence * $sampleConfidence, 2);

        $confidenceLabel = 'medium';
        if ($confidenceScore >= 0.75) $confidenceLabel = 'high';
        elseif ($confidenceScore >= 0.5) $confidenceLabel = 'medium';
        elseif ($confidenceScore >= 0.3) $confidenceLabel = 'low';
        else $confidenceLabel = 'very_low';

        // Range calculation (wider when confidence is lower)
        $spreadPercent = 0.30 + (1 - $confidenceScore) * 0.20;

        // Calculate demand level
        $demandLevel = 'low';
        if ($searchVolume >= 3000) {
            $demandLevel = 'high';
        } elseif ($searchVolume >= 1000) {
            $demandLevel = 'medium';
        }

        return [
            'estimated' => $searchVolume,
            'range' => [
                'min' => round($searchVolume * (1 - $spreadPercent)),
                'max' => round($searchVolume * (1 + $spreadPercent))
            ],
            'confidence' => $confidenceLabel,
            'confidence_score' => $confidenceScore,
            'demand_level' => $demandLevel,
            'products_used' => $validProducts,
            'organic_count' => count($organic),
            'sponsored_count' => count($sponsored),
            'source' => 'serp_analysis_v2', // Updated version
            'sales_metrics' => [
                'total_monthly_sales' => $totalSales,
                'weighted_sales' => round($weightedSales),
                'min_monthly_sales' => $minSales ?? 0,
                'max_monthly_sales' => $maxSales
            ],
            'debug_bsr' => $debugBSR ?? []
        ];
    }

    /**
     * Get position weight for click attribution
     * Scalable to 60+ products
     */
    private function getPositionWeight(int $position): float
    {
        return match(true) {
            $position <= 5 => 0.15,   // Grid positions 1-5: 15% each (75% total)
            $position <= 10 => 0.03,  // Positions 6-10: 3% each (15% total)
            $position <= 20 => 0.005, // Positions 11-20: 0.5% each (5% total)
            $position <= 40 => 0.002, // Positions 21-40: 0.2% each (4% total)
            default => 0.001,         // Positions 41-60: 0.1% each (1% total)
        };
    }

    /**
     * Calculate Keyword Difficulty (KD) Score 0-100
     */
    private function calculateKeywordDifficulty(array $products): array
    {
        $top10 = array_slice($products, 0, 10);
        
        if (empty($top10)) {
            return [
                'score' => 50,
                'level' => 'medium',
                'breakdown' => [],
                'recommendation' => 'Insufficient data to calculate difficulty'
            ];
        }

        // 1. Listing Strength (35% weight) - based on reviews and rating
        $avgReviews = 0;
        $avgRating = 0;
        $reviewCount = 0;
        
        foreach ($top10 as $p) {
            if (isset($p['reviews'])) {
                $avgReviews += $p['reviews'];
                $reviewCount++;
            }
            if (isset($p['rating'])) {
                $avgRating += $p['rating'];
            }
        }
        
        $avgReviews = $reviewCount > 0 ? $avgReviews / $reviewCount : 0;
        $avgRating = $reviewCount > 0 ? $avgRating / $reviewCount : 4.0;
        $listingStrength = min(100, ($avgReviews / 50) * ($avgRating / 5) * 100);

        // 2. Ad Density (25% weight)
        $sponsoredCount = count(array_filter($top10, fn($p) => $p['is_sponsored'] ?? false));
        $adDensity = ($sponsoredCount / max(count($top10), 1)) * 100;

        // 3. Review Barrier (25% weight) - median reviews needed
        $reviews = array_map(fn($p) => $p['reviews'] ?? 0, $top10);
        sort($reviews);
        $medianReviews = $reviews[floor(count($reviews) / 2)] ?? 0;
        $reviewBarrier = min(100, ($medianReviews / 50) * 100);

        // 4. Brand Dominance (15% weight) - based on brand frequency
        // Filter out placeholder/generic brand names (English and Arabic)
        $placeholderBrands = ['generic', 'unbranded', 'no brand', 'nobrand', 'unknown', 'n/a', 'na', '-', '', 'جينيريك', 'بدون علامة تجارية'];
        $brands = array_filter(
            array_map(fn($p) => strtolower(trim($p['brand'] ?? '')), $top10),
            fn($b) => !in_array($b, $placeholderBrands) && strlen($b) > 1
        );
        $brandDominance = 0;
        
        if (count($brands) > 0) {
            // Count frequency of each brand
            $brandCounts = array_count_values($brands);
            $maxBrandCount = max($brandCounts);
            $totalWithBrand = count($brands);
            
            // Dominance = (Most common brand count / total) * 100
            // If 1 brand owns 5/10 = 50%, if all different = 10% each
            $brandDominance = round(($maxBrandCount / $totalWithBrand) * 100);
        }

        // Final KD Score
        $kdScore = round(
            ($listingStrength * 0.35) +
            ($adDensity * 0.25) +
            ($reviewBarrier * 0.25) +
            ($brandDominance * 0.15)
        );
        $kdScore = max(0, min(100, $kdScore));

        // Level and recommendation
        $level = 'medium';
        $recommendation = 'Achievable with quality listing';
        
        if ($kdScore < 20) {
            $level = 'very_easy';
            $recommendation = 'Great opportunity for new sellers';
        } elseif ($kdScore < 40) {
            $level = 'easy';
            $recommendation = 'Good entry point with some optimizations';
        } elseif ($kdScore < 60) {
            $level = 'moderate';
            $recommendation = 'Requires solid listing and some reviews';
        } elseif ($kdScore < 80) {
            $level = 'hard';
            $recommendation = 'Needs PPC investment and differentiation';
        } else {
            $level = 'very_hard';
            $recommendation = 'Dominated by established brands - consider alternatives';
        }

        return [
            'score' => $kdScore,
            'level' => $level,
            'breakdown' => [
                'listing_strength' => round($listingStrength),
                'ad_density' => round($adDensity),
                'review_barrier' => round($reviewBarrier),
                'brand_dominance' => round($brandDominance)
            ],
            'recommendation' => $recommendation
        ];
    }

    /**
     * Calculate advertising metrics
     */
    private function calculateAdMetrics(array $products): array
    {
        $total = count($products);
        if ($total === 0) {
            return ['sponsored_count' => 0, 'density_percent' => 0, 'warning' => null];
        }

        $sponsored = count(array_filter($products, fn($p) => $p['is_sponsored'] ?? false));
        $density = round(($sponsored / $total) * 100, 1);

        $warning = null;
        if ($density > 40) {
            $warning = 'High ad density (>40%) — organic ranking may be difficult';
        }

        return [
            'sponsored_count' => $sponsored,
            'organic_count' => $total - $sponsored,
            'density_percent' => $density,
            'warning' => $warning
        ];
    }

    /**
     * Generate actionable insights
     */
    private function generateInsights(array $volume, array $difficulty, array $adMetrics): array
    {
        $insights = [];

        // Volume insight
        if ($volume['estimated'] >= 10000) {
            $insights[] = [
                'type' => 'success',
                'message' => "High search volume ({$volume['estimated']}+/mo) - strong market demand"
            ];
        } elseif ($volume['estimated'] >= 1000) {
            $insights[] = [
                'type' => 'info',
                'message' => "Moderate search volume (~{$volume['estimated']}/mo) - viable niche"
            ];
        } elseif ($volume['estimated'] < 500) {
            $insights[] = [
                'type' => 'warning',
                'message' => "Low search volume (<500/mo) - limited demand"
            ];
        }

        // Difficulty insight
        if ($difficulty['score'] < 40) {
            $insights[] = [
                'type' => 'success',
                'message' => "Low keyword difficulty ({$difficulty['score']}) - good ranking opportunity"
            ];
        } elseif ($difficulty['score'] >= 70) {
            $insights[] = [
                'type' => 'warning',
                'message' => "High keyword difficulty ({$difficulty['score']}) - competitive keyword"
            ];
        }

        // Ad density insight
        if ($adMetrics['warning']) {
            $insights[] = [
                'type' => 'warning',
                'message' => $adMetrics['warning']
            ];
        }

        // Combined opportunity
        if ($volume['estimated'] >= 1000 && $difficulty['score'] < 50) {
            $insights[] = [
                'type' => 'success',
                'message' => '⭐ Great opportunity: Good volume + Low difficulty'
            ];
        }

        return $insights;
    }

    /**
     * Get CVR for marketplace/category
     */
    private function getCVR(string $marketplace, string $category): float
    {
        $marketplaceCVR = self::CVR_TABLE[$marketplace] ?? self::CVR_TABLE['amazon.com'];
        return $marketplaceCVR[$category] ?? $marketplaceCVR['default'];
    }

    /**
     * Estimate sales from BSR using algorithm constants
     * NOTE: Amazon only shows "X+ bought in past month" badge for products with 50+ sales.
     * If we're estimating from BSR (meaning no badge was shown), sales must be <50.
     * Therefore we cap BSR-estimated sales at 49.
     */
    private function estimateSalesFromBSR(string $marketplace, int $bsr, string $category): int
    {
        $constants = $this->getConstants($marketplace, $category);
        
        $sales = $constants['C'] / pow($bsr, $constants['P']);
        $sales = max(min(round($sales), $constants['ceiling']), $constants['floor']);
        
        // Cap at 49: If Amazon didn't show a sales badge, it means sales < 50
        // So even if BSR formula estimates higher, we know it's under 50
        $sales = min($sales, 49);
        
        return (int) $sales;
    }

    /**
     * Get algorithm constants
     */
    private function getConstants(string $marketplace, string $category): array
    {
        $constant = Cache::remember("const_{$marketplace}_{$category}", 3600, function () use ($marketplace, $category) {
            return DB::table('algorithm_constants')
                ->where('marketplace', $marketplace)
                ->where('category', $category)
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

        if (!$constant) {
            // Hardcoded fallback
            return [
                'C' => $marketplace === 'amazon.eg' ? 1100 : 50000,
                'P' => 0.68,
                'floor' => $marketplace === 'amazon.eg' ? 2 : 5,
                'ceiling' => $marketplace === 'amazon.eg' ? 8000 : 120000,
            ];
        }

        return [
            'C' => floatval($constant->c_value),
            'P' => floatval($constant->p_value),
            'floor' => intval($constant->floor_value),
            'ceiling' => intval($constant->ceiling_value),
        ];
    }

    /**
     * Cache keyword data for analytics
     */
    private function cacheKeywordData(string $keyword, string $marketplace, array $volume, array $difficulty): void
    {
        DB::table('keyword_cache')->updateOrInsert(
            [
                'marketplace' => $marketplace,
                'keyword' => $keyword
            ],
            [
                'search_volume_estimate' => $volume['estimated'],
                'difficulty_score' => $difficulty['score'],
                'search_count' => DB::raw('COALESCE(search_count, 0) + 1'),
                'last_seen_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Save full search analysis to history
     */
    private function saveSearchAnalysis(string $keyword, string $marketplace, array $volume, string $demandLevel, array $products): void
    {
        DB::table('search_analyses')->insert([
            'marketplace' => $marketplace,
            'keyword' => $keyword,
            'search_volume' => $volume['estimated'],
            'demand_level' => $demandLevel,
            'products_count' => count($products),
            'products_data' => json_encode($products),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
