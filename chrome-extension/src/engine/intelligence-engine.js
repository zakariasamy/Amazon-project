// Intelligence Engine - Sales Estimation & Analytics
// MarketConstants is loaded before this script via manifest

class IntelligenceEngine {
    constructor(marketplace = 'amazon.com') {
        this.marketplace = marketplace;
        this.constants = new MarketConstants(marketplace);
    }

    /**
     * Analyze complete product data and return insights
     */
    analyze(productData) {
        const bsr = productData.bsr?.rank ? parseInt(productData.bsr.rank) : null;
        const price = parseFloat(productData.price) || 0;
        const reviewCount = parseInt(productData.reviewCount) || 0;

        // If no BSR, estimate based on review count
        let sales, revenue, fees, profit, competition, opportunity;

        if (!bsr) {
            // Rough estimation based on reviews when BSR is missing
            const estimatedMonthlySales = Math.max(50, reviewCount * 2); // Very rough estimate
            sales = {
                monthly: estimatedMonthlySales,
                daily: Math.round(estimatedMonthlySales / 30),
                confidence: 'very_low'
            };
            revenue = this.estimateRevenue(sales, price);
            fees = this.calculateFees(price, productData);
            profit = this.calculateProfit(revenue, fees, price);
            competition = this.analyzeCompetition(reviewCount, 50000, productData.sellerCount || 1); // Assume mid-range BSR
            opportunity = this.calculateOpportunityScore(profit, competition, sales);
        } else {
            // Normal calculation with BSR
            sales = this.estimateSales(bsr, productData.category);
            revenue = this.estimateRevenue(sales, price);
            fees = this.calculateFees(price, productData);
            profit = this.calculateProfit(revenue, fees, price);
            competition = this.analyzeCompetition(reviewCount, bsr, productData.sellerCount || 1);
            opportunity = this.calculateOpportunityScore(profit, competition, sales);
        }

        return {
            // Input data
            asin: productData.asin,
            title: productData.title,
            price,
            bsr: bsr || null,
            bsrData: productData.bsr,  // Include full BSR data with allRankings
            category: productData.bsr?.category || productData.category || 'Unknown',
            reviewCount,
            rating: parseFloat(productData.rating) || 0,
            currency: this.marketplace === 'amazon.eg' ? 'EGP' : 'USD',  // Currency detection

            // Product Specs
            dimensions: productData.dimensions,
            weight: productData.weight,

            // Sales estimates
            sales: {
                monthly: sales.monthly,
                daily: sales.daily,
                confidence: sales.confidence
            },

            // Revenue estimates
            revenue: {
                monthly: revenue.monthly,
                daily: revenue.daily,
                annual: revenue.annual
            },

            // Cost breakdown
            fees: {
                fba: fees.fba,
                referral: fees.referral,
                storage: fees.storage,
                total: fees.total
            },

            // Profit analysis
            profit: {
                perUnit: profit.perUnit,
                monthly: profit.monthly,
                margin: profit.margin,
                roi: profit.roi,
                rating: profit.rating
            },

            // Competition analysis
            competition: {
                level: competition.level,
                score: competition.score,
                reviewVelocity: competition.reviewVelocity,
                saturated: competition.saturated
            },

            // Opportunity score
            opportunity: {
                score: opportunity.score,
                rating: opportunity.rating,
                reasons: opportunity.reasons,
                recommended: opportunity.recommended
            },

            // Additional insights
            insights: this.generateInsights(sales, profit, competition, !bsr),

            // Metadata
            analyzedAt: new Date().toISOString(),
            marketplace: this.marketplace,
            noBSR: !bsr,  // Flag to indicate BSR was missing

            // Dual-language data
            title_ar: productData.title_ar,
            category_ar: productData.category_ar,
            availability_ar: productData.availability_ar
        };
    }

    /**
     * Estimate monthly and daily sales based on BSR
     */
    estimateSales(bsr, category = null) {
        const monthlySales = this.constants.calculateMonthlySales(bsr, category);

        // Apply seasonality
        const seasonMultiplier = this.constants.getCurrentSeasonMultiplier();
        const adjustedMonthlySales = Math.round(monthlySales * seasonMultiplier);

        // Calculate confidence based on BSR
        const confidence = this.calculateSalesConfidence(bsr);

        return {
            monthly: adjustedMonthlySales,
            daily: Math.round(adjustedMonthlySales / 30),
            confidence: confidence
        };
    }

    calculateSalesConfidence(bsr) {
        if (bsr <= 1000) return 'high';
        if (bsr <= 10000) return 'medium';
        if (bsr <= 100000) return 'low';
        return 'very_low';
    }

    /**
     * Estimate revenue
     */
    estimateRevenue(sales, price) {
        const monthly = sales.monthly * price;

        return {
            monthly: Math.round(monthly * 100) / 100,
            daily: Math.round((monthly / 30) * 100) / 100,
            annual: Math.round((monthly * 12) * 100) / 100
        };
    }

    /**
     * Calculate all fees
     */
    calculateFees(price, productData) {
        // Extract weight (rough estimate if not available)
        const weight = this.parseWeight(productData.weight) || 1;

        // FBA fee
        const fbaFee = this.constants.calculateFBAFee(weight);

        // Referral fee
        const category = productData.bsr?.category || productData.category || 'Default';
        const referralFee = this.constants.calculateReferralFee(price, category);

        // Storage fee
        let storageFee = 0;
        const volumeCuFt = this.calculateVolume(productData.dimensions);

        // Get rates for current marketplace
        const storageRates = this.constants.getStorageFees()[this.marketplace] || this.constants.getStorageFees()['amazon.com'];

        // Use Jan-Sep standard rate as baseline estimate
        // If volume is unknown, assume small standard size (approx 0.02 cu ft) for fallback
        const estimatedVolume = volumeCuFt || 0.02;
        const ratePerCuFt = storageRates['jan-sep_standard'];

        storageFee = estimatedVolume * ratePerCuFt;

        const total = fbaFee + referralFee + storageFee;

        return {
            fba: Math.round(fbaFee * 100) / 100,
            referral: Math.round(referralFee * 100) / 100,
            storage: Math.round(storageFee * 100) / 100,
            total: Math.round(total * 100) / 100,
            isEstimatedStorage: !volumeCuFt
        };
    }

    calculateVolume(dimStr) {
        if (!dimStr) return null;

        // Clean string: remove invisible characters (LRM, RLM, etc.) and trim
        // This ensures \u200e doesn't break the regex
        const cleanDimStr = dimStr.replace(/[\u2000-\u206F\u2E00-\u2E7F\\'!"#$%&()*+,\-.\/:;<=>?@\[\]^`{|}~]/g, ' ').replace(/\s+/g, ' ').trim();
        // Note: The previous regex was strict. Let's make it simpler but robust.
        // We really just want 3 numbers separated by 'x' (or similar) and a unit.

        // 1. Sanitize to find just the structure "Nu x Nu x Nu unit"
        // Try matching English "cm/in" or Arabic "سم"
        const regex = /([\d.]+)\s*[xX×]\s*([\d.]+)\s*[xX×]\s*([\d.]+)\s*(inches|in|cm|سم)/i;

        let match = dimStr.match(regex);
        if (!match) {
            // Try again on the cleaned string if the raw one failed
            match = cleanDimStr.match(regex);
        }

        if (match) {
            let l = parseFloat(match[1]);
            let w = parseFloat(match[2]);
            let h = parseFloat(match[3]);
            const unit = match[match.length - 1].toLowerCase();

            // Convert to inches
            if (unit === 'cm' || unit === 'سم') {
                l = l / 2.54;
                w = w / 2.54;
                h = h / 2.54;
            }

            // Volume in cubic feet = (L*W*H) / 1728
            const volValues = (l * w * h) / 1728;
            console.log(`Calculated volume from "${dimStr}": ${volValues} cu ft`);
            return volValues;
        }

        console.warn(`Could not parse dimensions string: "${dimStr}"`);
        return null; // Fallback will be used in caller
    }

    parseWeight(weightStr) {
        if (!weightStr) return null;

        const match = weightStr.match(/([\d.]+)\s*(pound|lb|kg|ounce|oz)/i);
        if (match) {
            const value = parseFloat(match[1]);
            const unit = match[2].toLowerCase();

            // Convert to pounds
            if (unit.includes('kg')) return value * 2.20462;
            if (unit.includes('oz')) return value / 16;
            return value;
        }

        return null;
    }

    /**
     * Calculate profit metrics
     */
    calculateProfit(revenue, fees, price) {
        const assumedCOGS = price * 0.25; // Reduced to 25% COGS (was 35%)
        const perUnit = price - fees.total - assumedCOGS;
        const monthlySalesUnits = Math.round(revenue.monthly / price);
        const monthly = perUnit * monthlySalesUnits;
        const margin = (perUnit / price) * 100;
        const roi = (perUnit / (assumedCOGS + fees.total)) * 100;

        // Rating
        let rating = 'poor';
        if (margin >= 40) rating = 'excellent';
        else if (margin >= 25) rating = 'good';
        else if (margin >= 15) rating = 'acceptable';

        return {
            perUnit: Math.round(perUnit * 100) / 100,
            monthly: Math.round(monthly * 100) / 100,
            margin: Math.round(margin * 100) / 100,
            roi: Math.round(roi * 100) / 100,
            rating
        };
    }

    /**
     * Analyze competition
     */
    analyzeCompetition(reviewCount, bsr, sellerCount = 1) {
        const levels = this.constants.getCompetitionLevels();

        let level = 'very_low';
        let score = 1;

        for (const [key, data] of Object.entries(levels)) {
            if (data.min && data.max) {
                if (reviewCount >= data.min && reviewCount <= data.max) {
                    level = key;
                    score = data.score;
                    break;
                }
            } else if (data.max && reviewCount <= data.max) {
                level = key;
                score = data.score;
                break;
            } else if (data.min && reviewCount >= data.min) {
                level = key;
                score = data.score;
            }
        }

        // Increase competition score for high seller count (buybox competition)
        if (sellerCount > 8) {
            score = Math.min(4, score + 2); // Very high buybox competition
        } else if (sellerCount > 3) {
            score = Math.min(4, score + 1); // Moderate buybox competition
        }

        // Update level name based on updated score
        if (score === 4) level = 'very_high';
        else if (score === 3) level = 'high';
        else if (score === 2) level = 'medium';
        else level = 'low';

        // Estimate review velocity (reviews per month)
        const reviewVelocity = Math.round(reviewCount / 12); // Assume 1 year average

        // Market saturation check - either high reviews & bad BSR, or extremely high buybox seller competition
        const saturated = (reviewCount > 500 && bsr > 10000) || sellerCount > 10;

        return {
            level,
            score,
            reviewVelocity,
            saturated,
            sellerCount
        };
    }

    /**
     * Calculate opportunity score (0-100)
     */
    calculateOpportunityScore(profit, competition, sales) {
        let score = 0;
        const reasons = [];

        // Profit contribution (40 points)
        if (profit.margin >= 40) {
            score += 40;
            reasons.push('Excellent profit margin');
        } else if (profit.margin >= 25) {
            score += 30;
            reasons.push('Good profit margin');
        } else if (profit.margin >= 15) {
            score += 20;
        } else {
            score += 10;
            reasons.push('⚠️ Low profit margin');
        }

        // Competition contribution (30 points)
        if (competition.score <= 2) {
            score += 30;
            reasons.push('Low competition');
        } else if (competition.score === 3) {
            score += 20;
        } else {
            score += 10;
            reasons.push('⚠️ High competition');
        }

        // Sales volume contribution (30 points)
        if (sales.monthly >= 1000) {
            score += 30;
            reasons.push('High sales volume');
        } else if (sales.monthly >= 300) {
            score += 20;
            reasons.push('Good sales volume');
        } else if (sales.monthly >= 100) {
            score += 10;
        } else {
            score += 5;
            reasons.push('⚠️ Low sales volume');
        }

        // Overall rating
        let rating = 'poor';
        if (score >= 80) rating = 'excellent';
        else if (score >= 60) rating = 'good';
        else if (score >= 40) rating = 'fair';

        const recommended = score >= 60;

        return {
            score,
            rating,
            reasons,
            recommended
        };
    }

    /**
     * Generate insights and recommendations
     */
    generateInsights(sales, profit, competition, noBSR) {
        const insights = [];

        // Warning if no BSR
        if (noBSR) {
            insights.push({
                type: 'warning',
                message: `⚠️ BSR data not found. Sales estimates are very rough based on review count.`
            });
        }

        // Sales insights
        if (sales.monthly > 1000) {
            insights.push({
                type: 'success',
                message: `🔥 High demand product with ${sales.monthly.toLocaleString()} estimated monthly sales`
            });
        } else if (sales.monthly < 100) {
            insights.push({
                type: 'warning',
                message: `⚠️ Low sales volume detected. Consider if niche is worth targeting`
            });
        }

        // Profit insights
        if (profit.margin >= 40) {
            insights.push({
                type: 'success',
                message: `💰 Excellent ${profit.margin.toFixed(1)}% profit margin`
            });
        } else if (profit.margin < 15) {
            insights.push({
                type: 'danger',
                message: `❌ Low profit margin (${profit.margin.toFixed(1)}%). May not be viable`
            });
        }

        // Competition insights
        if (competition.saturated) {
            insights.push({
                type: 'warning',
                message: `⚠️ Market appears saturated. Differentiation will be critical`
            });
        } else if (competition.score <= 2) {
            insights.push({
                type: 'success',
                message: `✅ Low competition. Good opportunity to establish presence`
            });
        }

        // Buybox Seller count insights (1 seller is not flagged as Private Label to respect new products)
        if (competition.sellerCount > 8) {
            insights.push({
                type: 'danger',
                message: `⚠️ Extremely high Buybox competition with ${competition.sellerCount} sellers. Intense price undercutting possible.`
            });
        } else if (competition.sellerCount > 3) {
            insights.push({
                type: 'warning',
                message: `🏪 Moderate seller activity (${competition.sellerCount} sellers). Typical of wholesale or popular retail arbitrage.`
            });
        }

        // ROI insight
        if (profit.roi >= 100) {
            insights.push({
                type: 'success',
                message: `🚀 Strong ${profit.roi.toFixed(0)}% ROI potential`
            });
        }

        return insights;
    }
}

// IntelligenceEngine is now available globally
