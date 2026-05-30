// CerebroAnalyzer - Multi-ASIN Keyword Analysis (Cerebro-like feature)
// Orchestrates analysis of up to 10 ASINs and compares keyword rankings

class CerebroAnalyzer {
    constructor(marketplace = 'amazon.com') {
        this.marketplace = this.normalizeMarketplace(marketplace);
        this.maxAsins = 10;
        this.asins = [];
        this.results = new Map(); // keyword -> data
        this.isRunning = false;
        this.onProgress = null;

        // Backend URL resolution (avoid hardcoded URLs drifting across modules)
        // Priority: global ApiClient instance -> window.API_CONFIG -> localhost fallback
        this.backendBaseUrlFallback = 'http://127.0.0.1:8000';

        // Configuration
        this.config = {
            includeSponsored: true,
            calculateCPR: true,
            deepScan: false,
            delayBetweenAsins: 2000,
            delayBetweenKeywords: 500
        };
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

    normalizeMarketplace(marketplace) {
        const raw = (marketplace || '').toString().trim().toLowerCase();
        if (!raw) return 'amazon.com';

        // Strip protocol/path and common subdomains
        const withoutProto = raw.replace(/^https?:\/\//, '');
        const hostname = withoutProto.split('/')[0] || withoutProto;
        const cleaned = hostname.replace(/^(www\.|smile\.|m\.)/i, '');

        if (cleaned.endsWith('amazon.co.uk')) return 'amazon.co.uk';
        if (cleaned.endsWith('amazon.com')) return 'amazon.com';
        if (cleaned.endsWith('amazon.eg')) return 'amazon.eg';
        if (cleaned.endsWith('amazon.de')) return 'amazon.de';

        return cleaned;
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

    /**
     * Main entry: Analyze multiple ASINs and find all keywords they rank for
     * @param {string[]} asins - Array of ASINs to analyze (max 10)
     * @param {Function} onProgress - Progress callback (percent, message, data)
     * @returns {Promise<object>} Complete analysis results
     */
    async analyze(asins, onProgress = null) {
        if (!asins || asins.length === 0) {
            throw new Error('At least one ASIN is required');
        }

        if (asins.length > this.maxAsins) {
            throw new Error(`Maximum ${this.maxAsins} ASINs allowed`);
        }

        this.asins = asins.map(a => a.trim().toUpperCase());
        this.onProgress = onProgress;
        this.isRunning = true;
        this.results.clear();

        const startTime = Date.now();

        try {
            this.updateProgress(0, 'Starting analysis...');

            // Fetch configuration from backend
            try {
                const baseUrl = this.getBackendBaseUrl();
                const configResponse = await fetch(`${baseUrl}/api/settings?_t=${Date.now()}`);
                if (configResponse.ok) {
                    const configData = await configResponse.json();
                    const settings = configData.settings || {};

                    this.fetchBsrEnabled = this.parseBooleanSetting(settings.cerebro_fetch_bsr_enabled, false);
                    this.testModeEnabled = this.parseBooleanSetting(settings.test_mode_enabled, false);
                    this.useBackendCache = this.testModeEnabled ? false : this.parseBooleanSetting(settings.cerebro_use_backend_cache, true);
                    this.testModeKeyword = settings.test_mode_keyword || 'portal scale body';
                    this.testModeProductUrl = settings.test_mode_product_url || '';

                    this.bsrProductsLimit = parseInt(settings.cerebro_bsr_products_limit) || 20;
                    this.bsrParallelRequests = parseInt(settings.cerebro_bsr_parallel_requests) || 3;
                    this.bsrDelayMs = parseInt(settings.cerebro_bsr_delay_ms) || 500;
                    this.parallelKeywords = parseInt(settings.cerebro_parallel_keywords) || 5;

                    console.log('[Cerebro] Settings loaded:', {
                        fetchBsrEnabled: this.fetchBsrEnabled,
                        useBackendCache: this.useBackendCache,
                        bsrProductsLimit: this.bsrProductsLimit,
                        bsrParallelRequests: this.bsrParallelRequests,
                        bsrDelayMs: this.bsrDelayMs,
                        parallelKeywords: this.parallelKeywords,
                        testModeEnabled: this.testModeEnabled
                    });
                }
            } catch (e) {
                console.warn('Failed to fetch settings, using defaults');
                this.fetchBsrEnabled = false;
                this.testModeEnabled = false;
                this.useBackendCache = true;
                this.testModeKeyword = 'portal scale body';
                this.testModeProductUrl = '';
                this.bsrProductsLimit = 20;
                this.bsrParallelRequests = 3;
                this.bsrDelayMs = 500;
                this.parallelKeywords = 5;
            }

            // Phase 1: Aggregate keywords for all ASINs
            this.updateProgress(5, 'Extracting keywords for all products...');
            
            const allKeywordsSet = new Set();
            const productInfos = {};
            const asinResultsMap = {};
            
            for (let i = 0; i < this.asins.length; i++) {
                if (!this.isRunning) break;
                const asin = this.asins[i];
                this.updateProgress(5 + Math.round((i / this.asins.length) * 15), `Extracting keywords for ASIN ${i + 1}/${this.asins.length}: ${asin}`);
                
                let productInfo = this.getProductInfoFromSearchPage(asin);
                if (!productInfo || !productInfo.title) {
                    productInfo = await this.getProductInfoFromProductPage(asin);
                }
                
                asinResultsMap[asin] = {
                    asin: asin,
                    result: {
                        productInfo: productInfo,
                        title: productInfo?.title || '',
                        keywords: []
                    },
                    error: productInfo ? null : 'Product not found'
                };
                
                if (productInfo && productInfo.title) {
                    productInfos[asin] = productInfo;
                    const seedKeywords = this.extractSeedKeywords(productInfo.title);
                    
                    const currentQuery = this.getCurrentSearchQuery();
                    if (currentQuery && !seedKeywords.includes(currentQuery.toLowerCase())) {
                        seedKeywords.unshift(currentQuery.toLowerCase());
                    }
                    
                    // Add seeds immediately
                    seedKeywords.slice(0, 10).forEach(k => allKeywordsSet.add(k));
                    
                    // Fetch suggestions
                    const suggestions = await this.getAmazonSuggestionsForSeeds(seedKeywords.slice(0, 3));
                    suggestions.forEach(k => allKeywordsSet.add(k));
                }
                
                if (i < this.asins.length - 1 && this.isRunning) {
                    await this.delay(300); // Shorter delay, not hammering search API
                }
            }
            
            // Limit to 50 unique keywords to keep search fast
            let allKeywords = [...allKeywordsSet].slice(0, 50);
            if (this.testModeEnabled) {
                allKeywords = [this.testModeKeyword];
                console.log(`[Cerebro] Test Mode Active: FORCE analyzing only one keyword: "${allKeywords[0]}"`);
            }
            console.log(`[Cerebro] Aggregated ${allKeywords.length} unique keywords for ${this.asins.length} ASINs`);
            
            // Pre-fetch cached volumes from backend if enabled
            let cachedVolumesMap = {};
            if (this.useBackendCache) {
                this.updateProgress(20, 'Checking backend cache for existing search volumes...');
                cachedVolumesMap = await this.fetchCachedVolumes(allKeywords);
                console.log(`[Cerebro] Found ${Object.keys(cachedVolumesMap).length} cached volumes`);
            }

            // Phase 2: Single-pass search (Batched)
            const batchSize = this.parallelKeywords || 5; // Search multiple keywords concurrently
            for (let i = 0; i < allKeywords.length; i += batchSize) {
                if (!this.isRunning) break;
                
                const batch = allKeywords.slice(i, i + batchSize);
                
                this.updateProgress(
                    20 + Math.round((i / allKeywords.length) * 50),
                    `Searching keywords ${i + 1} to ${Math.min(i + batchSize, allKeywords.length)} of ${allKeywords.length}...`
                );
                
                const batchPromises = batch.map(async (keyword) => {
                    try {
                        const cachedVolume = cachedVolumesMap[keyword] || null;
                        // searchAndFindAsins returns positions for ALL matching target ASINs
                        const searchData = await this.searchAndFindAsins(keyword, this.asins, cachedVolume);
                        if (searchData && searchData.found_asins) {
                            for (const [foundAsin, data] of Object.entries(searchData.found_asins)) {
                                if (asinResultsMap[foundAsin]) {
                                    asinResultsMap[foundAsin].result.keywords.push({
                                        keyword: keyword,
                                        position: data.position,
                                        estimated_volume: searchData.estimated_volume || 0,
                                        difficulty_score: searchData.difficulty_score ?? 0,
                                        difficulty_level: searchData.difficulty_level ?? null,
                                        competing_products: searchData.competing_products || 0,
                                        is_sponsored: data.is_sponsored || false,
                                        sponsored_count: searchData.sponsored_count || 0,
                                        title_density: searchData.title_density || 0,
                                        total_click_share: searchData.total_click_share || 0,
                                        total_page_sales: searchData.total_page_sales || 0,
                                        avg_reviews: searchData.avg_reviews || 0
                                    });
                                }
                            }
                        }
                    } catch (e) {
                        console.warn(`[Cerebro] Error searching for "${keyword}":`, e);
                    }
                });

                await Promise.all(batchPromises);
                
                if (i + batchSize < allKeywords.length) {
                    await this.delay(this.config.delayBetweenKeywords || 800);
                }
            }
            
            const asinResults = Object.values(asinResultsMap);

            // Step 2: Merge results across all ASINs
            this.updateProgress(75, 'Merging keyword data...');
            const mergedKeywords = this.mergeResults(asinResults);

            // Step 3: Calculate advanced metrics
            this.updateProgress(85, 'Calculating IQ scores and metrics...');
            const enrichedKeywords = this.calculateMetrics(mergedKeywords);

            // Step 4: Sort by Search Volume
            this.updateProgress(95, 'Sorting by search volume...');
            const sortedKeywords = enrichedKeywords.sort((a, b) =>
                (b.search_volume || 0) - (a.search_volume || 0)
            );

            this.updateProgress(100, 'Analysis complete!');

            const duration = Math.round((Date.now() - startTime) / 1000);

            return {
                success: true,
                marketplace: this.marketplace,
                asins: this.asins,
                asin_count: this.asins.length,
                total_keywords: sortedKeywords.length,
                duration_seconds: duration,
                keywords: sortedKeywords,
                asin_summaries: asinResults.map(r => ({
                    asin: r.asin,
                    keywords_found: r.result?.keywords?.length || 0,
                    error: r.error || null,
                    title: r.result?.productInfo?.title || '',
                    image: r.result?.productInfo?.image || ''
                }))
            };

        } catch (error) {
            this.isRunning = false;
            throw error;
        }
    }

    /**
     * Get product info from the search results page DOM
     */
    getProductInfoFromSearchPage(asin) {
        const productCard = document.querySelector(`[data-asin="${asin}"]`);
        if (!productCard) return null;

        // Extract title - use more specific selectors and avoid sponsored labels
        let title = '';

        // Try multiple title selectors in order of specificity
        const titleSelectors = [
            'h2.a-size-mini a span.a-text-normal',
            'h2 a.a-link-normal span',
            '[data-cy="title-recipe"] a span',
            'h2 a span:not(.a-color-secondary)',
            '.a-size-base-plus.a-color-base'
        ];

        for (const selector of titleSelectors) {
            const el = productCard.querySelector(selector);
            if (el) {
                const text = el.textContent?.trim() || '';
                // Skip if it contains "sponsored" (case insensitive)
                if (text && !text.toLowerCase().includes('sponsor')) {
                    title = text;
                    break;
                }
            }
        }

        // Fallback: get text from h2 and clean it
        if (!title) {
            const h2 = productCard.querySelector('h2');
            if (h2) {
                title = h2.textContent?.trim() || '';
                // Remove "Sponsored" prefix if present
                title = title.replace(/^(Sponsored\s*)+/gi, '').trim();
            }
        }

        // Extract price
        const priceEl = productCard.querySelector('.a-price .a-offscreen, .a-price-whole');
        const priceText = priceEl?.textContent?.trim() || '';
        const price = parseFloat(priceText.replace(/[^\d.]/g, '')) || 0;

        // Extract rating
        const ratingEl = productCard.querySelector('.a-icon-star-small .a-icon-alt, [data-cy="reviews-ratings-count"]');
        const ratingText = ratingEl?.textContent || '';
        const rating = parseFloat(ratingText.match(/[\d.]+/)?.[0]) || 0;

        // Extract reviews count
        const reviewsEl = productCard.querySelector('[data-csa-c-func-deps="aui-da-a-popover"] span, .a-size-base.s-underline-text');
        const reviewsText = reviewsEl?.textContent || '';
        const reviews = parseInt(reviewsText.replace(/[^\d]/g, '')) || 0;

        // Extract image
        const imgEl = productCard.querySelector('img.s-image');
        const image = imgEl?.src || '';

        return { asin, title, price, rating, reviews, image };
    }

    /**
     * Fetch the product page directly to get info (robust fallback)
     */
    async getProductInfoFromProductPage(asin) {
        try {
            const origin = window.location.origin; // e.g., https://www.amazon.eg or https://www.amazon.com
            const productUrl = `${origin}/dp/${asin}`;
            
            const response = await fetch(productUrl, {
                credentials: 'include',
                headers: {
                    'Accept': 'text/html,application/xhtml+xml',
                }
            });

            if (!response.ok) return null;

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const titleEl = doc.querySelector('#productTitle');
            const title = titleEl ? titleEl.textContent.trim() : '';

            if (!title) return null;

            // Extract price, rating, reviews if available
            const priceEl = doc.querySelector('.a-price .a-offscreen');
            const price = priceEl ? parseFloat(priceEl.textContent.replace(/[^\d.]/g, '')) : 0;

            const ratingEl = doc.querySelector('#acrPopover, .a-icon-star-small .a-icon-alt');
            const rating = ratingEl ? parseFloat(ratingEl.textContent.match(/[\d.]+/)?.[0]) : 0;

            const reviewsEl = doc.querySelector('#acrCustomerReviewText');
            const reviews = reviewsEl ? parseInt(reviewsEl.textContent.replace(/[^\d]/g, '')) : 0;

            // Extract image
            const imgEl = doc.querySelector('#landingImage, #imgBlkFront');
            const image = imgEl?.src || '';

            return { asin, title, price, rating, reviews, image };
        } catch (e) {
            console.warn(`[Cerebro] Failed to fetch product page for ${asin}:`, e.message);
            return null;
        }
    }

    /**
     * Get current search query from URL or search box
     */
    getCurrentSearchQuery() {
        // From URL
        const urlParams = new URLSearchParams(window.location.search);
        const query = urlParams.get('k') || urlParams.get('field-keywords');
        if (query) return query;

        // From search box
        const searchBox = document.querySelector('#twotabsearchtextbox');
        return searchBox?.value || '';
    }

    /**
     * Extract seed keywords from product title
     */
    extractSeedKeywords(title) {
        if (!title) return [];

        const stopWords = ['with', 'for', 'and', 'the', 'a', 'an', 'in', 'on', 'of', 'to', 'by'];
        const words = title.toLowerCase()
            .replace(/[^\w\s]/g, ' ')
            .split(/\s+/)
            .filter(w => w.length > 2 && !stopWords.includes(w));

        const keywords = [];

        // Add 2-word combinations
        for (let i = 0; i < words.length - 1 && keywords.length < 10; i++) {
            keywords.push(`${words[i]} ${words[i + 1]}`);
        }

        // Add 3-word combinations
        for (let i = 0; i < words.length - 2 && keywords.length < 15; i++) {
            keywords.push(`${words[i]} ${words[i + 1]} ${words[i + 2]}`);
        }

        // Add individual important words
        words.slice(0, 5).forEach(w => {
            if (!keywords.some(k => k.includes(w))) {
                keywords.push(w);
            }
        });

        return [...new Set(keywords)];
    }

    /**
     * Get Amazon autocomplete suggestions for seed keywords
     * Uses same method as ReverseAsin
     */
    async getAmazonSuggestionsForSeeds(seeds) {
        const suggestions = [];

        // Marketplace IDs for suggestions API (same as ReverseAsin)
        const marketplaceIds = {
            'eg': { mid: 'ARBP9OOSHTCHU', lop: 'en_AE' },
            'com': { mid: 'ATVPDKIKX0DER', lop: 'en_US' },
            'co.uk': { mid: 'A1F83G8C2ARO7P', lop: 'en_GB' },
            'de': { mid: 'A1PA6795UKMFR9', lop: 'de_DE' },
            'ae': { mid: 'A2VIGQ35RCS4UG', lop: 'en_AE' },
            'sa': { mid: 'A17E79C6D8DWNP', lop: 'ar_SA' },
            'ca': { mid: 'A2EUQ1WTGCTBG2', lop: 'en_CA' },
            'fr': { mid: 'A13V1IB3VIYBER', lop: 'fr_FR' },
            'it': { mid: 'APJ6JRA9NG5V4', lop: 'it_IT' },
            'es': { mid: 'A1RKKUPIHCS9HS', lop: 'es_ES' }
        };

        // Get domain from current marketplace
        const domain = this.marketplace.replace('www.', '').replace('amazon.', '');
        const mkp = marketplaceIds[domain] || marketplaceIds['com'];

        for (const seed of seeds) {
            try {
                // Build URL with all parameters matching Amazon's format
                const params = new URLSearchParams({
                    'limit': '11',
                    'prefix': seed,
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
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.suggestions && Array.isArray(data.suggestions)) {
                        data.suggestions
                            .filter(s => s.type === 'KEYWORD')
                            .forEach(s => {
                                if (s.value && s.value.length > 2) {
                                    suggestions.push(s.value.toLowerCase());
                                }
                            });
                    }
                }
            } catch (e) {
                console.warn(`[Cerebro] Suggestions failed for "${seed}":`, e.message);
            }
            await this.delay(150);
        }

        console.log(`[Cerebro] Got ${suggestions.length} suggestions from ${seeds.length} seeds`);
        return [...new Set(suggestions)];
    }

    /**
     * Fetch cached search volumes from backend for a batch of keywords
     */
    async fetchCachedVolumes(keywords) {
        if (!keywords || keywords.length === 0) return {};
        
        try {
            const baseUrl = this.getBackendBaseUrl();
            
            // Backend might have limits on array size, chunk if necessary (e.g., 50 at a time)
            const chunkSize = 50;
            let results = {};
            
            for (let i = 0; i < keywords.length; i += chunkSize) {
                const chunk = keywords.slice(i, i + chunkSize);
                
                const response = await fetch(`${baseUrl}/api/search-volume/batch-cached`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        keywords: chunk,
                        marketplace: this.marketplace
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.cached_volumes) {
                        results = { ...results, ...data.cached_volumes };
                    }
                }
            }
            return results;
        } catch (e) {
            console.warn('[Cerebro] Failed to fetch cached volumes:', e);
            return {};
        }
    }

    /**
     * Search for a keyword and find if multiple ASINs rank
     * Uses direct fetch (same-origin request from Amazon page)
     */
    async searchAndFindAsins(keyword, targetAsins, cachedVolumeData = null) {
        // Use same origin to ensure no CORS issues
        const origin = window.location.origin; // e.g., https://www.amazon.eg
        const searchUrl = `${origin}/s?k=${encodeURIComponent(keyword)}`;

        try {
            let html = this.testModeEnabled ? null : this.getCachedHtml(keyword);

            if (!html) {
                // Direct fetch works because we're on an Amazon page (same-origin)
                const response = await fetch(searchUrl, {
                    method: 'GET',
                    credentials: 'include', // Include cookies for logged-in state
                    headers: {
                        'Accept': 'text/html,application/xhtml+xml',
                    }
                });

                if (!response.ok) {
                    console.warn(`[Cerebro] Search returned ${response.status} for "${keyword}"`);
                    return null;
                }

                html = await response.text();
                if (!this.testModeEnabled) {
                    this.setCachedHtml(keyword, html);
                }
            } else {
                console.log(`[Cerebro] Using cached HTML for "${keyword}" (Development/Debug Mode)`);
            }

            // Check if we got a valid search page (not captcha or error)
            if (html.includes('captcha') || html.includes('Enter the characters')) {
                console.warn(`[Cerebro] Captcha detected for "${keyword}"`);
                return { position: 0, captcha: true };
            }

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Find products from the MAIN results container (avoid carousels/related widgets)
            const mainResultsContainer = doc.querySelector('.s-main-slot.s-result-list.s-search-results');
            const products = mainResultsContainer
                ? mainResultsContainer.querySelectorAll('[data-component-type="s-search-result"][data-asin]:not([data-asin=""])')
                : doc.querySelectorAll('[data-asin]:not([data-asin=""])');

            // Debug: Log first few ASINs found
            const foundAsins = Array.from(products).slice(0, 10).map(p => p.getAttribute('data-asin'));
            console.log(`[Cerebro] "${keyword}": Found ${products.length} products. ASINs:`, foundAsins.slice(0, 5));

            const foundAsinsMap = {};
            const targetAsinsUpper = new Set(targetAsins.map(a => a.toUpperCase()));

            let sponsoredCount = 0;
            let titleDensity = 0;
            const productSalesData = [];
            const cardProductsForBackend = [];
            let totalReviews = 0;
            let reviewCount = 0;
            const keywordLower = keyword.toLowerCase().trim();
            const organicProducts = [];

            for (let i = 0; i < products.length; i++) {
                const productCard = products[i];

                // Detect sponsored using multiple selectors (static HTML may include these)
                const sponsoredByAttr = productCard.getAttribute('data-component-type') === 'sp-sponsored-result';
                const sponsoredByChild = !!productCard.querySelector(
                    '[data-component-type="sp-sponsored-result"], ' +
                    '.s-sponsored-label-text, ' +
                    '.s-sponsored-label-info-icon, ' +
                    '.s-sponsored-label-info-icon-badge, ' +
                    '.puis-sponsored-label-text, ' +
                    'span.a-color-secondary > span[aria-label], ' +
                    '.AdHolder'
                );
                // Also check for Arabic sponsored text (مدعوم) or English text within the card
                const cardText = productCard.textContent || '';
                const sponsoredByText = /Sponsored|مدعوم|ممول/i.test(cardText.substring(0, 200));

                const isProductSponsored = sponsoredByAttr || sponsoredByChild || sponsoredByText;
                if (isProductSponsored) {
                    sponsoredCount++;
                } else {
                    const asin = productCard.getAttribute('data-asin');
                    if (asin) {
                        organicProducts.push(asin.toUpperCase());
                    }
                }

                // Extract product title for title density (matching SerpParser logic)
                let title = '';
                const h2Element = productCard.querySelector('h2');
                if (h2Element) {
                    const ariaLabel = h2Element.getAttribute('aria-label');
                    if (ariaLabel) {
                        title = ariaLabel.replace(/^Sponsored Ad\s*[–-]\s*/i, '').trim();
                    } else {
                        const titleSpan = h2Element.querySelector('span');
                        if (titleSpan) {
                            let spanText = titleSpan.textContent?.trim() || '';
                            spanText = spanText.replace(/^Sponsored\s*/gi, '').trim();
                            if (spanText && spanText !== 'Sponsored') {
                                title = spanText;
                            }
                        }
                    }
                }
                if (!title) {
                    const titleEl = productCard.querySelector('.s-title-instructions-style span, [data-cy="title-recipe"] span');
                    if (titleEl) {
                        title = titleEl.textContent?.trim() || '';
                        title = title.replace(/^Sponsored\s*/gi, '').trim();
                    }
                }
                if (!title) {
                    const h2 = productCard.querySelector('h2');
                    if (h2) {
                        title = h2.textContent?.trim() || '';
                        title = title.replace(/^(Sponsored\s*)+/gi, '').trim();
                    }
                }

                // Calculate Title Density
                if (title && title.toLowerCase().includes(keywordLower)) {
                    titleDensity++;
                }

                // ── Scrape "X bought in past month" for real sales data ──────────
                // Amazon shows this in various elements; covers English + Arabic
                const salesIndicatorSelectors = [
                    '.a-row .a-size-base.a-color-secondary',
                    '.a-row span.a-size-base',
                    '[data-cy="purchase-options"] .a-size-base',
                    '.s-csa-instrumentation-wrapper .a-size-base',
                    '.a-size-mini',
                    '.a-size-small'
                ];
                let cardSales = 0;
                let hasBadge = false;
                // Search entire card text for "X bought in past month" pattern
                const fullCardText = productCard.textContent || '';
                // English: "1K+", "500+", "50" etc. before "bought in past month"
                // Arabic: تم شراء أكثر من X خلال الشهر الماضي or similar
                const boughtMatch = fullCardText.match(
                    /([\d,]+(?:\.\d+)?[KkMm+]*)\+?\s*(?:bought in past month|\u062a\u0645 \u0634\u0631\u0627\u0621|\u0627\u0634\u062a\u0631\u0627\u0647\u0627)/i
                );
                if (boughtMatch) {
                    let raw = boughtMatch[1].replace(/,/g, '').replace(/\+/g, '').trim();
                    if (/k/i.test(raw)) {
                        cardSales = Math.round(parseFloat(raw) * 1000);
                    } else if (/m/i.test(raw)) {
                        cardSales = Math.round(parseFloat(raw) * 1000000);
                    } else {
                        cardSales = parseInt(raw) || 0;
                    }
                    hasBadge = true;
                }
                productSalesData.push({ position: i + 1, sales: cardSales, hasBadge: hasBadge });

                // ── Scrape rating + review count per card (needed for KD) ─────────
                let rating = 0;
                let reviews = 0;

                // Rating (e.g. "4.3 out of 5 stars")
                const ratingEl = productCard.querySelector('[aria-label*="out of 5"], .a-icon-star-small .a-icon-alt, .a-icon-star .a-icon-alt');
                if (ratingEl) {
                    const text = ratingEl.getAttribute('aria-label') || ratingEl.textContent || '';
                    const match = text.match(/([\d.]+)/);
                    rating = match ? parseFloat(match[1]) : 0;
                }

                // Reviews count
                const ratingLinks = productCard.querySelectorAll('a[aria-label*="rating"], a[aria-label*="review"], [href*="#customerReviews"]');
                for (const link of ratingLinks) {
                    const ariaLabel = link.getAttribute('aria-label') || '';
                    const match = ariaLabel.match(/(\d[\d,]*)\s*(?:rating|review)/i);
                    if (match) {
                        reviews = parseInt(match[1].replace(/,/g, '')) || 0;
                        break;
                    }
                    const text = (link.textContent || '').replace(/[^\d]/g, '');
                    if (text) {
                        reviews = parseInt(text) || 0;
                        break;
                    }
                }

                // Fallback selectors used on some marketplaces
                if (!reviews) {
                    const reviewsEl = productCard.querySelector('.a-size-base.s-underline-text, .a-size-base.s-underline-link-text, [data-cy="reviews-block"], .a-row.a-size-small');
                    if (reviewsEl) {
                        const revText = (reviewsEl.textContent || '').replace(/[^\d]/g, '');
                        reviews = parseInt(revText) || 0;
                    }
                }

                if (reviews > 0) {
                    totalReviews += reviews;
                    reviewCount++;
                }

                // Keep a clean per-card record for backend KD/volume calculations
                const asinForBackend = (productCard.getAttribute('data-asin') || '').toUpperCase();
                if (asinForBackend) {
                    cardProductsForBackend.push({
                        asin: asinForBackend,
                        position: i + 1,
                        bsr: null,
                        price: 0,
                        reviews,
                        rating,
                        is_sponsored: isProductSponsored,
                        monthly_sales: cardSales,
                        category: null,
                        brand: null
                    });
                }

                // Find our target ASIN's position
                const asin = (productCard.getAttribute('data-asin') || '').toUpperCase();
                if (targetAsinsUpper.has(asin)) {
                    foundAsinsMap[asin] = {
                        position: i + 1,
                        is_sponsored: isProductSponsored
                    };
                    console.log(`[Cerebro] ✓ FOUND ${asin} at position ${i + 1} for "${keyword}"`);
                }
            }

            // Competing products & search volume — using same formula as Magnet tool
            let resultsText = doc.body?.textContent || '';
            // Normalize Arabic-Indic digits
            resultsText = resultsText.replace(/[\u0660-\u0669]/g, d => d.charCodeAt(0) - 0x0660);
            const countMatch = resultsText.match(/(\d{1,3}(?:,\d{3})*)\s*(?:results|result|\u0645\u0646 \u0627\u0644\u0646\u062a\u0627\u0626\u062c|\u0646\u062a\u0627\u0626\u062c|\u0646\u062a\u064a\u062c\u0629)/i);
            const competingProducts = countMatch ? parseInt(countMatch[1].replace(/,/g, '')) : products.length;

            // Average reviews across cards on this page (used to tune the volume estimate)
            const avgReviews = reviewCount > 0 ? Math.round(totalReviews / reviewCount) : 0;

            // Use the same SerpV2 search volume calculation as Reverse ASIN and backend
            let estimated_volume = 0;
            let difficulty_score = 0;
            let difficulty_level = null;

            // ── Calculate Hybrid Page Sales (Market Analysis calculations) ──
            let totalPageSales = 0;
            let serpProducts = null;
            
            // Bypass BSR fetching if a cached volume exists and is high confidence
            const hasGoodCache = cachedVolumeData && cachedVolumeData.estimated > 0;
            
            if (typeof SerpParser !== 'undefined') {
                const serpParser = new SerpParser(doc);
                serpProducts = serpParser.extractProducts();
                
                if (this.fetchBsrEnabled && !hasGoodCache) {
                    // Slice immediately to the limit so only the top N products are enriched
                    // AND sent to the backend — this ensures position ordering matches Reverse ASIN exactly
                    serpProducts = serpProducts.slice(0, this.bsrProductsLimit || 20);
                    
                    const enrichOptions = {
                        limit: this.bsrProductsLimit || 20,
                        batchSize: this.bsrParallelRequests || 3,
                        batchDelay: this.bsrDelayMs || 500
                    };
                    
                    console.log(`[Cerebro] Fetching BSR for ${serpProducts.length} products for "${keyword}"...`);
                    serpProducts = await serpParser.enrichWithBSR(serpProducts, enrichOptions);
                }
            }

            if (serpProducts) {
                titleDensity = 0;
                for (let i = 0; i < serpProducts.length; i++) {
                    const item = serpProducts[i];
                    if (item.monthly_sales && item.monthly_sales > 0) {
                        totalPageSales += item.monthly_sales;
                    } else if (item.bsr && item.bsr > 0) {
                        totalPageSales += this.estimateSalesFromBSR(item.bsr);
                    }
                    // Recalculate title density using highly accurate SerpParser titles
                    if (item.title && item.title.toLowerCase().includes(keywordLower)) {
                        titleDensity++;
                    }
                }
            } else {
                if (hasGoodCache && this.fetchBsrEnabled) {
                    console.log(`[Cerebro] Bypassed BSR fetch for "${keyword}" (used backend cache)`);
                }
                // If a product does not have a badge, we just skip it (or it stays 0).
                // The new SerpV2 search volume calculation correctly weights only products with sales/badges.
                for (const item of productSalesData) {
                    if (item.hasBadge) {
                        totalPageSales += item.sales;
                    }
                }
            }

            // Calculate search volume using the backend API for perfect consistency across tools
            const baseUrl = this.getBackendBaseUrl();
            
            try {
                // Ensure all products sent to backend have required fields
                const backendProducts = (serpProducts || cardProductsForBackend).map(p => ({
                    asin: p.asin || 'N/A',
                    position: p.position,
                    bsr: p.bsr || null,
                    price: p.price || 0,
                    reviews: (p.reviews != null ? p.reviews : 0),
                    rating: (p.rating != null ? p.rating : 0),
                    is_sponsored: !!(p.is_sponsored || p.sponsored),
                    monthly_sales: p.monthly_sales || p.sales || 0,
                    category: p.category || p.bsr_category || null,
                    brand: p.brand || null
                }));

                // Slice to the bsrProductsLimit (default 20) for 100% calculation parity with Market Analysis
                const limit = this.bsrProductsLimit || 20;
                const slicedBackendProducts = backendProducts.slice(0, limit);

                console.log(`[Cerebro] Requesting backend search volume estimate (limited to top ${limit} products) for "${keyword}"...`);
                const response = await fetch(`${baseUrl}/api/search-volume/estimate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Tool-Source': 'cerebro'
                    },
                    body: JSON.stringify({
                        keyword: keyword,
                        marketplace: this.marketplace,
                        products: slicedBackendProducts,
                        prefer_cached_volume: !this.testModeEnabled // Skip cache if in test mode
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.search_volume) {
                        estimated_volume = data.search_volume.estimated;
                        if (data.difficulty) {
                            difficulty_score = data.difficulty.score ?? 0;
                            difficulty_level = data.difficulty.level ?? null;
                        }
                        console.log(`[Cerebro] Backend estimate for "${keyword}": volume=${estimated_volume} KD=${difficulty_score}`);
                    } else {
                        throw new Error("Invalid response structure");
                    }
                } else {
                    throw new Error(`HTTP error ${response.status}`);
                }
            } catch (e) {
                console.warn(`[Cerebro] Backend estimation failed for "${keyword}", using local fallback:`, e);
                const productsForVolume = serpProducts ? serpProducts : productSalesData;
                estimated_volume = this.calculateSearchVolumeSerpV2(productsForVolume);
            }
            
            // Override with cached volume if it exists and is more reliable (i.e. is non-zero)
            if (hasGoodCache && !estimated_volume) {
                // If the backend has a high-quality volume estimation, prefer it
                estimated_volume = cachedVolumeData.estimated;
            }

            // -----------------------------------------------------------------------
            // Top 3 Sales Share: estimated % of total page sales captured by the
            // top 3 organic products, based on a position-CTR → sales weight curve.
            //
            // CTR weights per organic position (industry averages):
            //   pos 1: 36%, pos 2: 17%, pos 3: 10%, pos 4: 7%, pos 5: 5%
            //   pos 6: 3%,  pos 7-10: 2%,  pos 11-20: 1%,  pos 21+: 0.5%
            //
            // Formula:
            //   top3Weight  = sum of weights for organic positions 1-3
            //   totalWeight = sum of weights for ALL organic positions on page
            //   Top3SalesShare = (top3Weight / totalWeight) × 100
            // -----------------------------------------------------------------------
            const getOrgWeight = (orgPos) => {
                if (orgPos === 1) return 0.36;
                if (orgPos === 2) return 0.17;
                if (orgPos === 3) return 0.10;
                if (orgPos === 4) return 0.07;
                if (orgPos === 5) return 0.05;
                if (orgPos === 6) return 0.03;
                if (orgPos <= 10) return 0.02;
                if (orgPos <= 20) return 0.01;
                return 0.005;
            };

            let top3Weight = 0;
            let totalPageWeight = 0;
            for (let p = 0; p < organicProducts.length; p++) {
                const w = getOrgWeight(p + 1);
                totalPageWeight += w;
                if (p < 3) top3Weight += w;
            }
            const top3SalesShare = totalPageWeight > 0
                ? Math.min((top3Weight / totalPageWeight) * 100, 100)
                : 0;

            console.log(`[Cerebro] "${keyword}": organic=${organicProducts.length} sponsored=${sponsoredCount} top3Share=${top3SalesShare.toFixed(1)}% pageSales=${totalPageSales} volume=${estimated_volume} avgReviews=${avgReviews}`);

            return {
                keyword,
                found_asins: foundAsinsMap,
                competing_products: competingProducts,
                estimated_volume,
                difficulty_score,
                difficulty_level,
                avg_reviews: avgReviews,
                sponsored_count: sponsoredCount,
                total_page_products: sponsoredCount + organicProducts.length,
                title_density: titleDensity,
                total_click_share: Math.round(top3SalesShare * 10) / 10,
                total_page_sales: totalPageSales
            };
        } catch (e) {
            console.error(`[Cerebro] Search error for "${keyword}":`, e.message);
            return null;
        }
    }

    /**
     * Merge keyword results from all ASINs into a unified view
     */
    mergeResults(asinResults) {
        const keywordMap = new Map();

        for (const { asin, result, error } of asinResults) {
            if (error || !result?.keywords) continue;

            for (const kw of result.keywords) {
                const keyword = kw.keyword.toLowerCase().trim();

                if (!keywordMap.has(keyword)) {
                    keywordMap.set(keyword, {
                        keyword: keyword,
                        word_count: keyword.split(' ').length,
                        search_volume: kw.estimated_volume || 0,
                        competing_products: kw.competing_products || 0,
                        avg_reviews: kw.avg_reviews || 0,
                        title_density: kw.title_density || 0,
                        sponsored_count: kw.sponsored_count || 0,
                        total_page_products: kw.total_page_products || 0,
                        avg_price: kw.avg_price || 0,
                        avg_bsr: kw.avg_bsr || 0,
                        total_sales: kw.total_sales || 0,
                        total_page_sales: kw.total_page_sales || 0,
                        difficulty_score: kw.difficulty_score || 0,
                        difficulty_level: kw.difficulty_level || null,
                        organic_ranks: {},
                        sponsored_ranks: {},
                        asins_ranking: 0,
                        has_amazon_choice: false
                    });
                }

                const existing = keywordMap.get(keyword);

                // Store this ASIN's rank for this keyword
                if (kw.position && kw.position > 0) {
                    existing.organic_ranks[asin] = kw.position;
                    existing.asins_ranking++;
                }

                // Update with better data if available
                if (kw.estimated_volume > existing.search_volume) {
                    existing.search_volume = kw.estimated_volume;
                }
                if (kw.competing_products > existing.competing_products) {
                    existing.competing_products = kw.competing_products;
                }
                if (kw.title_density > existing.title_density) {
                    existing.title_density = kw.title_density;
                }
                if (kw.sponsored_count > existing.sponsored_count) {
                    existing.sponsored_count = kw.sponsored_count;
                    existing.total_page_products = kw.total_page_products || existing.total_page_products;
                }
                if (kw.total_click_share > (existing.total_click_share || 0)) {
                    existing.total_click_share = kw.total_click_share;
                }
                // Accumulate real page sales (sum across all ASIN passes for this keyword)
                if (kw.total_page_sales > (existing.total_page_sales || 0)) {
                    existing.total_page_sales = kw.total_page_sales;
                }
                // Keep highest avg_reviews seen (richer data)
                if ((kw.avg_reviews || 0) > (existing.avg_reviews || 0)) {
                    existing.avg_reviews = kw.avg_reviews;
                }

                // Keep highest difficulty score seen (worst-case competitiveness)
                if ((kw.difficulty_score || 0) > (existing.difficulty_score || 0)) {
                    existing.difficulty_score = kw.difficulty_score || 0;
                    existing.difficulty_level = kw.difficulty_level || existing.difficulty_level;
                }
            }
        }

        return Array.from(keywordMap.values());
    }

    /**
     * Calculate advanced metrics for each keyword
     */
    calculateMetrics(keywords) {
        return keywords.map(kw => {
            // ── Search Volume — Use SerpV2 calculation from Phase 1 ──
            // Priority 1: SerpV2 estimate from Phase 1 (position way like Reverse ASIN)
            // Priority 2: Fallback Magnet Analyzer estimate
            let searchVolume = kw.search_volume;
            
            // If the SerpV2 algorithm fell back to 100, we can use the Magnet estimate to be safe
            if (!searchVolume || searchVolume === 100) {
                searchVolume = this.estimateSearchVolume(kw.competing_products, kw.avg_reviews || 0) || 100;
            }

            // Cerebro IQ Score = (Volume / Competing Products) × 10
            const iqScore = kw.competing_products > 0
                ? ((searchVolume / kw.competing_products) * 10)
                : (searchVolume > 0 ? 10 : 0);

            // Keyword Sales estimate based on top ranker position
            const topRank = this.getMinRank(kw.organic_ranks);
            const keywordSales = this.estimateKeywordSales(searchVolume, topRank);

            // Average organic rank across ASINs
            const ranks = Object.values(kw.organic_ranks).filter(r => r && r > 0);
            const avgRank = ranks.length > 0
                ? Math.round(ranks.reduce((a, b) => a + b, 0) / ranks.length)
                : null;
            const minRank = ranks.length > 0 ? Math.min(...ranks) : null;
            const maxRank = ranks.length > 0 ? Math.max(...ranks) : null;

            // ── Total Keyword Sales ──
            // Priority 1: Real "X bought in past month" scraped from page
            // Priority 2: BSR-bracket estimate (market-analysis formula)
            //             Using amazon.eg table: ~average of top organic results
            // Priority 3: volume × 9.5% estimate (last resort)
            let totalKeywordSales = 0;
            if (kw.total_page_sales > 0) {
                // Real data + BSR estimates from scraping pass
                totalKeywordSales = kw.total_page_sales;
            } else {
                // Fallback BSR-bracket estimate ...
                let baseBSR = 50000;
                if (searchVolume > 5000)      baseBSR = 500;
                else if (searchVolume > 2000) baseBSR = 2000;
                else if (searchVolume > 1000) baseBSR = 5000;
                else if (searchVolume > 500)  baseBSR = 10000;
                else if (searchVolume > 100)  baseBSR = 20000;

                let sumSales = 0;
                for (let i = 0; i < 15; i++) {
                    const estimatedBSR = Math.min(100000, Math.round(baseBSR * Math.sqrt(i + 1)));
                    let posSales = this.estimateSalesFromBSR(estimatedBSR);
                    posSales = Math.min(posSales, 49); // Cap at 49 to align with Market Analysis no-badge rule
                    sumSales += posSales;
                }
                totalKeywordSales = sumSales;
            }

            // Use real scraped click share; 0 if not found (no static fallback)
            const totalClickShare = (kw.total_click_share != null) ? kw.total_click_share : 0;

            return {
                ...kw,
                search_volume: searchVolume,
                cerebro_iq_score: Math.round(iqScore * 100) / 100,
                keyword_sales: keywordSales,
                total_keyword_sales: totalKeywordSales,
                total_click_share: Math.round(totalClickShare * 10) / 10,
                avg_organic_rank: avgRank,
                min_organic_rank: minRank,
                max_organic_rank: maxRank,
                match_type: this.determineMatchType(kw)
            };
        });
    }

    /**
     * Get the minimum (best) rank from organic_ranks object
     */
    getMinRank(organicRanks) {
        const ranks = Object.values(organicRanks).filter(r => r && r > 0);
        return ranks.length > 0 ? Math.min(...ranks) : null;
    }

    /**
     * Get cached HTML from sessionStorage (expires after 1 hour)
     */
    getCachedHtml(keyword) {
        try {
            const str = sessionStorage.getItem(`cerebro_html_${keyword}`);
            if (!str) return null;
            const data = JSON.parse(str);
            // Expire after 1 hour
            if (Date.now() - data.timestamp > 3600000) {
                sessionStorage.removeItem(`cerebro_html_${keyword}`);
                return null;
            }
            return data.html;
        } catch(e) { 
            return null; 
        }
    }

    /**
     * Save HTML to sessionStorage for fast debugging and development
     */
    setCachedHtml(keyword, html) {
        try {
            const data = JSON.stringify({
                timestamp: Date.now(),
                html: html
            });
            sessionStorage.setItem(`cerebro_html_${keyword}`, data);
        } catch(e) {
            // Quota exceeded (usually 5MB limit). Free space by clearing all previous cerebro caches.
            console.warn('[Cerebro] Session cache quota exceeded. Freeing space for new keywords...');
            const keysToRemove = [];
            for (let i = 0; i < sessionStorage.length; i++) {
                const key = sessionStorage.key(i);
                if (key && key.startsWith('cerebro_html_')) {
                    keysToRemove.push(key);
                }
            }
            keysToRemove.forEach(k => sessionStorage.removeItem(k));
            
            // Try one more time
            try {
                const data = JSON.stringify({ timestamp: Date.now(), html: html });
                sessionStorage.setItem(`cerebro_html_${keyword}`, data);
            } catch(e2) {
                console.warn('[Cerebro] HTML too large to cache even after clearing.');
            }
        }
    }

    /**
     * Calculates search volume using the SerpV2 algorithm, exactly like Reverse ASIN and Backend.
     * It uses position-weighted click attribution to estimate total market volume.
     */
    calculateSearchVolumeSerpV2(products) {
        if (!products || products.length === 0) return 100;

        let organic = [];
        let sponsored = [];
        for (const p of products) {
            if (p.is_sponsored || p.sponsored) sponsored.push(p);
            else organic.push(p);
        }
        
        // Reorder: organic first, sponsored at end
        const reordered = [...organic, ...sponsored];

        let weightedSales = 0;
        let validProducts = 0;

        for (let i = 0; i < reordered.length; i++) {
            const product = reordered[i];
            const position = i + 1;

            let monthlySales = product.sales || product.monthly_sales || 0;

            if (!monthlySales && product.bsr && product.bsr > 0) {
                monthlySales = this.estimateSalesFromBSR(product.bsr);
            }

            if (!monthlySales || monthlySales <= 0) continue;

            let positionWeight = 0.001;
            if (position <= 5) positionWeight = 0.15;
            else if (position <= 10) positionWeight = 0.03;
            else if (position <= 20) positionWeight = 0.005;
            else if (position <= 40) positionWeight = 0.002;

            let typeWeight = (product.is_sponsored || product.sponsored) ? 0.5 : 1.0;

            weightedSales += monthlySales * positionWeight * typeWeight;
            validProducts++;
        }

        if (validProducts === 0) {
            // Fallback if NO products have sales badges and BSR is off
            return 100;
        }

        const clickShare = 0.95;
        const avgCVR = 0.08;

        return Math.round(weightedSales / avgCVR / clickShare);
    }

    /**
     * Magnet Analyzer fallback search volume estimate
     */
    estimateSearchVolume(competingProducts, avgReviews = 0) {
        // Reuse MagnetAnalyzer's formula so both tools stay in sync
        if (typeof MagnetAnalyzer !== 'undefined') {
            const magnet = new MagnetAnalyzer(this.marketplace);
            return magnet.estimateSearchVolume(competingProducts, avgReviews);
        }
        // Fallback (should never happen after manifest reorder)
        if (!competingProducts || competingProducts === 0) return 0;
        let volume = Math.sqrt(competingProducts) * 50;
        volume *= avgReviews > 1000 ? 1.5 : avgReviews > 500 ? 1.2 : avgReviews > 100 ? 1.0 : 0.8;
        return Math.round(volume);
    }

    /**
     * Estimate monthly sales from BSR.
     * Delegates to MarketConstants.calculateMonthlySales() — single source of truth.
     */
    estimateSalesFromBSR(bsr) {
        // Reuse MarketConstants so the BSR bracket table is maintained in one place
        if (typeof MarketConstants !== 'undefined') {
            const mc = new MarketConstants(this.marketplace);
            return mc.calculateMonthlySales(bsr);
        }
        // Fallback if class not available
        if (bsr <= 100)    return 600;
        if (bsr <= 500)    return 280;
        if (bsr <= 2000)   return 140;
        if (bsr <= 10000)  return 60;
        if (bsr <= 50000)  return 28;
        return 8;
    }

    /**
     * Estimate keyword sales based on position
     */
    estimateKeywordSales(volume, position) {
        if (!position || position > 50) return 0;

        // Click share by position
        let clickShare = 0;
        if (position <= 5) clickShare = 0.15;
        else if (position <= 10) clickShare = 0.03;
        else if (position <= 20) clickShare = 0.005;
        else clickShare = 0.002;

        // CVR assumption
        const cvr = 0.10;

        return Math.round(volume * clickShare * cvr);
    }

    /**
     * Determine match type (organic/sponsored/both)
     */
    determineMatchType(kw) {
        const hasOrganic = Object.values(kw.organic_ranks).some(r => r > 0);
        const hasSponsored = Object.values(kw.sponsored_ranks || {}).some(r => r > 0);

        if (hasOrganic && hasSponsored) return 'both';
        if (hasSponsored) return 'sponsored';
        return 'organic';
    }

    /**
     * Calculate Title Density by counting products with keyword in title
     * @param {string} keyword
     * @param {array} products
     */
    calculateTitleDensity(keyword, products) {
        if (!products || products.length === 0) return 0;

        const keywordLower = keyword.toLowerCase();
        let count = 0;

        for (const product of products.slice(0, 48)) { // Page 1 = first 48
            const title = (product.title || '').toLowerCase();
            if (title.includes(keywordLower)) {
                count++;
            }
        }

        return count;
    }

    /**
     * Apply filters to results
     */
    filterResults(keywords, filters) {
        return keywords.filter(kw => {
            // Volume filter
            if (filters.volume_min && kw.search_volume < filters.volume_min) return false;
            if (filters.volume_max && kw.search_volume > filters.volume_max) return false;

            // Word count filter
            if (filters.words_min && kw.word_count < filters.words_min) return false;
            if (filters.words_max && kw.word_count > filters.words_max) return false;

            // Title density filter
            if (filters.title_density_max && kw.title_density > filters.title_density_max) return false;

            // Competing products filter
            if (filters.competing_max && kw.competing_products > filters.competing_max) return false;

            // Include phrase filter
            if (filters.include_phrase) {
                const phrases = filters.include_phrase.split(',').map(p => p.trim().toLowerCase());
                const hasPhrase = phrases.some(p => kw.keyword.includes(p));
                if (!hasPhrase) return false;
            }

            // Exclude phrase filter
            if (filters.exclude_phrase) {
                const phrases = filters.exclude_phrase.split(',').map(p => p.trim().toLowerCase());
                const hasExcluded = phrases.some(p => kw.keyword.includes(p));
                if (hasExcluded) return false;
            }

            // Organic rank filter
            if (filters.rank_min || filters.rank_max) {
                const minRank = kw.min_organic_rank;
                if (filters.rank_min && (!minRank || minRank < filters.rank_min)) return false;
                if (filters.rank_max && (!minRank || minRank > filters.rank_max)) return false;
            }

            // Match type filter
            if (filters.match_type && filters.match_type !== 'all') {
                if (kw.match_type !== filters.match_type) return false;
            }

            // ASINs ranking filter (Advanced Rank Filter)
            if (filters.asins_ranking_min && kw.asins_ranking < filters.asins_ranking_min) return false;

            return true;
        });
    }

    /**
     * Quick filter presets
     */
    applyQuickFilter(keywords, filterName) {
        const presets = {
            'top_keywords': { volume_min: 1000, rank_max: 20 },
            'opportunity': { volume_min: 500, title_density_max: 5 },
            'low_competition': { competing_max: 10000, volume_min: 500 },
            'long_tail': { words_min: 4, volume_min: 100 },
            'not_ranking': { rank_min: 999 } // Keywords where we don't rank
        };

        const preset = presets[filterName];
        if (!preset) return keywords;

        return this.filterResults(keywords, preset);
    }

    /**
     * Update progress
     */
    updateProgress(percent, message, data = null) {
        if (this.onProgress) {
            this.onProgress(percent, message, data);
        }
    }

    /**
     * Stop the analysis
     */
    stop() {
        this.isRunning = false;
    }

    /**
     * Delay helper
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Export results to CSV format
     */
    exportToCSV(keywords) {
        const headers = [
            'Keyword',
            'Searches',
            'Top 3 Sales Share',
            'Sales',
            'Density',
            'KD',
            'Sponsored',
            'Words',
            'Ranking',
            ...this.asins.map(a => `Rank: ${a}`)
        ];

        const rows = keywords.map(kw => [
            kw.keyword,
            kw.search_volume || 0,
            kw.total_click_share != null ? `${kw.total_click_share.toFixed(0)}%` : '0%',
            kw.total_keyword_sales || 0,
            kw.title_density || 0,
            kw.difficulty_score ?? 0,
            kw.sponsored_count || 0,
            kw.word_count || 0,
            `${kw.asins_ranking || 0}/${this.asins.length}`,
            ...this.asins.map(a => kw.organic_ranks?.[a] ? `#${kw.organic_ranks[a]}` : '-')
        ]);

        const csv = [headers, ...rows]
            .map(row => row.map(cell => `"${cell}"`).join(','))
            .join('\n');

        return csv;
    }
}

// Make available globally
if (typeof window !== 'undefined') {
    window.CerebroAnalyzer = CerebroAnalyzer;
}
