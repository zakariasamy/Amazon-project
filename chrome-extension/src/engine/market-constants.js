// Market Constants for Amazon Analytics
class MarketConstants {
    constructor(marketplace = 'amazon.com') {
        this.marketplace = marketplace;
    }

    /**
     * BSR to Sales Conversion Multipliers
     * Based on industry research and Helium 10 methodology
     */
    getBSRMultipliers() {
        return {
            'amazon.com': {
                '1-5': 5000,
                '6-20': 3000,
                '21-50': 2000,
                '51-100': 1500,
                '101-200': 1000,
                '201-500': 700,
                '501-1000': 500,
                '1001-2000': 350,
                '2001-5000': 250,
                '5001-10000': 150,
                '10001-20000': 100,
                '20001-50000': 70,
                '50001-100000': 50,
                '100001-200000': 35,
                '200001-500000': 20,
                '500001-1000000': 10,
                '1000001+': 5
            },
            'amazon.eg': {
                '1-5': 2000,
                '6-20': 1200,
                '21-50': 800,
                '51-100': 600,
                '101-200': 400,
                '201-500': 280,
                '501-1000': 200,
                '1001-2000': 140,
                '2001-5000': 100,
                '5001-10000': 60,
                '10001-20000': 40,
                '20001-50000': 28,
                '50001-100000': 20,
                '100001+': 8
            }
        };
    }

    /**
     * FBA Fee Tiers (amazon.com)
     */
    getFBAFeeTiers() {
        return {
            'amazon.com': {
                'small_standard': {
                    maxWeight: 16, // oz
                    maxLongestSide: 15, // inches
                    maxMedianSide: 12,
                    maxShortestSide: 0.75,
                    baseFee: 3.22,
                    perPoundFee: 0
                },
                'large_standard': {
                    minWeight: 0,
                    maxWeight: 20, // lbs
                    baseFee: 3.86,
                    perPoundAbove1lb: 0.42,
                    perPoundAbove2lb: 0.39
                },
                'large_bulky': {
                    minWeight: 20,
                    maxWeight: 50,
                    baseFee: 9.73,
                    perPoundAbove1lb: 0.83
                },
                'extra_large': {
                    minWeight: 50,
                    baseFee: 89.98,
                    perPoundAbove90lb: 0.83
                }
            },
            'amazon.eg': {
                // Egyptian Pound (EGP) - More realistic conversion
                'small_standard': {
                    maxWeight: 16,
                    baseFee: 30, // EGP (~$1 USD equivalent)
                    perPoundFee: 0
                },
                'large_standard': {
                    maxWeight: 20,
                    baseFee: 40, // EGP
                    perPoundAbove1lb: 5 // EGP
                },
                'large_bulky': {
                    maxWeight: 50,
                    baseFee: 100, // EGP
                    perPoundAbove1lb: 10 // EGP
                }
            }
        };
    }

    /**
     * Referral Fee Percentages by Category
     */
    getReferralFees() {
        return {
            'Automotive': 12,
            'Beauty': 15,
            'Books': 15,
            'Electronics': 8,
            'Clothing': 17,
            'Home & Kitchen': 15,
            'Sports & Outdoors': 15,
            'Toys & Games': 15,
            'Health & Personal Care': 15,
            'Office Products': 15,
            'Pet Supplies': 15,
            'Tools & Home Improvement': 15,
            'Video Games': 15,
            'Default': 15 // Default for unknown categories
        };
    }

    /**
     * Monthly Storage Fees (per cubic foot)
     */
    getStorageFees() {
        return {
            'amazon.com': {
                'jan-sep_standard': 0.87,
                'oct-dec_standard': 2.40,
                'jan-sep_oversize': 0.56,
                'oct-dec_oversize': 1.40
            },
            'amazon.eg': {
                'jan-sep_standard': 27, // EGP
                'oct-dec_standard': 75,
                'jan-sep_oversize': 17,
                'oct-dec_oversize': 43
            }
        };
    }

    /**
     * Seasonality Multipliers by Month
     */
    getSeasonalityMultipliers() {
        return {
            1: 0.85,  // January - Post-holiday slump
            2: 0.90,  // February
            3: 0.95,  // March
            4: 1.00,  // April
            5: 1.05,  // May
            6: 1.10,  // June
            7: 1.15,  // July - Prime Day
            8: 1.05,  // August
            9: 1.00,  // September
            10: 1.10, // October
            11: 1.30, // November - Black Friday
            12: 1.40  // December - Holiday season
        };
    }

    /**
     * Category-specific adjustments
     */
    getCategoryAdjustments() {
        return {
            'Toys & Games': {
                11: 1.50, // Extra boost for holidays
                12: 1.80
            },
            'Clothing': {
                9: 1.20, // Back to school
                11: 1.40  // Holiday shopping
            },
            'Electronics': {
                7: 1.30,  // Prime Day
                11: 1.50, // Black Friday
                12: 1.40  // Holidays
            }
        };
    }

    /**
     * Profit Margin Benchmarks
     */
    getProfitBenchmarks() {
        return {
            'excellent': 40,   // 40%+ margin
            'good': 25,        // 25-40% margin
            'acceptable': 15,  // 15-25% margin
            'poor': 10,        // 10-15% margin
            'loss': 0          // <10% margin
        };
    }

    /**
     * Review to Sales Conversion Rates
     */
    getReviewConversionRates() {
        return {
            'under_10_reviews': 0.01,      // 1% of sales leave review
            '10_to_50_reviews': 0.015,     // 1.5%
            '50_to_100_reviews': 0.02,     // 2%
            '100_to_500_reviews': 0.025,   // 2.5%
            '500_plus_reviews': 0.03       // 3%
        };
    }

    /**
     * Competition Levels based on Review Count
     */
    getCompetitionLevels() {
        return {
            'very_low': { max: 10, score: 1 },
            'low': { min: 11, max: 50, score: 2 },
            'medium': { min: 51, max: 200, score: 3 },
            'high': { min: 201, max: 1000, score: 4 },
            'very_high': { min: 1001, score: 5 }
        };
    }

    /**
     * Calculate monthly sales based on BSR
     */
    calculateMonthlySales(bsr, category = null) {
        const multipliers = this.getBSRMultipliers()[this.marketplace];

        // Find the appropriate multiplier range
        for (const [range, multiplier] of Object.entries(multipliers)) {
            if (range.includes('+')) {
                const minRank = parseInt(range.replace('+', ''));
                if (bsr >= minRank) {
                    return Math.round(multiplier / (bsr / minRank));
                }
            } else {
                const [min, max] = range.split('-').map(Number);
                if (bsr >= min && bsr <= max) {
                    // Linear interpolation within range
                    const position = (bsr - min) / (max - min);
                    const nextRange = this.getNextRangeMultiplier(range, multipliers);
                    const interpolated = multiplier - (position * (multiplier - nextRange));
                    return Math.round(interpolated);
                }
            }
        }

        return 5; // Fallback for very high BSR
    }

    getNextRangeMultiplier(currentRange, multipliers) {
        const ranges = Object.keys(multipliers);
        const currentIndex = ranges.indexOf(currentRange);
        if (currentIndex < ranges.length - 1) {
            return multipliers[ranges[currentIndex + 1]];
        }
        return multipliers[currentRange] * 0.7; // 30% reduction for next tier
    }

    /**
     * Calculate FBA fee based on product dimensions and weight
     */
    calculateFBAFee(weightLbs, dimensions = null) {
        const tiers = this.getFBAFeeTiers()[this.marketplace] || this.getFBAFeeTiers()['amazon.com'];

        // Determine tier (simplified - would need exact dimensions)
        if (weightLbs <= 1) {
            return tiers.small_standard.baseFee;
        } else if (weightLbs <= 20) {
            const tier = tiers.large_standard;
            const additionalWeight = Math.max(0, weightLbs - 1);
            return tier.baseFee + (additionalWeight * tier.perPoundAbove1lb);
        } else if (weightLbs <= 50) {
            const tier = tiers.large_bulky;
            const additionalWeight = Math.max(0, weightLbs - 1);
            return tier.baseFee + (additionalWeight * tier.perPoundAbove1lb);
        } else {
            const tier = tiers.extra_large;
            const additionalWeight = Math.max(0, weightLbs - 90);
            return tier.baseFee + (additionalWeight * tier.perPoundAbove90lb);
        }
    }

    /**
     * Calculate referral fee
     */
    calculateReferralFee(price, category) {
        const fees = this.getReferralFees();
        const feePercent = fees[category] || fees['Default'];
        return (price * feePercent) / 100;
    }

    /**
     * Get current season multiplier
     */
    getCurrentSeasonMultiplier() {
        const month = new Date().getMonth() + 1; // 1-12
        return this.getSeasonalityMultipliers()[month];
    }
}

// MarketConstants is now available globally
