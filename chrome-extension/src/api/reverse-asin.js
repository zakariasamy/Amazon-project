// Reverse ASIN - Keyword Discovery Module
// Finds keywords that an ASIN ranks for by searching and detecting positions

class ReverseAsin {
    constructor(marketplace = 'amazon.com') {
        this.marketplace = this.normalizeMarketplace(marketplace);
        this.tld = this.getTLD(this.marketplace);
        this.discoveredKeywords = new Map();
        this.isRunning = false;
        this.onProgress = null; // Callback for progress updates
        this.productsFetchLimit = 20; // Default, can be overridden by backend settings
        // Backend URL resolution (avoid hardcoded URLs drifting across modules)
        this.backendBaseUrlFallback = 'http://127.0.0.1:8000';
    }

    parseBooleanSetting(value, defaultValue = false) {
        if (value === undefined || value === null) return defaultValue;
        if (typeof value === 'boolean') return value;
        if (typeof value === 'number') return value !== 0;

        const s = String(value).trim().toLowerCase();
        if (s === '1' || s === 'true' || s === 'yes' || s === 'on') return true;
        if (s === '0' || s === 'false' || s === 'no' || s === 'off' || s === '') return false;
        return defaultValue;
    }

    getBackendBaseUrl() {
        try {
            if (typeof window !== 'undefined') {
                if (window.ApiClient && window.ApiClient.baseUrl) return window.ApiClient.baseUrl;
                if (window.API_CONFIG && window.API_CONFIG.baseUrl) return window.API_CONFIG.baseUrl;
            }
        } catch (e) {
            // ignore
        }
        return this.backendBaseUrlFallback;
    }

    normalizeMarketplace(marketplace) {
        const raw = (marketplace || '').toString().trim().toLowerCase();
        if (!raw) return 'amazon.com';

        const withoutProto = raw.replace(/^https?:\/\//, '');
        const hostname = withoutProto.split('/')[0] || withoutProto;
        const cleaned = hostname.replace(/^(www\.|smile\.|m\.)/i, '');

        if (cleaned.endsWith('amazon.co.uk')) return 'amazon.co.uk';
        if (cleaned.endsWith('amazon.com')) return 'amazon.com';
        if (cleaned.endsWith('amazon.eg')) return 'amazon.eg';
        if (cleaned.endsWith('amazon.de')) return 'amazon.de';

        return cleaned;
    }

    getTLD(marketplace) {
        const tldMap = {
            'amazon.com': 'com',
            'amazon.eg': 'eg',
            'amazon.co.uk': 'co.uk',
            'amazon.de': 'de'
        };
        return tldMap[marketplace] || 'com';
    }

    /**
     * Main entry point: Discover keywords for an ASIN
     * Enhanced with carousel-based keyword extraction
     * @param {string} asin - Target ASIN
     * @param {Document} productPageDoc - DOM of the product page (optional, will fetch if not provided)
     * @param {Function} onProgress - Progress callback (stage, current, total, message)
     */
    parseAsinFromUrl(url) {
        if (!url) return null;
        const match = url.match(/(?:dp|gp\/product)\/([A-Z0-9]{10})/i);
        return match ? match[1].toUpperCase() : null;
    }

    /**
     * Main entry point: Discover keywords for an ASIN
     * Enhanced with carousel-based keyword extraction
     * @param {string} asin - Target ASIN
     * @param {Document} productPageDoc - DOM of the product page (optional, will fetch if not provided)
     * @param {Function} onProgress - Progress callback (stage, current, total, message)
     */
    async discoverKeywords(asin, productPageDoc = null, onProgress = null) {
        if (this.isRunning) {
            return { error: 'Discovery already in progress' };
        }

        this.isRunning = true;
        this.onProgress = onProgress;
        this.discoveredKeywords.clear();

        try {
            // Fetch all configuration from backend first (same settings as Search Page)
            try {
                const baseUrl = this.getBackendBaseUrl();
                console.log('[Reverse ASIN] Using backend baseUrl:', baseUrl);
                const configResponse = await fetch(`${baseUrl}/api/settings?_t=${Date.now()}`);
                if (configResponse.ok) {
                    const configData = await configResponse.json();
                    const settings = configData.settings || {};

                    // Reverse ASIN specific settings (aligned with Competitor Keyword Analyzer limit in Admin Settings)
                    this.bsrProductsLimit = (settings.reverse_asin_products_limit !== undefined && settings.reverse_asin_products_limit !== '')
                        ? parseInt(settings.reverse_asin_products_limit)
                        : (parseInt(settings.cerebro_bsr_products_limit) || 20);
                    this.bsrParallelRequests = parseInt(settings.reverse_asin_bsr_parallel_requests) || 3;
                    this.bsrDelayMs = parseInt(settings.reverse_asin_bsr_delay_ms) || 500;
                    this.maxKeywords = parseInt(settings.reverse_asin_keywords_limit) || 50;
                    this.searchDelayMs = parseInt(settings.reverse_asin_search_delay_ms) || 1500;
                    this.backendBatchSize = parseInt(settings.reverse_asin_backend_batch_size) || 5;
                    
                    this.testModeEnabled = this.parseBooleanSetting(settings.test_mode_enabled, false);
                    this.testModeKeyword = settings.test_mode_keyword || 'portal scale body';
                    this.testModeProductUrl = settings.test_mode_product_url || '';

                    console.log('[Reverse ASIN] Settings loaded:', {
                        bsrProductsLimit: this.bsrProductsLimit,
                        bsrParallelRequests: this.bsrParallelRequests,
                        bsrDelayMs: this.bsrDelayMs,
                        maxKeywords: this.maxKeywords,
                        searchDelayMs: this.searchDelayMs,
                        backendBatchSize: this.backendBatchSize,
                        testModeEnabled: this.testModeEnabled,
                        testModeKeyword: this.testModeKeyword
                    });
                }
            } catch (e) {
                console.warn('Failed to fetch settings, using defaults');
                this.bsrProductsLimit = 20;
                this.bsrParallelRequests = 3;
                this.bsrDelayMs = 500;
                this.maxKeywords = 50;
                this.searchDelayMs = 1500;
                this.backendBatchSize = 5;
                this.testModeEnabled = false;
                this.testModeKeyword = 'portal scale body';
                this.testModeProductUrl = '';
            }

            // Override ASIN in Test Mode
            if (this.testModeEnabled && this.testModeProductUrl) {
                const testAsin = this.parseAsinFromUrl(this.testModeProductUrl);
                if (testAsin) {
                    console.log(`[Reverse ASIN] Test Mode Active: overriding target ASIN ${asin} with test ASIN ${testAsin}`);
                    asin = testAsin;
                }
            }

            // Check for cached results for today first (skip in Test Mode)
            if (!this.testModeEnabled) {
                this.updateProgress('cache_check', 0, 1, 'Checking for cached results...');
                try {
                    const baseUrl = this.getBackendBaseUrl();
                    const historyResponse = await fetch(`${baseUrl}/api/reverse-asin/${asin}/history?limit=1&marketplace=${this.marketplace}`);
                    if (historyResponse.ok) {
                        const historyData = await historyResponse.json();
                        if (historyData.success && historyData.history && historyData.history.length > 0) {
                            const lastResult = historyData.history[0];
                            const createdDate = new Date(lastResult.created_at);
                            const today = new Date();
                            
                            // Check if it's from today (same calendar day)
                            if (createdDate.toDateString() === today.toDateString()) {
                                const cachedKeywords = lastResult.keywords || [];
                                const foundKeywords = cachedKeywords.filter(k => k && k.found);
                                const looksLikeTestModeCache =
                                    (String(lastResult.source || '').toLowerCase().includes('test')) ||
                                    (foundKeywords.length === 1 && (foundKeywords[0].keyword || '').trim().toLowerCase() === (this.testModeKeyword || '').trim().toLowerCase());

                                if (looksLikeTestModeCache) {
                                    console.log('[Reverse ASIN] Cache looks like Test Mode output; ignoring cache and recomputing.', {
                                        asin,
                                        source: lastResult.source,
                                        keywordsFound: lastResult.keywords_found,
                                        foundKeywords: foundKeywords.map(k => k.keyword)
                                    });
                                } else {
                                    console.log(`[Reverse ASIN] Reusing cached results from today for ${asin}`);
                                    this.isRunning = false;
                                    this.updateProgress('complete', lastResult.keywords_tested, lastResult.keywords_tested, 'Loaded from cache!');

                                    return {
                                        asin: lastResult.asin,
                                        productInfo: {
                                            title: lastResult.title,
                                            category: lastResult.category
                                        },
                                        keywordsTested: lastResult.keywords_tested,
                                        keywordsFound: lastResult.keywords_found,
                                        keywords: cachedKeywords.filter(k => k.found),
                                        allKeywords: cachedKeywords,
                                        source: lastResult.source || 'cache',
                                        isCached: true,
                                        test_mode_enabled: this.testModeEnabled
                                    };
                                }
                            }
                        }
                    }
                } catch (e) {
                    console.warn('Failed to check history cache:', e);
                }
            } else {
                console.log('[Reverse ASIN] Test Mode Active: Bypassing history cache!');
            }

            // =========================
            // PHASE 1: Product Context
            // =========================
            this.updateProgress('context', 0, 1, 'Extracting product context...');

            let doc = productPageDoc;
            let productInfo = null;
            let candidateKeywords = [];

            // If no document provided, try to fetch the product page
            if (!doc) {
                const productUrl = `https://www.amazon.${this.tld}/dp/${asin}`;
                try {
                    const response = await fetch(productUrl);
                    const html = await response.text();
                    const parser = new DOMParser();
                    doc = parser.parseFromString(html, 'text/html');
                } catch (e) {
                    console.warn('Could not fetch product page:', e);
                }
            }

            // Use ProductParser if document is available
            // Use ProductParser if document is available
            let dominantWord = null;
            if (doc && typeof ProductParser !== 'undefined') {
                const productParser = new ProductParser(doc);
                const extracted = productParser.getKeywordCandidatesForReverseAsin();

                productInfo = extracted.productInfo;
                candidateKeywords = extracted.candidates;
                dominantWord = extracted.dominantWord;

                console.log(`Phase 1: Extracted ${candidateKeywords.length} candidates from ${extracted.source}`);
                console.log('Product Info:', productInfo);
            }

            // =========================
            // PHASE 2: Amazon Suggestions API (EXPANDED - PARALLEL BATCHES)
            // =========================
            this.updateProgress('suggestions', 0, 1, 'Getting Amazon suggestions...');

            // Use top 15 n-grams as seeds for Amazon Suggestions API
            const seedKeywords = candidateKeywords.slice(0, 15);
            console.log('Seed keywords for suggestions:', seedKeywords);

            // Fetch suggestions in parallel batches of 5 to optimize speed while avoiding rate limits
            const amazonSuggestions = [];
            const batchSize = 5;

            for (let i = 0; i < seedKeywords.length; i += batchSize) {
                const batch = seedKeywords.slice(i, i + batchSize);
                this.updateProgress('suggestions', Math.min(i + batchSize, seedKeywords.length), seedKeywords.length,
                    `Fetching suggestions (batch ${Math.floor(i / batchSize) + 1}/${Math.ceil(seedKeywords.length / batchSize)})...`);

                // Run batch in parallel
                const batchPromises = batch.map(seed =>
                    this.getAmazonSuggestions(seed).catch(e => {
                        console.warn('Suggestion fetch failed for:', seed);
                        return [];
                    })
                );

                const batchResults = await Promise.all(batchPromises);
                batchResults.forEach((suggestions, idx) => {
                    if (suggestions.length > 0) {
                        console.log(`Got ${suggestions.length} suggestions for "${batch[idx]}"`);
                        // Process suggestions: split by separators to avoid "keyword - description" artifacts
                        const processed = [];
                        suggestions.forEach(sug => {
                            // Split by - (hyphen), | (pipe), , (comma)
                            const parts = sug.split(/[-|,]+/).map(p => p.trim());
                            parts.forEach(p => {
                                if (p.length > 2) processed.push(p);
                            });
                        });
                        amazonSuggestions.push(...processed);
                    }
                });

                // Rate limit between batches (not between individual requests)
                if (i + batchSize < seedKeywords.length) {
                    await this.delay(400);
                }
            }

            // =========================
            // PHASE 2.5: Search with product title to find similar products
            // =========================
            let titleSearchKeywords = [];
            if (productInfo?.title) {
                this.updateProgress('suggestions', 0, 1, 'Searching for similar products...');
                try {
                    titleSearchKeywords = await this.extractKeywordsFromTitleSearch(productInfo.title);
                    console.log(`Phase 2.5: Got ${titleSearchKeywords.length} keywords from title search`);
                } catch (e) {
                    console.warn('Title search failed:', e);
                }
            }

            // PRIORITY ORDER: Amazon Suggestions FIRST, then title search, then local extraction
            const prioritizedKeywords = [
                ...amazonSuggestions,        // Priority 1: Amazon API suggestions
                ...titleSearchKeywords,      // Priority 2: Keywords from similar products
                ...candidateKeywords          // Priority 3: Local n-gram extraction
            ];

            // Deduplicate while preserving priority order
            candidateKeywords = [...new Set(prioritizedKeywords)];

            // Post-process: Clean artifacts (e.g. ".05", standalone numbers)
            candidateKeywords = candidateKeywords
                .map(kw => {
                    let cleaned = kw.toLowerCase().trim();
                    // Remove trailing punctuation/symbols
                    cleaned = cleaned.replace(/[^\w\s]+$/, '');
                    // Remove leading punctuation/symbols
                    cleaned = cleaned.replace(/^[^\w\s]+/, '');
                    return cleaned;
                })
                .filter(kw => {
                    if (kw.length < 3) return false;
                    // Remove pure numbers
                    if (/^[\d\.,]+$/.test(kw)) return false;
                    // Remove keywords ending in dangling decimals like "scale .05"
                    if (/\s\.\d+$/.test(kw)) return false;

                    // Enforce dominant word relevance (e.g. must contain "scale")
                    if (dominantWord) {
                        const lower = kw.toLowerCase();
                        // Allow singular or plural form
                        if (!lower.includes(dominantWord) && !lower.includes(dominantWord + 's')) {
                            return false;
                        }
                    }

                    return true;
                });

            console.log(`Phase 2: Total ${candidateKeywords.length} unique candidates`);

            // =========================
            // PHASE 3: Reverse Check (PARALLEL BATCHES for speed)
            // =========================
            // Apply keywords limit from settings
            const maxKw = this.maxKeywords || 50;
            let keywordsToTest = candidateKeywords.slice(0, maxKw);

            if (this.testModeEnabled) {
                keywordsToTest = [this.testModeKeyword];
                console.log(`[Reverse ASIN] Test Mode Active: FORCE testing only one keyword: "${keywordsToTest[0]}"`);
            } else if (candidateKeywords.length > maxKw) {
                console.log(`[Reverse ASIN] Limiting keywords from ${candidateKeywords.length} to ${maxKw} (setting: reverse_asin_keywords_limit)`);
            }

            const results = [];
            const allKeywordResults = []; // Store ALL results for comprehensive display

            // Smart rate limiting configuration
            const checkBatchSize = 5; // 5 parallel requests
            let consecutiveBatches = 0;
            let captchaEncountered = false;

            for (let i = 0; i < keywordsToTest.length; i += checkBatchSize) {
                if (!this.isRunning) break;

                const batch = keywordsToTest.slice(i, i + checkBatchSize);
                this.updateProgress('checking', Math.min(i + checkBatchSize, keywordsToTest.length), keywordsToTest.length,
                    `Checking keywords ${i + 1}-${Math.min(i + checkBatchSize, keywordsToTest.length)}/${keywordsToTest.length}...`);

                // Run scraping in parallel (NO backend calls yet)
                const scrapePromises = batch.map(keyword =>
                    this.searchForAsin(keyword, asin)
                );

                const scrapeResults = await Promise.all(scrapePromises);

                // Separate valid scrapes from failed ones (e.g. captcha)
                const successfulScrapes = scrapeResults.filter(r => r !== null && r.products.length > 0);
                const failedScrapes = scrapeResults.filter(r => r === null);

                let batchHadIssues = failedScrapes.length > 0;

                // Batch process successful scrapes with backend
                if (successfulScrapes.length > 0) {
                    try {
                        const payload = {
                            items: successfulScrapes.map(s => ({
                                keyword: s.keyword,
                                marketplace: this.marketplace,
                                products: s.products,
                                prefer_cached_volume: !this.testModeEnabled
                            }))
                        };

                        const baseUrl = this.getBackendBaseUrl();
                        const response = await fetch(`${baseUrl}/api/search-volume/batch-estimate`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Tool-Source': 'reverse_asin' },
                            body: JSON.stringify(payload)
                        });

                        if (response.ok) {
                            const data = await response.json();

                            if (data.success && data.results) {
                                // Process each result from backend
                                data.results.forEach(est => {
                                    // Find original scrape info
                                    const scrape = successfulScrapes.find(s => s.keyword === est.keyword);
                                    if (!scrape) return;

                                    const ranking = {
                                        keyword: est.keyword,
                                        position: scrape.targetPosition, // From scrape
                                        page: scrape.targetPage,
                                        isSponsored: scrape.targetSponsored,

                                        // Backend enriched data
                                        estimatedVolume: est.search_volume?.estimated,
                                        totalSales: est.search_volume?.sales_metrics?.total_monthly_sales,
                                        demandLevel: est.search_volume?.demand_level,
                                        difficultyScore: est.difficulty?.score,
                                        difficultyLevel: est.difficulty?.level,
                                        adDensity: est.ad_metrics?.density_percent,
                                        sponsoredCount: est.ad_metrics?.sponsored_count,
                                        organicCount: est.ad_metrics?.organic_count,
                                        avgPrice: est.product_stats?.average_price,
                                        avgReviews: est.product_stats?.average_reviews,
                                        avgBsr: est.product_stats?.average_bsr,
                                        productsAnalyzed: est.products_analyzed
                                    };

                                    const keywordResult = {
                                        keyword: ranking.keyword,
                                        position: ranking.position,
                                        page: ranking.page,
                                        isSponsored: ranking.isSponsored,
                                        found: !!ranking.position,
                                        estimated_volume: ranking.estimatedVolume,
                                        total_sales: ranking.totalSales,
                                        demand_level: ranking.demandLevel,
                                        difficulty_score: ranking.difficultyScore,
                                        difficulty_level: ranking.difficultyLevel,
                                        ad_density: ranking.adDensity,
                                        sponsored_count: ranking.sponsoredCount,
                                        organic_count: ranking.organicCount,
                                        avg_price: ranking.avgPrice,
                                        avg_reviews: ranking.avgReviews,
                                        avg_bsr: ranking.avgBsr,
                                        products_analyzed: ranking.productsAnalyzed,
                                        discoveredAt: new Date().toISOString()
                                    };

                                    allKeywordResults.push(keywordResult);

                                    if (ranking.position) {
                                        results.push(keywordResult);
                                        this.discoveredKeywords.set(ranking.keyword, ranking);
                                        // We can save individual rankings if needed, but batch save at end is better
                                    }
                                });
                            }
                        } else {
                            console.warn(`Batch backend error: ${response.status}`);
                            batchHadIssues = true;
                            // Fallback: Add scraped but un-enriched results? 
                            // Or just retry? For now, we accept meaningful stats might be missing
                        }
                    } catch (e) {
                        console.error("Batch processing failed", e);
                        batchHadIssues = true;
                    }
                }

                // Handle failed scrapes (captcha/network error)
                // We add them as "not found" or "error" entries
                batch.forEach(kw => {
                    // Check if kw was processed successfully
                    const processed = allKeywordResults.some(r => r.keyword === kw);
                    if (!processed) {
                        allKeywordResults.push({
                            keyword: kw,
                            found: false,
                            error: 'Extraction failed',
                            discoveredAt: new Date().toISOString()
                        });
                        batchHadIssues = true;
                    }
                });

                consecutiveBatches++;

                // Smart adaptive rate limiting between batches
                if (i + checkBatchSize < keywordsToTest.length) {
                    let delayMs;

                    if (batchHadIssues || captchaEncountered) {
                        // If issues detected, slow down significantly
                        delayMs = 3000 + Math.random() * 2000; // 3-5 seconds
                        captchaEncountered = true;
                        console.log('Rate limit detected, slowing down...');
                    } else if (consecutiveBatches % 5 === 0) {
                        delayMs = 1500 + Math.random() * 1000;
                    } else {
                        delayMs = 800 + Math.random() * 400;
                    }

                    await this.delay(delayMs);
                }
            }

            this.isRunning = false;
            this.updateProgress('complete', keywordsToTest.length, keywordsToTest.length, 'Discovery complete!');

            const finalResult = {
                asin,
                productInfo,
                keywordsTested: keywordsToTest.length,
                keywordsFound: results.length,
                keywords: results.sort((a, b) => a.position - b.position),
                // ALL keywords with their status (found/not found)
                allKeywords: allKeywordResults.sort((a, b) => {
                    // Sort: found first (by position), then not found
                    if (a.found && b.found) return a.position - b.position;
                    if (a.found) return -1;
                    if (b.found) return 1;
                    return 0;
                }),
                source: `${productInfo ? 'carousel_analysis' : 'title_fallback'}${this.testModeEnabled ? '_test_mode' : ''}`,
                test_mode_enabled: this.testModeEnabled
            };

            // Save complete results to backend for history and analysis
            await this.saveResultsToBackend(finalResult);

            return finalResult;

        } catch (error) {
            this.isRunning = false;
            console.error('Reverse ASIN error:', error);
            return {
                error: error?.message || 'Reverse ASIN error',
                asin,
                productInfo: productInfo || null,
                keywordsTested: 0,
                keywordsFound: 0,
                keywords: [],
                allKeywords: Array.from(this.discoveredKeywords.entries()).map(([k, v]) => ({ keyword: k, ...v })),
                source: 'error'
            };
        }
    }

    updateProgress(stage, current, total, message) {
        if (this.onProgress) {
            this.onProgress(stage, current, total, message);
        }
        console.log(`[${stage}] ${current}/${total}: ${message}`);
    }

    /**
     * Extract 2-3 good seed keywords from product info for Amazon Suggestions API
     * These should be broad category + type combinations
     */
    extractSeedKeywords(productInfo) {
        const title = (productInfo?.title || '').toLowerCase();
        const seeds = [];

        // Common product type patterns to look for
        const productTypes = [
            'scale', 'scales', 'weighing', 'balance',
            'blender', 'mixer', 'grinder', 'chopper',
            'oven', 'microwave', 'toaster', 'kettle',
            'pan', 'pot', 'skillet', 'fryer',
            'knife', 'cutter', 'peeler', 'slicer',
            'board', 'rack', 'holder', 'organizer',
            'container', 'jar', 'box', 'bag',
            'timer', 'thermometer', 'meter'
        ];

        // Common category modifiers
        const categoryModifiers = [
            'kitchen', 'digital', 'electronic', 'food', 'cooking',
            'weight', 'body', 'bathroom', 'portable',
            'stainless', 'steel', 'glass', 'plastic'
        ];

        // Find product type in title
        let foundType = null;
        for (const type of productTypes) {
            if (title.includes(type)) {
                foundType = type;
                break;
            }
        }

        // Find category modifiers in title
        const foundModifiers = categoryModifiers.filter(mod => title.includes(mod));

        if (foundType) {
            // Create seed combinations
            if (foundModifiers.length >= 2) {
                // e.g., "digital kitchen scale"
                seeds.push(`${foundModifiers[0]} ${foundModifiers[1]} ${foundType}`);
            }
            if (foundModifiers.length >= 1) {
                // e.g., "kitchen scale", "digital scale"
                seeds.push(`${foundModifiers[0]} ${foundType}`);
                if (foundModifiers.length >= 2) {
                    seeds.push(`${foundModifiers[1]} ${foundType}`);
                }
            }
            // Just the product type
            if (seeds.length === 0) {
                seeds.push(foundType);
            }
        }

        // Fallback: use first 2-3 significant words from title
        if (seeds.length === 0) {
            const stopWords = new Set(['a', 'an', 'the', 'and', 'or', 'for', 'with', 'by', 'in', 'on', 'to', 'of']);
            const words = title
                .replace(/[^\w\s]/g, ' ')
                .split(/\s+/)
                .filter(w => w.length > 2 && !stopWords.has(w))
                .slice(0, 3);

            if (words.length >= 2) {
                seeds.push(words.slice(0, 2).join(' '));
                seeds.push(words.slice(0, 3).join(' '));
            } else if (words.length === 1) {
                seeds.push(words[0]);
            }
        }

        // Limit to 3 seeds max and deduplicate
        return [...new Set(seeds)].slice(0, 3);
    }

    async submitRankingToBackend(asin, keyword, ranking) {
        try {
            // Using fetch directly to ensure it works even if ApiClient not loaded
            const baseUrl = this.getBackendBaseUrl();
            await fetch(`${baseUrl}/api/reverse-asin/ranking`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                    // Add auth token if available, but for now we might skipping auth for simplicity or need to get it from storage
                },
                body: JSON.stringify({
                    asin,
                    marketplace: this.marketplace,
                    keyword,
                    position: ranking.position,
                    isSponsored: ranking.isSponsored,
                    page: ranking.page
                })
            });
            console.log(`Submitted ranking for ${keyword}`);
        } catch (e) {
            console.error('Failed to save ranking to backend', e);
        }
    }

    /**
     * Save complete reverse ASIN results to backend for history
     * Also enriches results with search volume analysis
     */
    async saveResultsToBackend(result) {
        try {
            // First save the raw results
            const baseUrl = this.getBackendBaseUrl();
            await fetch(`${baseUrl}/api/reverse-asin/results`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    asin: result.asin,
                    marketplace: this.marketplace,
                    title: result.productInfo?.title || null,
                    category: result.productInfo?.category || null,
                    keywords_tested: result.keywordsTested || 0,
                    keywords_found: result.keywordsFound || 0,
                    keywords: result.allKeywords || result.keywords || [],
                    source: result.source || 'unknown'
                })
            });
            console.log(`Saved complete reverse ASIN results for ${result.asin}`);
        } catch (e) {
            console.error('Failed to save results to backend', e);
        }
    }

    /**
     * Search Amazon for a keyword and check if ASIN appears
     * @param {string} keyword 
     * @param {string} targetAsin 
     * @returns {Promise<object|null>}
     */
    async searchForAsin(keyword, targetAsin) {
        try {
            // Add jitter to avoid pattern detection
            await this.delay(Math.random() * 1000);

            const searchUrl = `https://www.amazon.${this.tld}/s?k=${encodeURIComponent(keyword)}`;
            console.log(`Checking rank for "${keyword}"...`);

            const response = await fetch(searchUrl, {
                credentials: 'include',
                headers: {
                    'Accept': 'text/html,application/xhtml+xml',
                    'Accept-Language': 'en-US,en;q=0.9',
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                }
            });

            if (!response.ok) {
                if (response.status === 503) {
                    throw new Error('Amazon Service Unavailable (503)');
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const html = await response.text();

            // Check for captcha
            if (html.includes('api-services-support@amazon.com') || html.includes('Type the characters you see in this image')) {
                console.warn('Captcha detected!');
                return null; // Signal retry/backoff
            }

            // Parse the HTML to find ASINs
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Normalize target ASIN for comparison (uppercase)
            const normalizedTarget = targetAsin.toUpperCase().trim();

            // Use SerpParser for consistent product extraction (same as search page)
            let productsList = [];
            let targetPosition = null;
            let targetSponsored = false;
            let targetPage = 1; // Default to page 1

            try {
                if (typeof SerpParser !== 'undefined') {
                    const serpParser = new SerpParser(doc);
                    const products = serpParser.extractProducts();

                    // Find target position first
                    for (const product of products) {
                        if (product.asin.toUpperCase() === normalizedTarget) {
                            targetPosition = product.position;
                            targetSponsored = product.is_sponsored;
                            break;
                        }
                    }

                    // Enrich products with BSR (same as Search Page)
                    // This fetches individual product pages to get fresh BSR
                    const enrichOptions = {
                        limit: this.bsrProductsLimit || 20,
                        batchSize: this.bsrParallelRequests || 3,
                        batchDelay: this.bsrDelayMs || 500
                    };
                    console.log(`Enriching ${Math.min(products.length, enrichOptions.limit)} products with BSR for "${keyword}"...`);
                    productsList = await serpParser.enrichWithBSR(products, enrichOptions);

                } else {
                    console.warn('SerpParser not available, using fallback extraction');
                    productsList = this.fallbackExtractProducts(doc, normalizedTarget);

                    for (const p of productsList) {
                        if (p.asin === normalizedTarget) {
                            targetPosition = p.position;
                            targetSponsored = p.is_sponsored;
                            break;
                        }
                    }
                }
            } catch (e) {
                console.warn('SerpParser extraction failed, using fallback:', e);
                productsList = this.fallbackExtractProducts(doc, normalizedTarget);
            }

            // Limit to top products matching the BSR limit (default 20) for perfect consistency with estimation
            const limit = this.bsrProductsLimit || 20;
            productsList = productsList.slice(0, limit);

            // Return scrape result for batch processing
            return {
                keyword,
                products: productsList,
                targetPosition,
                targetSponsored,
                targetPage
            };

        } catch (error) {
            console.error('Search error for keyword:', keyword, error);
            return null;
        }
    }

    /**
     * Search Amazon using the product title to find similar products and extract their keywords
     * This simulates "Customers who viewed this item also viewed" by finding what shows up for the same search
     * @param {string} title 
     * @returns {Promise<string[]>}
     */
    async extractKeywordsFromTitleSearch(title) {
        try {
            // Create a search query from the first few words of the title
            // Remove special characters and extra spaces
            const cleanTitle = title.replace(/[^\w\s]/gi, '').replace(/\s+/g, ' ').trim();
            const words = cleanTitle.split(' ');
            const queryWords = words.slice(0, 6); // Use first 6 words
            const query = queryWords.join(' ');

            const searchUrl = `https://www.amazon.${this.tld}/s?k=${encodeURIComponent(query)}`;
            console.log(`Phase 2.5: Searching similar products with query: "${query}"`);

            const response = await fetch(searchUrl, {
                credentials: 'include',
                headers: {
                    'Accept': 'text/html,application/xhtml+xml',
                    'Accept-Language': 'en-US,en;q=0.9'
                }
            });

            if (!response.ok) return [];

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Extract titles from organic search results
            const resultTitles = [];
            const resultItems = doc.querySelectorAll('div.s-result-item[data-component-type="s-search-result"] h2 > a > span');

            resultItems.forEach((span, index) => {
                if (index < 10) { // Top 10 results
                    resultTitles.push(span.textContent.trim());
                }
            });

            console.log(`Found ${resultTitles.length} similar product titles`);

            // Use ProductParser logic to extract keywords from these titles if available
            // leveraging the same n-gram logic we just fixed
            let allKeywords = [];

            if (typeof ProductParser !== 'undefined') {
                // Initialize a dummy parser just to access helper methods
                // We're essentially doing what extractKeywordCandidates does but for explicit titles
                const tempParser = new ProductParser(doc);

                resultTitles.forEach(t => {
                    // Use segmented generation to avoid phrases crossing separators
                    const ngrams = tempParser.generateNgramsFromTitle(t);
                    allKeywords.push(...ngrams);
                });
            } else {
                // Fallback simple extraction
                resultTitles.forEach(t => {
                    const w = t.toLowerCase().replace(/[^\w\s]/g, '').split(' ');
                    // Simple 2-grams
                    for (let i = 0; i < w.length - 1; i++) {
                        if (w[i].length > 2 && w[i + 1].length > 2) {
                            allKeywords.push(w[i] + ' ' + w[i + 1]);
                        }
                    }
                });
            }

            // Frequency analysis
            const keywordCounts = {};
            allKeywords.forEach(k => {
                keywordCounts[k] = (keywordCounts[k] || 0) + 1;
            });

            // Return keywords appearing at least twice, or top 20 by frequency
            return Object.entries(keywordCounts)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 15) // Top 15 keywords
                .map(e => e[0]);

        } catch (e) {
            console.error('Error in title search extraction:', e);
            return [];
        }
    }

    /**
     * Get competitor keywords (keywords competitors rank for)
     * @param {string[]} competitorAsins 
     * @returns {Promise<object>}
     */
    async getCompetitorKeywords(competitorAsins) {
        const allKeywords = new Map();

        for (const asin of competitorAsins.slice(0, 5)) {
            // Get keywords from backend
            if (typeof ApiClient !== 'undefined') {
                try {
                    const data = await ApiClient.getReverseAsinKeywords(asin);
                    if (data.keywords) {
                        data.keywords.forEach(kw => {
                            if (!allKeywords.has(kw.keyword)) {
                                allKeywords.set(kw.keyword, {
                                    keyword: kw.keyword,
                                    competitorCount: 1,
                                    avgPosition: kw.position
                                });
                            } else {
                                const existing = allKeywords.get(kw.keyword);
                                existing.competitorCount++;
                                existing.avgPosition = (existing.avgPosition + kw.position) / 2;
                            }
                        });
                    }
                } catch (e) {
                    console.log('Could not get competitor keywords');
                }
            }
        }

        return {
            keywords: Array.from(allKeywords.values())
                .sort((a, b) => b.competitorCount - a.competitorCount),
            totalKeywords: allKeywords.size
        };
    }

    /**
     * Extract monthly sales from "X+ bought in past month" badge
     * Same logic as SerpParser.extractMonthlySales for consistency
     */
    extractMonthlySales(card) {
        // First try to find specific badge elements (more reliable)
        const badgeSelectors = [
            '.a-size-base.a-color-secondary:not(.a-text-strike)',
            '[data-component-type="s-product-badges"] span',
            '.a-row.a-size-base span.a-color-secondary',
            '.s-underline-text'
        ];

        for (const selector of badgeSelectors) {
            const badges = card.querySelectorAll(selector);
            for (const badge of badges) {
                const text = badge.textContent?.trim() || '';

                // Check if this text contains "bought" or Arabic equivalent
                if (text.match(/bought|purchased|تم شراء|اشتروا/i)) {
                    const sales = this.parseSalesFromText(text);
                    if (sales !== null) {
                        console.log(`ReverseASIN: Found sales badge: "${text}" -> ${sales}`);
                        return sales;
                    }
                }
            }
        }

        // Fallback: Search card text more carefully (only specific patterns)
        const spans = card.querySelectorAll('span.a-size-base');
        for (const span of spans) {
            const text = span.textContent?.trim() || '';
            // Only match if the ENTIRE span content is about buying
            if (text.match(/^\d+.*?(bought|purchased|تم شراء)/i) ||
                text.match(/^.*?(bought|purchased|تم شراء).*?\d+/i)) {
                const sales = this.parseSalesFromText(text);
                if (sales !== null) {
                    console.log(`ReverseASIN: Found sales text: "${text}" -> ${sales}`);
                    return sales;
                }
            }
        }

        return null;
    }

    /**
     * Parse actual sales number from text like "1K+ bought" or "500+ bought"
     */
    parseSalesFromText(text) {
        // Remove commas and normalize
        const normalized = text.replace(/,/g, '');

        // Pattern: Number with K suffix (1K, 2K, etc)
        const kMatch = normalized.match(/(\d+)K\+?\s*(?:bought|purchased|تم شراء)/i);
        if (kMatch) {
            return parseInt(kMatch[1]) * 1000;
        }

        // Pattern: Just a number (50, 100, 500, etc)
        const numMatch = normalized.match(/(\d+)\+?\s*(?:bought|purchased|تم شراء)/i);
        if (numMatch) {
            return parseInt(numMatch[1]);
        }

        // Arabic patterns
        const arabicMatch = normalized.match(/تم شراء\s*\+?(\d+)/);
        if (arabicMatch) {
            return parseInt(arabicMatch[1]);
        }

        // Arabic thousands
        const arabicKMatch = normalized.match(/(\d+)\s*آلاف/);
        if (arabicKMatch) {
            return parseInt(arabicKMatch[1]) * 1000;
        }

        return null;
    }

    /**
     * Fallback product extraction when SerpParser is not available
     * Uses same selectors and logic, just inline
     */
    fallbackExtractProducts(doc) {
        const productsList = [];
        const seenAsins = new Set();

        // Try multiple selector strategies
        let searchResults = doc.querySelectorAll(
            'div.s-result-item[data-asin][role="listitem"], ' +
            'div[data-component-type="s-search-result"][data-asin]'
        );

        if (searchResults.length === 0) {
            searchResults = doc.querySelectorAll(
                'div.s-result-item.s-asin[data-asin], ' +
                '[data-cel-widget^="search_result_"][data-asin]'
            );
        }

        let position = 0;

        for (const result of searchResults) {
            let asin = result.getAttribute('data-asin');
            if (!asin || asin.length < 10) continue;

            asin = asin.toUpperCase().trim();
            if (seenAsins.has(asin)) continue;
            seenAsins.add(asin);

            position++;

            const resultText = result.textContent || '';
            const isSponsored =
                result.querySelector('[data-component-type="sp-sponsored-result"]') !== null ||
                resultText.toLowerCase().includes('sponsored');

            // Extract price
            let price = null;
            const priceWhole = result.querySelector('.a-price .a-price-whole');
            if (priceWhole) {
                const whole = priceWhole.textContent.replace(/[^\d]/g, '');
                const fraction = result.querySelector('.a-price .a-price-fraction')?.textContent?.replace(/[^\d]/g, '') || '00';
                price = parseFloat(`${whole}.${fraction}`);
            }

            // Extract rating
            let rating = null;
            const ratingEl = result.querySelector('[aria-label*="out of 5"]');
            if (ratingEl) {
                const match = (ratingEl.getAttribute('aria-label') || '').match(/([\d.]+)/);
                if (match) rating = parseFloat(match[1]);
            }

            // Extract reviews
            let reviews = null;
            const reviewLink = result.querySelector('a[aria-label*="rating"], a[aria-label*="review"]');
            if (reviewLink) {
                const match = (reviewLink.getAttribute('aria-label') || '').match(/(\d+)/);
                if (match) reviews = parseInt(match[1]);
            }

            // Skip products without price (unavailable/out of stock)
            if (!price || price <= 0) {
                console.log(`Fallback: Skipping unavailable product ${asin} (no price)`);
                position--; // Undo position increment for skipped products
                continue;
            }

            productsList.push({
                asin,
                position,
                monthly_sales: this.extractMonthlySales(result),
                is_sponsored: isSponsored,
                price,
                rating,
                reviews
            });

            if (position >= 60) break;
        }

        return productsList;
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    stop() {
        this.isRunning = false;
    }

    getDiscoveredKeywords() {
        return Array.from(this.discoveredKeywords.entries()).map(([keyword, data]) => ({
            keyword,
            ...data
        }));
    }

    async getAmazonSuggestions(prefix) {
        try {
            const domain = this.marketplace.replace('amazon.', '');

            // Marketplace IDs - these are required for the suggestions API
            const marketplaceIds = {
                'eg': { mid: 'ARBP9OOSHTCHU', lop: 'en_AE' },
                'com': { mid: 'ATVPDKIKX0DER', lop: 'en_US' },
                'co.uk': { mid: 'A1F83G8C2ARO7P', lop: 'en_GB' },
                'de': { mid: 'A1PA6795UKMFR9', lop: 'de_DE' },
                'ae': { mid: 'A2VIGQ35RCS4UG', lop: 'en_AE' },
                'sa': { mid: 'A17E79C6D8DWNP', lop: 'ar_SA' }
            };

            const mkp = marketplaceIds[domain] || marketplaceIds['com'];

            console.log(`Fetching suggestions for "${prefix}" from amazon.${domain}...`);

            // Build URL with all parameters matching Amazon's format
            const params = new URLSearchParams({
                'limit': '11',
                'prefix': prefix,
                'suggestion-type': 'KEYWORD',
                'page-type': 'Search',
                'alias': 'aps',
                'site-variant': 'desktop',
                'version': '3',
                'event': 'onfocuswithsearchterm',
                'wc': '',
                'lop': mkp.lop,
                'fb': '1',
                'mid': mkp.mid,
                'client-info': 'search-ui'
            });

            const url = `https://www.amazon.${domain}/suggestions?${params.toString()}`;

            const response = await fetch(url, {
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                console.warn(`Suggestions API returned ${response.status}`);
                return [];
            }

            const data = await response.json();

            if (data.suggestions && Array.isArray(data.suggestions)) {
                const keywords = data.suggestions
                    .filter(s => s.type === 'KEYWORD')
                    .map(s => s.value);
                console.log(`Got ${keywords.length} suggestions for "${prefix}":`, keywords);
                return keywords;
            }

            return [];
        } catch (e) {
            console.warn('Suggestion fetch error:', e);
            return [];
        }
    }
}

// Make available globally
if (typeof window !== 'undefined') {
    window.ReverseAsin = ReverseAsin;
}
