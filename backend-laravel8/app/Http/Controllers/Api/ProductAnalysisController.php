<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProductAnalysisController extends Controller
{
    /**
     * Analyze a scraped product and return all calculations
     * This is the main endpoint the extension calls to get insights
     */
    public function analyze(Request $request)
    {
        // More lenient validation - extension may send incomplete data
        $validated = $request->validate([
            'asin' => 'required|string|max:20',
            'marketplace' => 'required|string|max:30',
            'title' => 'nullable|string|max:500',
            'price' => 'nullable|numeric|min:0', // Made optional - will default to 0
            'currency' => 'nullable|string|max:10',
            'bsr' => 'nullable|integer|min:0', // Allow 0
            'category' => 'nullable|string|max:200',
            'reviews_count' => 'nullable|integer|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_fba' => 'nullable', // Made more lenient
            'weight_kg' => 'nullable|numeric|min:0',
            'monthly_badge' => 'nullable|integer|min:0',
            'cogs' => 'nullable|numeric|min:0',
        ]);

        $marketplace = $validated['marketplace'];
        $category = $validated['category'] ?? 'default';
        $price = floatval($validated['price'] ?? 0);
        $bsr = isset($validated['bsr']) && $validated['bsr'] > 0 ? intval($validated['bsr']) : null;
        $isFBA = filter_var($validated['is_fba'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $monthlyBadge = $validated['monthly_badge'] ?? null;
        $cogs = $validated['cogs'] ?? ($price > 0 ? $price * 0.30 : 0); // Default 30% of price
        $weightKg = floatval($validated['weight_kg'] ?? 0.5);
        $currency = $validated['currency'] ?? ($marketplace === 'amazon.eg' ? 'EGP' : 'USD');

        // Get algorithm constants
        $constants = $this->getConstants($marketplace, $category);

        // Get seasonality
        $seasonality = $this->getSeasonality($marketplace);

        // Calculate sales estimate
        $salesEstimate = $this->calculateSalesEstimate($bsr, $monthlyBadge, $constants, $seasonality, $isFBA);

        // Calculate fees
        $fees = $this->calculateFees($marketplace, $category, $price, $weightKg, $isFBA);

        // Calculate profit metrics
        $profitMetrics = $this->calculateProfitMetrics($price, $cogs, $fees, $salesEstimate['monthly'], $marketplace);

        // Calculate competition metrics
        $competitionMetrics = $this->calculateCompetitionMetrics($bsr, $validated['reviews_count'] ?? 0, $validated['rating'] ?? 0, $salesEstimate['monthly']);

        // Calculate opportunity score
        $opportunityScore = $this->calculateOpportunityScore($profitMetrics, $competitionMetrics, $salesEstimate);

        // Generate insights
        $insights = $this->generateInsights($salesEstimate, $profitMetrics, $competitionMetrics, $opportunityScore);

        // Cache the product for analytics
        $this->cacheProduct($validated, $salesEstimate);

        return response()->json([
            'success' => true,
            'asin' => $validated['asin'],
            'marketplace' => $marketplace,
            'price' => $price,
            'currency' => $currency,

            'sales' => $salesEstimate,
            'fees' => $fees,
            'profit' => $profitMetrics,
            'competition' => $competitionMetrics,
            'opportunity' => $opportunityScore,
            'insights' => $insights,

            'constants_version' => $constants['version'] ?? '2025.01.01',
            'calculated_at' => now()->toISOString()
        ]);
    }

    private function getConstants(string $marketplace, string $category): array
    {
        $constant = Cache::remember("const_{$marketplace}_{$category}", 3600, function () use ($marketplace, $category) {
            return DB::table('algorithm_constants')
                ->where('marketplace', $marketplace)
                ->where('category', $category)
                ->where('is_active', true)
                ->first();
        });

        // Fallback to default if category not found
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
                'C' => 50000,
                'P' => 0.68,
                'CVR' => 0.11,
                'floor' => 5,
                'ceiling' => 120000,
                'confidence' => 0.85,
                'version' => '2025.01.01'
            ];
        }

        return [
            'C' => floatval($constant->c_value),
            'P' => floatval($constant->p_value),
            'CVR' => floatval($constant->cvr_value),
            'floor' => intval($constant->floor_value),
            'ceiling' => intval($constant->ceiling_value),
            'confidence' => floatval($constant->market_confidence),
            'version' => $constant->version
        ];
    }

    private function getSeasonality(string $marketplace): float
    {
        $month = intval(date('n'));
        $year = intval(date('Y'));

        $seasonality = DB::table('seasonality_factors')
            ->where('marketplace', $marketplace)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        return $seasonality ? floatval($seasonality->multiplier) : 1.0;
    }

    private function calculateSalesEstimate(?int $bsr, ?int $monthlyBadge, array $constants, float $seasonality, bool $isFBA): array
    {
        // If we have the monthly badge, use it directly (most accurate)
        if ($monthlyBadge && $monthlyBadge > 0) {
            return [
                'monthly' => $monthlyBadge,
                'daily' => ($monthlyBadge / 30) < 10 ? round($monthlyBadge / 30, 1) : round($monthlyBadge / 30),
                'yearly' => $monthlyBadge * 12,
                'source' => 'badge',
                'confidence' => 'high',
                'range' => [
                    'min' => round($monthlyBadge * 0.9),
                    'max' => round($monthlyBadge * 1.1)
                ]
            ];
        }

        // If no BSR, return minimum estimate
        if (!$bsr || $bsr <= 0) {
            return [
                'monthly' => $constants['floor'],
                'daily' => 0,
                'yearly' => $constants['floor'] * 12,
                'source' => 'unranked',
                'confidence' => 'very_low',
                'range' => ['min' => 0, 'max' => $constants['floor'] * 2]
            ];
        }

        // BSR-based calculation: Sales = C / BSR^P
        $baseSales = $constants['C'] / pow($bsr, $constants['P']);

        // Apply seasonality
        $baseSales *= $seasonality;

        // FBA boost (typically sells 10% more)
        if ($isFBA) {
            $baseSales *= 1.10;
        }

        // Clamp to floor/ceiling
        $monthlySales = max(min(round($baseSales), $constants['ceiling']), $constants['floor']);

        // Calculate confidence based on BSR range
        $confidence = $constants['confidence'];
        if ($bsr > 100000) $confidence *= 0.7;
        elseif ($bsr > 50000) $confidence *= 0.85;

        $confidenceLabel = $confidence >= 0.75 ? 'high' : ($confidence >= 0.5 ? 'medium' : 'low');

        // Calculate range based on confidence
        $spreadPercent = 0.20 + (1 - $confidence) * 0.30;

        return [
            'monthly' => $monthlySales,
            'daily' => ($monthlySales / 30) < 10 ? round($monthlySales / 30, 1) : round($monthlySales / 30),
            'yearly' => $monthlySales * 12,
            'source' => 'bsr_estimate',
            'confidence' => $confidenceLabel,
            'confidence_score' => round($confidence, 2),
            'range' => [
                'min' => round($monthlySales * (1 - $spreadPercent)),
                'max' => round($monthlySales * (1 + $spreadPercent))
            ]
        ];
    }

    /**
     * Base fulfillment fees by weight tier (Synced with FeesController)
     */
    private const FULFILLMENT_BASE = [
        'amazon.com' => [
            'small_standard' => 3.22,    // <= 1 lb
            'large_standard' => 4.95,    // <= 3 lb
            'small_oversize' => 9.73,    // <= 70 lb
            'large_oversize' => 89.98,   // > 70 lb
        ],
        'amazon.eg' => [
            'small_standard' => 25.00,   // <= 0.5 kg
            'large_standard' => 35.00,   // <= 1.5 kg
            'small_oversize' => 60.00,   // <= 30 kg
            'large_oversize' => 120.00,  // > 30 kg
        ],
    ];

    private function calculateFees(string $marketplace, string $category, float $price, float $weightKg, bool $isFBA): array
    {
        // Get referral fee
        $referralFee = Cache::remember("referral_fee_{$marketplace}_{$category}", 86400, function () use ($marketplace, $category) {
            $fee = DB::table('fba_fees')
                ->where('marketplace', $marketplace)
                ->where('category', $category)
                ->where('effective_date', '<=', now())
                ->first();

            if (!$fee) {
                // Try to find partial match for category if exact match fails
                $fee = DB::table('fba_fees')
                    ->where('marketplace', $marketplace)
                    ->where('category', 'like', "%{$category}%")
                    ->where('effective_date', '<=', now())
                    ->first();
            }

            if (!$fee) {
                $fee = DB::table('fba_fees')
                    ->where('marketplace', $marketplace)
                    ->where('category', 'default')
                    ->where('effective_date', '<=', now())
                    ->first();
            }

            return $fee;
        });

        $referralPercent = $referralFee ? floatval($referralFee->referral_fee_percent) : 15.0;
        $referralMin = $referralFee ? floatval($referralFee->referral_fee_min) : 0.30;

        $referralAmount = max($price * ($referralPercent / 100), $referralMin);

        // Fulfillment fee (if FBA)
        $fulfillmentAmount = 0;
        if ($isFBA) {
            // Use standardized logic from FeesController for consistency
            $base = self::FULFILLMENT_BASE[$marketplace] ?? self::FULFILLMENT_BASE['amazon.com'];

            if ($marketplace === 'amazon.eg') {
                // Egypt weight tiers (kg)
                if ($weightKg <= 0.5) $fulfillmentAmount = $base['small_standard'];
                elseif ($weightKg <= 1.5) $fulfillmentAmount = $base['large_standard'];
                elseif ($weightKg <= 30) $fulfillmentAmount = $base['small_oversize'] + ($weightKg - 1.5) * 2; // per kg surcharge approximation
                else $fulfillmentAmount = $base['large_oversize'];
            } else {
                // US weight tiers (convert kg to lb)
                $weightLb = $weightKg * 2.205;
                if ($weightLb <= 1) $fulfillmentAmount = $base['small_standard'];
                elseif ($weightLb <= 3) $fulfillmentAmount = $base['large_standard'];
                elseif ($weightLb <= 70) $fulfillmentAmount = $base['small_oversize'] + ($weightLb - 3) * 0.40;
                else $fulfillmentAmount = $base['large_oversize'];
            }
        }

        $totalFees = $referralAmount + $fulfillmentAmount;

        return [
            'referral' => round($referralAmount, 2),
            'referral_percent' => $referralPercent,
            'fulfillment' => round($fulfillmentAmount, 2),
            'total' => round($totalFees, 2),
            'is_fba' => $isFBA
        ];
    }

    private function calculateProfitMetrics(float $price, float $cogs, array $fees, int $monthlySales, string $marketplace): array
    {
        $totalFees = $fees['total'];

        // Default tax estimate (14% VAT for Egypt, 0 for others in simple view)
        $taxRate = ($marketplace === 'amazon.eg') ? 0.14 : 0.0;
        $estimatedTax = $price * $taxRate;

        $profitPerUnit = $price - $cogs - $totalFees - $estimatedTax;

        $margin = $price > 0 ? ($profitPerUnit / $price) * 100 : 0;
        $roi = ($cogs + $totalFees) > 0 ? ($profitPerUnit / ($cogs + $totalFees)) * 100 : 0;

        $monthlyProfit = $profitPerUnit * $monthlySales;
        $annualProfit = $monthlyProfit * 12;
        $monthlyRevenue = $price * $monthlySales;

        return [
            'per_unit' => round($profitPerUnit, 2),
            'margin_percent' => round($margin, 1),
            'roi_percent' => round($roi, 1),
            'monthly' => round($monthlyProfit, 2),
            'annual' => round($annualProfit, 2),
            'monthly_revenue' => round($monthlyRevenue, 2),
            'cogs' => round($cogs, 2),
            'estimated_tax' => round($estimatedTax, 2)
        ];
    }

    private function calculateCompetitionMetrics(?int $bsr, int $reviewsCount, float $rating, int $monthlySales): array
    {
        // Competition is based on TOTAL REVIEW COUNT (1-5), matching the extension UI.
        // Levels:
        //   very_low:  0-10   (score 1)
        //   low:       11-50  (score 2)
        //   medium:    51-200 (score 3)
        //   high:      201-1,000 (score 4)
        //   very_high: 1,001+ (score 5)

        $level = 'very_low';
        $score = 1;

        if ($reviewsCount <= 10) {
            $level = 'very_low';
            $score = 1;
        } elseif ($reviewsCount <= 50) {
            $level = 'low';
            $score = 2;
        } elseif ($reviewsCount <= 200) {
            $level = 'medium';
            $score = 3;
        } elseif ($reviewsCount <= 1000) {
            $level = 'high';
            $score = 4;
        } else {
            $level = 'very_high';
            $score = 5;
        }

        // Review velocity (reviews/month) requires product age, which we don't reliably have.
        // Use the same conservative assumption as the extension: ~12 months in market.
        $reviewVelocity = round($reviewsCount / 12, 1);

        // Market saturation flag used by the UI badge.
        // Keep this separate from the competition score (which is reviews-only).
        $saturated = false;
        if ($bsr && $reviewsCount > 500 && $bsr > 10000) {
            $saturated = true;
        }

        return [
            'score' => $score,
            'level' => $level,
            'reviews' => $reviewsCount,
            'rating' => $rating,
            'reviewVelocity' => $reviewVelocity,
            'saturated' => $saturated,
        ];
    }

    private function calculateOpportunityScore(array $profit, array $competition, array $sales): array
    {
        $notes = [];

        // Sales Volume Notes
        if ($sales['monthly'] >= 500) {
            $notes[] = ['type' => 'success', 'message' => '🔥 High demand - strong sales volume'];
        } elseif ($sales['monthly'] >= 200) {
            $notes[] = ['type' => 'success', 'message' => '✅ Good demand - solid sales volume'];
        } elseif ($sales['monthly'] >= 50) {
            $notes[] = ['type' => 'warning', 'message' => '⚠️ Moderate demand - verify market interest'];
        } else {
            $notes[] = ['type' => 'danger', 'message' => '❌ Low sales volume - demand unverified'];
        }

        // Margin Notes
        if ($profit['margin_percent'] >= 40) {
            $notes[] = ['type' => 'success', 'message' => '💰 Excellent margins (' . round($profit['margin_percent'], 0) . '%)'];
        } elseif ($profit['margin_percent'] >= 25) {
            $notes[] = ['type' => 'success', 'message' => '✅ Good margins (' . round($profit['margin_percent'], 0) . '%)'];
        } elseif ($profit['margin_percent'] >= 15) {
            $notes[] = ['type' => 'warning', 'message' => '⚠️ Moderate margins (' . round($profit['margin_percent'], 0) . '%) - optimize costs'];
        } else {
            $notes[] = ['type' => 'danger', 'message' => '❌ Low margins (' . round($profit['margin_percent'], 0) . '%) - may not be profitable'];
        }

        // Rating Notes
        $productRating = floatval($competition['rating'] ?? 0);
        if ($productRating >= 4.5) {
            $notes[] = ['type' => 'warning', 'message' => '⚠️ High rating (' . $productRating . '★) - tough to compete'];
        } elseif ($productRating >= 4.0) {
            $notes[] = ['type' => 'success', 'message' => '✅ Solid rating (' . $productRating . '★) - quality product'];
        } elseif ($productRating >= 3.5) {
            $notes[] = ['type' => 'warning', 'message' => '⚠️ Average rating (' . $productRating . '★) - room for improvement'];
        } elseif ($productRating > 0) {
            $notes[] = ['type' => 'danger', 'message' => '❌ Low rating (' . $productRating . '★) - quality concerns'];
        }

        // Competition Notes
        $reviewCount = intval($competition['reviews'] ?? 0);
        if ($reviewCount >= 1000) {
            $notes[] = ['type' => 'danger', 'message' => '🔴 High competition - ' . number_format($reviewCount) . ' reviews'];
        } elseif ($reviewCount >= 500) {
            $notes[] = ['type' => 'warning', 'message' => '⚠️ Moderate competition - ' . number_format($reviewCount) . ' reviews'];
        } elseif ($reviewCount >= 100) {
            $notes[] = ['type' => 'success', 'message' => '✅ Low competition - ' . number_format($reviewCount) . ' reviews'];
        } else {
            $notes[] = ['type' => 'success', 'message' => '🟢 Very low competition - only ' . $reviewCount . ' reviews'];
        }

        // Calculate a simple score for backwards compatibility
        $positiveCount = count(array_filter($notes, fn($n) => $n['type'] === 'success'));
        $dangerCount = count(array_filter($notes, fn($n) => $n['type'] === 'danger'));

        $overallRating = 'fair';
        if ($dangerCount >= 2) $overallRating = 'poor';
        elseif ($dangerCount === 0 && $positiveCount >= 3) $overallRating = 'excellent';
        elseif ($positiveCount >= 2) $overallRating = 'good';

        return [
            'score' => 0, // Deprecated - using notes now
            'rating' => $overallRating,
            'recommended' => $dangerCount < 2,
            'notes' => $notes
        ];
    }

    private function generateInsights(array $sales, array $profit, array $competition, array $opportunity): array
    {
        $insights = [];

        // Sales insights
        if ($sales['confidence'] === 'high') {
            $insights[] = ['type' => 'success', 'message' => "High confidence estimate: ~{$sales['monthly']} sales/month"];
        } elseif ($sales['confidence'] === 'low') {
            $insights[] = ['type' => 'warning', 'message' => "Low confidence estimate - BSR may be unstable"];
        }

        // Profit insights
        if ($profit['margin_percent'] >= 40) {
            $insights[] = ['type' => 'success', 'message' => "Excellent margins ({$profit['margin_percent']}%) - great profit potential"];
        } elseif ($profit['margin_percent'] < 15) {
            $insights[] = ['type' => 'danger', 'message' => "Low margins ({$profit['margin_percent']}%) - may not be profitable"];
        }

        // Competition insights
        if ($competition['level'] === 'very_high') {
            $insights[] = ['type' => 'warning', 'message' => "Very competitive market - hard to gain market share"];
        } elseif ($competition['level'] === 'low' || $competition['level'] === 'very_low') {
            $insights[] = ['type' => 'success', 'message' => "Low competition - easier to rank and grow"];
        }

        // Opportunity insight
        if ($opportunity['recommended']) {
            $insights[] = ['type' => 'success', 'message' => "Opportunity score: {$opportunity['score']}/100 - Worth considering!"];
        } else {
            $insights[] = ['type' => 'warning', 'message' => "Opportunity score: {$opportunity['score']}/100 - Proceed with caution"];
        }

        return $insights;
    }

    private function cacheProduct(array $data, array $sales): void
    {
        DB::table('product_cache')->updateOrInsert(
            [
                'asin' => $data['asin'],
                'marketplace' => $data['marketplace']
            ],
            [
                'title' => $data['title'] ?? null,
                'category' => $data['category'] ?? null,
                'bsr' => $data['bsr'] ?? null,
                'price' => $data['price'] ?? null,
                'monthly_badge_value' => $data['monthly_badge'] ?? null,
                'monthly_sales_estimate' => $sales['monthly'],
                'monthly_sales_source' => $sales['source'],
                'last_scraped_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}
