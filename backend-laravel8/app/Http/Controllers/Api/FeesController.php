<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FeesController extends Controller
{
    /**
     * Referral fee percentages by category
     */
    private const REFERRAL_FEES = [
        'amazon.com' => [
            'Electronics' => 0.08,
            'Cell Phones' => 0.08,
            'Computers' => 0.08,
            'Camera' => 0.08,
            'Video Games' => 0.15,
            'Books' => 0.15,
            'Clothing' => 0.17,
            'Shoes' => 0.15,
            'Jewelry' => 0.20,
            'Watches' => 0.15,
            'Home & Kitchen' => 0.15,
            'Sports & Outdoors' => 0.15,
            'Beauty' => 0.15,
            'Health & Household' => 0.15,
            'Grocery' => 0.15,
            'Pet Supplies' => 0.15,
            'Toys & Games' => 0.15,
            'default' => 0.15,
        ],
        'amazon.eg' => [
            'Electronics' => 0.10,
            'Cell Phones' => 0.10,
            'Home & Kitchen' => 0.15,
            'Beauty' => 0.15,
            'Fashion' => 0.15,
            'Books' => 0.15,
            'Sports & Outdoors' => 0.15,
            'Grocery' => 0.10,
            'default' => 0.15,
        ],
    ];

    /**
     * Base fulfillment fees by weight tier
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

    /**
     * Get FBA fees for marketplace
     */
    public function byMarketplace(string $marketplace)
    {
        $referralFees = Cache::remember("referral_fees_{$marketplace}", 86400, function () use ($marketplace) {
            return DB::table('fba_fees')
                ->where('marketplace', $marketplace)
                ->where('effective_date', '<=', now())
                ->where(function ($query) {
                    $query->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>', now());
                })
                ->get();
        });

        $fulfillmentFees = Cache::remember("fulfillment_fees_{$marketplace}", 86400, function () use ($marketplace) {
            return DB::table('fulfillment_fees')
                ->where('marketplace', $marketplace)
                ->where('effective_date', '<=', now())
                ->get();
        });

        return response()->json([
            'success' => true,
            'marketplace' => $marketplace,
            'referral_fees' => $referralFees,
            'fulfillment_fees' => $fulfillmentFees
        ]);
    }

    /**
     * Calculate FBA profit with all costs
     * Used by the FBA Calculator in the Chrome extension
     */
    public function calculateProfit(Request $request)
    {
        $validated = $request->validate([
            'marketplace' => 'required|string|max:30',
            'selling_price' => 'required|numeric|min:0',
            'product_cost' => 'required|numeric|min:0',
            'shipping_cost' => 'required|numeric|min:0',
            'cpc_cost' => 'required|numeric|min:0',
            'tax_percent' => 'required|numeric|min:0|max:100',
            'monthly_sales' => 'required|integer|min:0',
            'weight_kg' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:200',
            'is_fba' => 'nullable|boolean',
        ]);

        $marketplace = $validated['marketplace'];
        $sellingPrice = $validated['selling_price'];
        $productCost = $validated['product_cost'];
        $shippingCost = $validated['shipping_cost'];
        $cpcCost = $validated['cpc_cost'];
        $taxPercent = $validated['tax_percent'];
        $monthlySales = $validated['monthly_sales'];
        $weightKg = $validated['weight_kg'] ?? 0.5;
        $category = $validated['category'] ?? 'default';
        $isFba = $validated['is_fba'] ?? true;

        $currency = $marketplace === 'amazon.eg' ? 'EGP' : 'USD';

        // Calculate Referral Fee
        $referralPercent = $this->getReferralFeePercent($marketplace, $category);
        $referralFee = $sellingPrice * $referralPercent;

        // Calculate Fulfillment Fee
        $fulfillmentFee = $isFba ? $this->getFulfillmentFee($marketplace, $weightKg) : 0;
        
        // Calculate Storage Fee (Assuming 1 month in inventory)
        // Need volume for this: Volume = Length * Width * Height
        // If dimensions not provided, use weight as proxy for volume estimation (rough heuristic)
        $volumeCubicMeters = ($weightKg / 250); // Very rough proxy if no dims (density assumption)
        // Ideally we would accept dimensions in current request, but for now we estimate
        
        $storageFee = $isFba ? $this->getStorageFee($marketplace, $volumeCubicMeters) : 0;

        // Total Amazon Fees
        $amazonFees = $referralFee + $fulfillmentFee + $storageFee;

        // Tax Amount (on selling price)
        $taxAmount = ($sellingPrice * $taxPercent) / 100;

        // Total Costs
        $totalCosts = $productCost + $shippingCost + $cpcCost + $amazonFees + $taxAmount;

        // Net Profit per Unit
        $netProfit = $sellingPrice - $totalCosts;

        // Net Margin
        $netMargin = $sellingPrice > 0 ? ($netProfit / $sellingPrice) * 100 : 0;

        // Investment (user's capital outlay per unit)
        $investment = $productCost + $shippingCost + $cpcCost;

        // ROI (Return on Investment)
        $roi = $investment > 0 ? ($netProfit / $investment) * 100 : 0;

        // Monthly Calculations
        $monthlyRevenue = $sellingPrice * $monthlySales;
        $monthlyProfit = $netProfit * $monthlySales;
        $monthlyCosts = $totalCosts * $monthlySales;

        // Break-even analysis
        $breakEvenUnits = $netProfit > 0 ? 0 : ($investment > 0 ? ceil(abs($netProfit) / $investment) : 0);

        // Profitability rating
        $profitRating = $this->getProfitRating($netMargin, $roi);

        return response()->json([
            'success' => true,
            'currency' => $currency,
            'inputs' => [
                'selling_price' => round($sellingPrice, 2),
                'product_cost' => round($productCost, 2),
                'shipping_cost' => round($shippingCost, 2),
                'cpc_cost' => round($cpcCost, 2),
                'tax_percent' => round($taxPercent, 2),
                'monthly_sales' => $monthlySales,
            ],
            'fees' => [
                'referral_percent' => round($referralPercent * 100, 1),
                'referral_fee' => round($referralFee, 2),
                'fulfillment_fee' => round($fulfillmentFee, 2),
                'storage_fee' => round($storageFee, 2),
                'amazon_total' => round($amazonFees, 2),
                'tax_amount' => round($taxAmount, 2),
            ],
            'costs' => [
                'per_unit' => round($totalCosts, 2),
                'monthly' => round($monthlyCosts, 2),
            ],
            'profit' => [
                'per_unit' => round($netProfit, 2),
                'margin_percent' => round($netMargin, 1),
                'roi_percent' => round($roi, 0),
                'monthly' => round($monthlyProfit, 0),
                'annual' => round($monthlyProfit * 12, 0),
            ],
            'analysis' => [
                'rating' => $profitRating['rating'],
                'color' => $profitRating['color'],
                'message' => $profitRating['message'],
                'break_even_units' => $breakEvenUnits,
            ],
        ]);
    }

    /**
     * Get referral fee percentage for category
     */
    private function getReferralFeePercent(string $marketplace, string $category): float
    {
        $fees = self::REFERRAL_FEES[$marketplace] ?? self::REFERRAL_FEES['amazon.com'];
        
        // Try exact match first
        if (isset($fees[$category])) {
            return $fees[$category];
        }

        // Try partial match
        foreach ($fees as $key => $percent) {
            if (stripos($category, $key) !== false || stripos($key, $category) !== false) {
                return $percent;
            }
        }

        return $fees['default'];
    }

    /**
     * Get fulfillment fee based on weight
     */
    private function getFulfillmentFee(string $marketplace, float $weightKg): float
    {
        $base = self::FULFILLMENT_BASE[$marketplace] ?? self::FULFILLMENT_BASE['amazon.com'];

        if ($marketplace === 'amazon.eg') {
            // Egypt weight tiers (kg)
            if ($weightKg <= 0.5) return $base['small_standard'];
            if ($weightKg <= 1.5) return $base['large_standard'];
            if ($weightKg <= 30) return $base['small_oversize'] + ($weightKg - 1.5) * 2; // per kg surcharge
            return $base['large_oversize'];
        } else {
            // US weight tiers (convert kg to lb)
            $weightLb = $weightKg * 2.205;
            if ($weightLb <= 1) return $base['small_standard'];
            if ($weightLb <= 3) return $base['large_standard'];
            if ($weightLb <= 70) return $base['small_oversize'] + ($weightLb - 3) * 0.40; // per lb surcharge
            return $base['large_oversize'];
        }
    }

    /**
     * Monthly storage fees per cubic meter (approximate)
     */
    private const STORAGE_FEES = [
        'amazon.com' => 30.00, // Roughly $0.87/cubic foot converted to cubic meter (~$30 USD)
        'amazon.eg' => 250.00, // Estimated EGP per cubic meter
    ];

    /**
     * Calculate estimated monthly storage fee
     */
    private function getStorageFee(string $marketplace, float $volumeCubicMeters): float
    {
        $rate = self::STORAGE_FEES[$marketplace] ?? self::STORAGE_FEES['amazon.com'];
        return $volumeCubicMeters * $rate;
    }

    /**
     * Get profitability rating
     */
    private function getProfitRating(float $margin, float $roi): array
    {
        if ($margin >= 30 && $roi >= 100) {
            return ['rating' => 'excellent', 'color' => '#28a745', 'message' => '🚀 Excellent opportunity!'];
        } elseif ($margin >= 20 && $roi >= 50) {
            return ['rating' => 'good', 'color' => '#5cb85c', 'message' => '✅ Good profit potential'];
        } elseif ($margin >= 10 && $roi >= 25) {
            return ['rating' => 'moderate', 'color' => '#ffc107', 'message' => '⚠️ Moderate margin - optimize costs'];
        } elseif ($margin > 0) {
            return ['rating' => 'low', 'color' => '#ff9800', 'message' => '⚠️ Low margin - reconsider pricing'];
        } else {
            return ['rating' => 'negative', 'color' => '#dc3545', 'message' => '❌ Not profitable at current costs'];
        }
    }
}
