// MagnetAnalyzer - Keyword Suggestion Tool (Helium 10 Magnet-like feature)
// Generates keyword ideas from a seed term WITHOUT product ranks
// Default marketplace: Amazon Egypt

class MagnetAnalyzer {
    constructor(marketplace = 'amazon.eg') {
        this.marketplace = marketplace;
        this.seedKeyword = '';
        this.results = [];
        this.isRunning = false;
        this.onProgress = null;

        // Configuration 
        // NOTE: ALL runtime values are fetched from backend (or fallback to defaults if fetch fails)
        this.config = {
            maxKeywords: 0,
            useAutocomplete: undefined,
            useRelated: undefined,
            useTitles: undefined,
            useAttributes: undefined,
            attributeProductCount: 0,
            attributeVariationScope: null, // 'seed', 'top_n', 'all'
            attributeVariationLimit: 0,
            delayBetweenRequests: 0
        };

        // Marketplace data with Egypt as default
        this.marketplaces = {
            'amazon.eg': { mid: 'ARBP9OOSHTCHU', lop: 'en_AE', currency: 'EGP', flag: '🇪🇬' },
            'amazon.com': { mid: 'ATVPDKIKX0DER', lop: 'en_US', currency: 'USD', flag: '🇺🇸' },
            'amazon.co.uk': { mid: 'A1F83G8C2ARO7P', lop: 'en_GB', currency: 'GBP', flag: '🇬🇧' },
            'amazon.de': { mid: 'A1PA6795UKMFR9', lop: 'de_DE', currency: 'EUR', flag: '🇩🇪' },
            'amazon.ae': { mid: 'A2VIGQ35RCS4UG', lop: 'en_AE', currency: 'AED', flag: '🇦🇪' },
            'amazon.sa': { mid: 'A17E79C6D8DWNP', lop: 'ar_SA', currency: 'SAR', flag: '🇸🇦' }
        };
    }

    /**
     * Convert Arabic-Indic digits to Latin digits
     */
    arabicToLatin(str) {
        if (!str) return '';
        const arabicNums = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        const latinNums = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        let result = str;
        arabicNums.forEach((arabic, index) => {
            result = result.replace(new RegExp(arabic, 'g'), latinNums[index]);
        });
        result = result.replace(/[\u066c，]/g, ','); // normalize thousands separators
        return result;
    }

    /**
     * Clean up invisible formatting/direction characters and collapse whitespaces
     */
    cleanArabicText(str) {
        if (!str) return '';
        return str
            .replace(/[\u200e\u200f\u202a-\u202e]/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    /**
     * Extract "Competing Products" from the search results page.
     * Prefer the results info bar; fall back to full-page text.
     */
    extractCompetingProducts(doc, fallback = 0) {
        if (!doc) return fallback || 0;

        const infoEl = doc.querySelector('[data-component-type="s-result-info-bar"], .s-breadcrumb');
        const rawText = infoEl?.textContent || doc.body?.textContent || '';
        const text = this.arabicToLatin(rawText);

        const patterns = [
            /(?:of|over|أكثر من|اكثر من|من)\s*(\d{1,3}(?:,\d{3})*)\s*(?:results|result|النتائج|نتائج|نتيجة)?/i,
            /(\d{1,3}(?:,\d{3})*)\s*(?:results|result|من النتائج|نتائج|نتيجة|النتائج|من نتيجة)/i,
        ];

        for (const re of patterns) {
            const m = text.match(re);
            if (m && m[1]) {
                const n = parseInt(m[1].replace(/,/g, ''), 10);
                if (!Number.isNaN(n) && n > 0) return n;
            }
        }

        return fallback || 0;
    }

    /**
     * Get available marketplaces with Egypt as default
     */
    static getMarketplaces() {
        return [
            { code: 'amazon.eg', name: 'Amazon Egypt', currency: 'EGP', flag: '🇪🇬', default: true },
            { code: 'amazon.com', name: 'Amazon US', currency: 'USD', flag: '🇺🇸', default: false },
            { code: 'amazon.co.uk', name: 'Amazon UK', currency: 'GBP', flag: '🇬🇧', default: false },
            { code: 'amazon.de', name: 'Amazon Germany', currency: 'EUR', flag: '🇩🇪', default: false },
            { code: 'amazon.ae', name: 'Amazon UAE', currency: 'AED', flag: '🇦🇪', default: false },
            { code: 'amazon.sa', name: 'Amazon Saudi Arabia', currency: 'SAR', flag: '🇸🇦', default: false }
        ];
    }

    /**
     * Main entry: Discover keywords from a seed term
     * @param {string} seedKeyword - The seed keyword to expand
     * @param {object} options - Configuration options
     * @param {Function} onProgress - Progress callback (percent, message, data)
     * @returns {Promise<object>} Complete analysis results
     */
    async analyze(seedKeyword, options = {}, onProgress = null) {
        if (!seedKeyword || seedKeyword.trim().length < 2) {
            throw new Error('Seed keyword must be at least 2 characters');
        }

        this.seedKeyword = seedKeyword.trim().toLowerCase();
        this.onProgress = onProgress;
        this.isRunning = true;
        this.results = [];

        // Merge options
        this.config = { ...this.config, ...options };

        // Fetch global settings to check for Test Mode
        this.testModeEnabled = false;
        try {
            const apiBase = window.API_CONFIG?.baseUrl || 'http://127.0.0.1:8000';
            const response = await fetch(`${apiBase}/api/settings?_t=${Date.now()}`);
            if (response.ok) {
                const configData = await response.json();
                const settings = configData.settings || {};
                this.testModeEnabled = settings.test_mode_enabled === true || settings.test_mode_enabled === 'true' || settings.test_mode_enabled === 1 || settings.test_mode_enabled === '1';
            }
        } catch (e) {
            console.warn('[Magnet] Failed to fetch global settings for test mode:', e);
        }

        // Fetch configurable settings from backend
        try {
            const backendSettings = await this.fetchBackendSettings();
            if (backendSettings) {
                this.config.attributeProductCount = backendSettings.attribute_product_count || 5;
                this.config.maxKeywords = backendSettings.max_keywords_limit || 1000;

                // Logic toggles
                this.config.useAutocomplete = backendSettings.use_autocomplete ?? true;
                this.config.useRelated = backendSettings.use_related ?? true;
                this.config.useTitles = backendSettings.use_titles ?? true;
                this.config.useAttributes = backendSettings.use_attributes ?? true;

                this.config.attributeVariationScope = backendSettings.attribute_variation_scope || 'top_n';
                this.config.attributeVariationLimit = backendSettings.attribute_variation_limit || 50;

                // Timing
                this.config.delayBetweenRequests = backendSettings.delay_between_requests || 300;

                console.log('[Magnet] Loaded backend settings:', backendSettings);
            }
        } catch (e) {
            console.warn('[Magnet] Could not fetch backend settings, using defaults:', e.message);
        }

        // Final safety check: ensure we have valid values even if backend fetch failed
        if (!this.config.maxKeywords || this.config.maxKeywords <= 0) this.config.maxKeywords = 1000;
        if (!this.config.attributeProductCount || this.config.attributeProductCount <= 0) this.config.attributeProductCount = 5;
        if (!this.config.delayBetweenRequests || this.config.delayBetweenRequests <= 0) this.config.delayBetweenRequests = 300;
        if (!this.config.attributeVariationScope) this.config.attributeVariationScope = 'top_n';
        if (!this.config.attributeVariationLimit || this.config.attributeVariationLimit <= 0) this.config.attributeVariationLimit = 50;

        // Ensure bools are true if they are undefined (meaning initialization failed)
        if (this.config.useAutocomplete === undefined) this.config.useAutocomplete = true;
        if (this.config.useRelated === undefined) this.config.useRelated = true;
        if (this.config.useTitles === undefined) this.config.useTitles = true;
        if (this.config.useAttributes === undefined) this.config.useAttributes = true;

        const startTime = Date.now();

        try {
            this.updateProgress(0, 'Starting keyword discovery...');

            // Step 0: Identify CORE NOUN from seed keyword
            // The core noun is the main product word (e.g., "scale" from "digital kitchen scale")
            // It's usually a noun, not an adjective/modifier
            const seedWords = this.seedKeyword.split(' ').filter(w => w.length > 2);
            const modifierWords = new Set(['digital', 'electronic', 'automatic', 'manual', 'smart', 'portable', 'mini', 'large', 'small', 'big', 'best', 'cheap', 'premium', 'professional', 'new', 'modern', 'classic', 'high', 'low', 'ultra', 'super', 'extra', 'multi', 'dual', 'triple', 'heavy', 'light', 'compact', 'wireless', 'bluetooth', 'usb', 'rechargeable', 'battery', 'powered', 'precision', 'accurate', 'lcd', 'led']);
            const unitWords = new Set(['kg', 'lb', 'lbs', 'gram', 'grams', 'oz', 'ounce', 'ounces', 'inch', 'inches', 'cm', 'mm', 'ml', 'liter']);

            // Find the core noun (last non-modifier, non-unit significant word)
            let coreNoun = '';
            for (let i = seedWords.length - 1; i >= 0; i--) {
                const word = seedWords[i].toLowerCase();
                if (!modifierWords.has(word) && !unitWords.has(word) && !/^\d+$/.test(word) && word.length > 2) {
                    coreNoun = word;
                    break;
                }
            }

            // Fallback: if no core noun found, use the longest word
            if (!coreNoun) {
                coreNoun = seedWords.reduce((a, b) => a.length > b.length ? a : b, '');
            }

            console.log(`[Magnet] Core noun identified: "${coreNoun}" from seed "${this.seedKeyword}"`);
            this.coreNoun = coreNoun; // Store for use in other methods

            // Step 1: Collect keywords from various sources
            const allKeywords = new Map(); // keyword -> source

            // Helper to add keyword with source tracking
            const addKeyword = (kw, source) => {
                if (allKeywords.has(kw)) {
                    const currentSources = allKeywords.get(kw);
                    if (!currentSources.includes(source)) {
                        allKeywords.set(kw, `${currentSources}, ${source}`);
                    }
                } else {
                    allKeywords.set(kw, source);
                }
            };

            // Add seed keyword first
            addKeyword(this.seedKeyword, 'seed');

            // Step 2: Get autocomplete suggestions
            if (this.config.useAutocomplete) {
                this.updateProgress(10, 'Fetching autocomplete suggestions...');
                let autocomplete = await this.getAutocompleteSuggestions(this.seedKeyword);
                if (this.testModeEnabled && autocomplete.length > 0) {
                    autocomplete = [autocomplete[0]];
                }
                autocomplete.forEach(kw => {
                    // Only add if contains core noun
                    if (kw.includes(coreNoun)) {
                        addKeyword(kw, 'autocomplete');
                    }
                });
                console.log(`[Magnet] Got ${autocomplete.length} autocomplete suggestions`);
            }

            // Step 3: Get related keywords (variations)
            if (this.config.useRelated) {
                this.updateProgress(25, 'Generating related keywords...');
                let related = await this.getRelatedKeywords(this.seedKeyword);
                if (this.testModeEnabled && related.length > 0) {
                    related = [related[0]];
                }
                related.forEach(kw => {
                    if (kw.includes(coreNoun)) {
                        addKeyword(kw, 'related');
                    }
                });
                console.log(`[Magnet] Got ${related.length} related keywords`);
            }

            // Step 4: Get keywords from SERP titles and collect ASINs for attribute scraping
            let topAsins = [];
            if (this.config.useTitles || this.config.useAttributes) {
                this.updateProgress(35, 'Extracting keywords from search results...');
                let { keywords: titleKeywords, asins } = await this.getTitleKeywordsWithAsins(this.seedKeyword);
                if (this.testModeEnabled) {
                    if (titleKeywords.length > 0) titleKeywords = [titleKeywords[0]];
                    if (asins.length > 0) asins = [asins[0]];
                }
                topAsins = asins;

                if (this.config.useTitles) {
                    titleKeywords.forEach(kw => {
                        if (kw.includes(coreNoun)) {
                            addKeyword(kw, 'title');
                        }
                    });
                    console.log(`[Magnet] Got ${titleKeywords.length} title keywords`);
                }
            }

            // Step 4.5: Get attribute-based keywords from top product pages
            if (this.config.useAttributes && topAsins.length > 0) {
                this.updateProgress(40, 'Extracting attributes from top products...');
                const productCount = this.testModeEnabled ? 1 : this.config.attributeProductCount;
                const attrResult = await this.getAttributeKeywords(topAsins, productCount);
                let { keywords: attrKeywords, values: attrValues } = attrResult;

                if (this.testModeEnabled) {
                    if (attrKeywords.length > 0) attrKeywords = [attrKeywords[0]];
                    if (attrValues.length > 0) attrValues = [attrValues[0]];
                }

                console.log(`[Magnet DEBUG] Attribute Variation Scope: ${this.config.attributeVariationScope}`);
                console.log(`[Magnet DEBUG] Attribute Values extracted: ${attrValues.length}`, attrValues);

                // 1. Add direct attribute keywords (e.g. "Scale Compact") - SKIP if scope is 'seed'
                // When scope is 'seed', we only want seed + attribute combinations
                if (this.config.attributeVariationScope !== 'seed') {
                    attrKeywords.forEach(kw => {
                        if (kw.includes(coreNoun)) {
                            addKeyword(kw, 'attribute');
                        }
                    });
                }

                // 2. Cross-multiply attributes with discovered keywords based on scope
                let baseKeywords = [];
                const allDiscovered = Array.from(allKeywords.keys());

                switch (this.config.attributeVariationScope) {
                    case 'seed':
                        baseKeywords = [this.seedKeyword];
                        console.log(`[Magnet] Scope 'seed' - only combining with: "${this.seedKeyword}"`);
                        break;
                    case 'all':
                        baseKeywords = allDiscovered;
                        break;
                    case 'top_n':
                    default:
                        // Use only the top N discovered keywords to keep speed reasonable
                        const limit = this.config.attributeVariationLimit || 50;
                        baseKeywords = allDiscovered.slice(0, limit);
                        console.log(`[Magnet] Scope top_n limit applied: ${limit}`);
                        break;
                }

                console.log(`[Magnet] Generating attribute combinations for ${baseKeywords.length} keywords (Scope: ${this.config.attributeVariationScope})`);

                baseKeywords.forEach(baseKw => {
                    attrValues.forEach(val => {
                        // Avoid duplicates: if base keyword already contains the attribute value, skip
                        if (baseKw.includes(val.toLowerCase())) return;

                        // Create combination: Base Keyword + Attribute Value
                        // e.g., "Digital Kitchen Scale" + "Pink" -> "Digital Kitchen Scale Pink"
                        const combination = `${baseKw} ${val}`.toLowerCase();

                        addKeyword(combination, 'attribute'); // Mark as attribute-derived
                    });
                });

                console.log(`[Magnet] Total keywords after attribute expansion: ${allKeywords.size}`);
            }

            // Limit keywords
            let keywordsToProcess = Array.from(allKeywords.entries());
            if (this.testModeEnabled) {
                // Keep only exactly one keyword for every primary match type to make testing extremely fast
                const seenTypes = new Set();
                const filtered = [];
                for (const [kw, matchType] of keywordsToProcess) {
                    const primaryType = matchType.split(',')[0].trim();
                    if (!seenTypes.has(primaryType)) {
                        seenTypes.add(primaryType);
                        filtered.push([kw, matchType]);
                    }
                }
                keywordsToProcess = filtered;
                console.log('[Magnet] Test Mode Active: Filtered down to 1 keyword per type:', keywordsToProcess);
            } else {
                keywordsToProcess = keywordsToProcess.slice(0, this.config.maxKeywords);
            }

            console.log(`[Magnet] Processing ${keywordsToProcess.length} unique keywords (all contain "${coreNoun}")`);


            // Step 5: Enrich each keyword with metrics
            this.updateProgress(45, 'Enriching keywords with metrics...');
            const enrichedKeywords = [];

            for (let i = 0; i < keywordsToProcess.length; i++) {
                if (!this.isRunning) break;

                const [keyword, matchType] = keywordsToProcess[i];
                const progress = 45 + Math.round((i / keywordsToProcess.length) * 45);
                this.updateProgress(progress, `Analyzing "${keyword.substring(0, 30)}..."`);

                try {
                    const metrics = await this.getKeywordMetrics(keyword);
                    enrichedKeywords.push({
                        keyword,
                        match_type: matchType,
                        word_count: keyword.split(' ').length,
                        relevance_score: this.calculateRelevance(keyword, this.seedKeyword),
                        ...metrics
                    });
                } catch (error) {
                    console.warn(`[Magnet] Error getting metrics for "${keyword}":`, error.message);
                    // Still include with default metrics
                    enrichedKeywords.push({
                        keyword,
                        match_type: matchType,
                        word_count: keyword.split(' ').length,
                        relevance_score: this.calculateRelevance(keyword, this.seedKeyword),
                        search_volume: 0,
                        magnet_iq_score: 0,
                        competing_products: 0,
                        title_density: 0,
                        cpr_8day: 0,
                        cpr_total: 0,
                        keyword_sales: 0,
                        avg_price: 0,
                        avg_reviews: 0,
                        sponsored_count: 0
                    });
                }

                // Delay between requests
                if (i < keywordsToProcess.length - 1 && this.isRunning) {
                    await this.delay(this.config.delayBetweenRequests);
                }
            }

            // Step 6: Filter out generic/short keywords
            this.updateProgress(92, 'Filtering generic keywords...');
            const filteredKeywords = enrichedKeywords.filter(kw => {
                if (this.testModeEnabled) return true;

                // Must have at least 2 words (single words are too generic)
                if (kw.word_count < 2) return false;

                // Minimum keyword length of 5 characters
                if (kw.keyword.length < 5) return false;

                return true;
            });

            // Step 7: Sort by IQ score
            this.updateProgress(95, 'Sorting results...');
            const sortedKeywords = filteredKeywords.sort((a, b) =>
                (b.magnet_iq_score || 0) - (a.magnet_iq_score || 0)
            );

            this.results = sortedKeywords;
            this.updateProgress(100, 'Analysis complete!');

            const duration = Math.round((Date.now() - startTime) / 1000);

            return {
                success: true,
                marketplace: this.marketplace,
                seed_keyword: this.seedKeyword,
                total_keywords: sortedKeywords.length,
                duration_seconds: duration,
                keywords: sortedKeywords
            };

        } catch (error) {
            this.isRunning = false;
            throw error;
        }
    }

    /**
     * Get Amazon autocomplete suggestions
     */
    async getAutocompleteSuggestions(seed) {
        if (this.testModeEnabled) {
            return [`${seed} manual`, `${seed} automatic`];
        }
        const suggestions = [];
        const domain = this.marketplace.replace('www.', '').replace('amazon.', '');
        const mkp = this.marketplaces[this.marketplace] || this.marketplaces['amazon.eg'];

        // Detect if language is Arabic
        const url = window.location.href;
        const hasArUrl = url.includes('language=ar') || url.includes('/ar/');
        const isRtl = document.documentElement.getAttribute('dir') === 'rtl' || 
                      document.body?.getAttribute('dir') === 'rtl' || 
                      document.documentElement.style.direction === 'rtl' ||
                      document.body?.style.direction === 'rtl' ||
                      document.documentElement.classList.contains('a-rtl');
        const hasArLang = document.documentElement.lang && document.documentElement.lang.startsWith('ar');
        const isArabic = !!(hasArUrl || isRtl || hasArLang);
        let lop = mkp.lop;
        if (isArabic) {
            lop = this.marketplace === 'amazon.sa' ? 'ar_SA' : 'ar_AE';
        }

        // Get suggestions for seed and variations
        const prefixes = [
            seed,
            seed + ' ',
            seed + ' a', seed + ' b', seed + ' c', seed + ' d', seed + ' e',
            seed + ' for', seed + ' with', seed + ' best'
        ];

        for (const prefix of prefixes.slice(0, 8)) {
            try {
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
                    'lop': lop,
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
                                const kw = s.value?.toLowerCase().trim();
                                if (kw && kw.length > 2 && !suggestions.includes(kw)) {
                                    suggestions.push(kw);
                                }
                            });
                    }
                }
            } catch (e) {
                console.warn(`[Magnet] Autocomplete error for "${prefix}":`, e.message);
            }

            await this.delay(100);
        }

        return [...new Set(suggestions)];
    }

    /**
     * Generate related keyword variations
     * Only generates full phrase variations, no single words
     */
    async getRelatedKeywords(seed) {
        const related = [];
        const words = seed.split(' ').filter(w => w.length > 2);

        // Common modifiers - only add full phrases
        const prefixModifiers = ['best', 'cheap', 'premium', 'professional', 'top', 'new', 'mini', 'large'];
        const suffixModifiers = ['for sale', 'online', 'shop', 'price', 'buy', 'set', 'kit', 'pack'];

        // Add prefix modifiers (full phrases only)
        prefixModifiers.forEach(mod => {
            const kw = `${mod} ${seed}`.toLowerCase();
            if (!related.includes(kw)) related.push(kw);
        });

        // Add suffix modifiers (full phrases only)
        suffixModifiers.forEach(mod => {
            const kw = `${seed} ${mod}`.toLowerCase();
            if (!related.includes(kw)) related.push(kw);
        });

        // Add word order variations if multi-word (but keep 2+ words)
        if (words.length >= 3) {
            // Try reversing order (keep all words)
            related.push(words.slice().reverse().join(' '));

            // Try key combinations (2+ words)
            if (words.length >= 3) {
                // First 2 words + last word combinations
                related.push(`${words[0]} ${words[words.length - 1]}`);
                related.push(`${words[words.length - 2]} ${words[words.length - 1]}`);
            }
        }

        // DO NOT add single words - they're too generic
        // The old code added single words like 'digital', 'kitchen', 'scale' which is wrong

        return [...new Set(related)].slice(0, 20);
    }

    /**
     * Extract attribute-based keywords from top product pages
     * Scrapes the "Product Overview" table for values like Color, Material, Special Features
     * @param {Array} topProductAsins - Array of ASINs to scrape
     * @param {number} productCount - Number of products to scrape (configurable from backend)
     */
    async getAttributeKeywords(topProductAsins, productCount = 5) {
        if (this.testModeEnabled) {
            return {
                keywords: [`${this.coreNoun} compact`, `${this.coreNoun} light`],
                values: ['compact', 'light']
            };
        }
        const attributeKeywords = [];
        const origin = window.location.origin;

        // DEBUG: Collect ALL raw attributes from ALL products
        const allRawAttributes = [];

        // Only interested in certain attribute types that make good keywords
        const usefulAttributes = new Set([
            'color', 'material', 'special feature', 'special features', 'style',
            'back style', 'finish type', 'pattern', 'shape', 'fabric type',
            'size', 'power source', 'power source type', 'connectivity',
            'display type', 'recommended uses for product', 'item form',
            'form factor', 'weigh scale type', 'room type', 'theme', 'occasion'
        ]);

        // Values that are too generic or illogical to be useful as keywords
        const genericValues = new Set([
            // Generic terms
            'generic', 'standard', 'default', 'basic', 'regular', 'normal',
            'n/a', 'na', 'none', 'other', 'various', 'multi', 'multiple',
            // Units/counts
            '1', '1.0', 'set', 'piece', 'count', 'pack', 'unit', '1 count', '1.0 count',
            // Meaningless colors
            'one color', 'mix', 'mixed', 'multicolor', 'multicolour', 'assorted',
            // Meaningless demographics
            'adult', 'adults', 'all ages', 'universal',
            // Operation modes
            'manual', 'automatic', 'auto',
            // Power sources (too common)
            'battery', 'corded electric', 'ac adapter',
            // Components (not keywords)
            'valve', 'battery', 'ac adapter',
            // Other
            'wearable', 'food'
        ]);

        const asinsToProcess = topProductAsins.slice(0, productCount);
        const uniqueValues = new Set();
        console.log(`[Magnet] Fetching attributes from ${asinsToProcess.length} product pages...`);

        for (const asin of asinsToProcess) {
            // ... (network code remains same) ...
            if (!this.isRunning) break;

            try {
                // ... fetch logic ...
                const productUrl = `${origin}/dp/${asin}`;
                const response = await fetch(productUrl, {
                    method: 'GET',
                    credentials: 'include',
                    headers: { 'Accept': 'text/html,application/xhtml+xml' }
                });

                if (!response.ok) continue;

                const html = await response.text();
                if (html.includes('captcha')) continue;

                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const rows = doc.querySelectorAll('table.a-normal.a-spacing-micro tr[class*="po-"]');

                rows.forEach(row => {
                    const rowClass = row.className || '';
                    const attrTypeMatch = rowClass.match(/po-([a-z_\.]+)/i);
                    if (!attrTypeMatch) return;

                    const attrType = attrTypeMatch[1].toLowerCase().replace(/[._]/g, ' ').trim();
                    const valueEl = row.querySelector('td.a-span9 .po-break-word');
                    let rawValue = valueEl?.textContent?.trim() || '';

                    allRawAttributes.push({ asin, type: attrType, value: rawValue });

                    const isUseful = [...usefulAttributes].some(ua => attrType.includes(ua) || ua.includes(attrType));
                    if (!isUseful) return;

                    let value = rawValue.toLowerCase();
                    if (!value || value.length < 2) return;
                    if (genericValues.has(value)) return;

                    // Handle color combinations with slashes
                    let valuesToProcess = [value];
                    if (value.includes('/')) {
                        valuesToProcess = value.split('/').map(v => v.trim()).filter(v => v.length >= 2);
                    }

                    valuesToProcess.forEach(val => {
                        if (genericValues.has(val)) return;
                        if (/^\d+(\.\d+)?$/.test(val)) return;
                        if (/\d+\s*(centimeters?|grams?|kilograms?|inches?|millimeters?)/.test(val)) return;

                        val = val
                            .replace(/\d+(\.\d+)?\s*(kg|lb|gram|oz|cm|mm|inch|ml|liter|w|watt|l|w|th)s?/gi, '')
                            .replace(/\d+[dx×]\d+([dx×]\d+)?/gi, '')
                            .replace(/\d+l\s*x\s*\d+w/gi, '')
                            .trim();

                        val = val.replace(/\s+(for|with|and|by|of|in|to)\s*$/i, '').trim();

                        if (!val || val.length < 3) return;

                        let keyword;
                        if (val.includes(this.coreNoun)) {
                            keyword = val.toLowerCase();
                        } else {
                            keyword = `${this.coreNoun} ${val}`.toLowerCase();
                        }

                        if (keyword.split(' ').length >= 2) {
                            if (!attributeKeywords.includes(keyword)) attributeKeywords.push(keyword);
                            uniqueValues.add(val); // Capture the clean value
                        }
                    });
                });

                await this.delay(200);

            } catch (e) {
                console.warn(`[Magnet] Error fetching attributes for ASIN ${asin}:`, e.message);
            }
        }

        console.log(`[Magnet DEBUG] All scraped attributes (${allRawAttributes.length} total):`, allRawAttributes);
        console.table(allRawAttributes);
        console.log(`[Magnet DEBUG] Final attribute keywords:`, attributeKeywords);

        return {
            keywords: [...new Set(attributeKeywords)],
            values: [...uniqueValues]
        };
    }

    /**
     * Extract keywords from SERP product titles
     * Uses title frequency analysis to identify core keywords (like Helium 10)
     */
    async getTitleKeywords(seed) {
        const keywords = [];

        // Junk phrases to filter out
        const junkPhrases = [
            'results for', 'showing results', 'sponsored', 'best seller',
            'amazon choice', 'climate pledge', 'limited time', 'shop now',
            'see all', 'view more', 'learn more', 'add to cart', 'buy now',
            'free delivery', 'prime', 'over results', 'previous page', 'next page'
        ];

        // Common stop words to ignore in frequency analysis
        const stopWords = new Set([
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been',
            'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
            'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those',
            'it', 'its', 'i', 'you', 'he', 'she', 'we', 'they', 'my', 'your', 'his',
            'her', 'our', 'their', 'what', 'which', 'who', 'whom', 'when', 'where',
            'why', 'how', 'all', 'each', 'every', 'both', 'few', 'more', 'most',
            'other', 'some', 'such', 'no', 'not', 'only', 'same', 'so', 'than',
            'too', 'very', 'just', 'also', 'now', 'new', 'one', 'two', 'per', 'set'
        ]);

        try {
            const origin = window.location.origin;
            const urlParams = new URLSearchParams(window.location.search);
            const langParam = urlParams.get('language');
            const langSuffix = langParam ? `&language=${langParam}` : '';
            const searchUrl = `${origin}/s?k=${encodeURIComponent(seed)}${langSuffix}`;

            const response = await fetch(searchUrl, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Accept': 'text/html,application/xhtml+xml',
                }
            });

            if (response.ok) {
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Extract titles ONLY from product cards with data-asin
                const productCards = doc.querySelectorAll('[data-asin]:not([data-asin=""])');
                const titles = [];

                // Word frequency counter for finding CORE keywords
                const wordFrequency = new Map();

                productCards.forEach(card => {
                    const titleEl = card.querySelector('h2 a span, h2 span, .a-size-base-plus, .a-size-medium');
                    if (titleEl) {
                        const title = titleEl.textContent?.trim().toLowerCase() || '';
                        // Filter out junk and short titles
                        if (title.length > 10 && !junkPhrases.some(jp => title.includes(jp))) {
                            titles.push(title);

                            // Count word frequency for core keyword detection
                            const words = title
                                .replace(/[^\w\s-]/g, ' ')
                                .split(/\s+/)
                                .filter(w => w.length > 2 && !stopWords.has(w) && !/^\d+$/.test(w));

                            words.forEach(word => {
                                wordFrequency.set(word, (wordFrequency.get(word) || 0) + 1);
                            });
                        }
                    }
                });

                // Identify CORE keywords (most frequently mentioned in titles)
                // These are the words that define this product category
                const sortedWords = [...wordFrequency.entries()]
                    .sort((a, b) => b[1] - a[1])
                    .slice(0, 10) // Top 10 most frequent words
                    .map(([word]) => word);

                console.log('[Magnet] Frequent words in titles:', sortedWords.slice(0, 5));

                // Use the SEED's core noun (set in analyze method) as the required word
                // This ensures all keywords are relevant to the user's search
                const requiredWord = this.coreNoun || sortedWords[0] || seed.split(' ')[0];
                console.log(`[Magnet] Requiring keywords to contain: "${requiredWord}"`);

                // Extract n-grams that contain the REQUIRED core noun
                titles.forEach(title => {
                    const words = title
                        .replace(/[^\w\s-]/g, ' ')
                        .split(/\s+/)
                        .filter(w => w.length > 2 && !/^\d+$/.test(w));

                    // 2-word combinations (must contain the required word)
                    for (let i = 0; i < words.length - 1 && keywords.length < 30; i++) {
                        const ngram = `${words[i]} ${words[i + 1]}`;
                        if (ngram.length > 6 && ngram.includes(requiredWord) && !keywords.includes(ngram)) {
                            keywords.push(ngram);
                        }
                    }

                    // 3-word combinations (must contain the required word)
                    for (let i = 0; i < words.length - 2 && keywords.length < 50; i++) {
                        const ngram = `${words[i]} ${words[i + 1]} ${words[i + 2]}`;
                        if (ngram.length > 10 && ngram.includes(requiredWord) && !keywords.includes(ngram)) {
                            keywords.push(ngram);
                        }
                    }
                });
            }
        } catch (e) {
            console.warn(`[Magnet] Title extraction error:`, e.message);
        }

        return [...new Set(keywords)];
    }

    /**
     * Extract keywords from SERP product titles AND collect ASINs
     * Returns both keywords for title-based discovery and ASINs for attribute scraping
     */
    async getTitleKeywordsWithAsins(seed) {
        if (this.testModeEnabled) {
            return {
                keywords: [`${seed} professional`, `${seed} premium`],
                asins: ['B0811P9G8C']
            };
        }
        const keywords = [];
        const asins = [];

        // Junk phrases to filter out
        const junkPhrases = [
            'results for', 'showing results', 'sponsored', 'best seller',
            'amazon choice', 'climate pledge', 'limited time', 'shop now',
            'see all', 'view more', 'learn more', 'add to cart', 'buy now',
            'free delivery', 'prime', 'over results', 'previous page', 'next page'
        ];

        // Common stop words to ignore in frequency analysis
        const stopWords = new Set([
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been',
            'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
            'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those',
            'it', 'its', 'i', 'you', 'he', 'she', 'we', 'they', 'my', 'your', 'his',
            'her', 'our', 'their', 'what', 'which', 'who', 'whom', 'when', 'where',
            'why', 'how', 'all', 'each', 'every', 'both', 'few', 'more', 'most',
            'other', 'some', 'such', 'no', 'not', 'only', 'same', 'so', 'than',
            'too', 'very', 'just', 'also', 'now', 'new', 'one', 'two', 'per', 'set'
        ]);

        try {
            const origin = window.location.origin;
            const urlParams = new URLSearchParams(window.location.search);
            const langParam = urlParams.get('language');
            const langSuffix = langParam ? `&language=${langParam}` : '';
            const searchUrl = `${origin}/s?k=${encodeURIComponent(seed)}${langSuffix}`;

            const response = await fetch(searchUrl, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Accept': 'text/html,application/xhtml+xml',
                }
            });

            if (response.ok) {
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Extract titles and ASINs from product cards
                const productCards = doc.querySelectorAll('[data-asin]:not([data-asin=""])');
                const titles = [];

                // Word frequency counter for finding CORE keywords
                const wordFrequency = new Map();

                productCards.forEach(card => {
                    // Collect ASIN for attribute scraping
                    const asin = card.getAttribute('data-asin');
                    if (asin && asin.length === 10 && !asins.includes(asin)) {
                        // Only collect non-sponsored organic results (first 10)
                        if (asins.length < 10 && !card.querySelector('[data-component-type="sp-sponsored-result"]')) {
                            asins.push(asin);
                        }
                    }

                    const titleEl = card.querySelector('h2 a span, h2 span, .a-size-base-plus, .a-size-medium');
                    if (titleEl) {
                        const title = titleEl.textContent?.trim().toLowerCase() || '';
                        // Filter out junk and short titles
                        if (title.length > 10 && !junkPhrases.some(jp => title.includes(jp))) {
                            titles.push(title);

                            // Count word frequency for core keyword detection
                            const words = title
                                .replace(/[^\w\s-]/g, ' ')
                                .split(/\s+/)
                                .filter(w => w.length > 2 && !stopWords.has(w) && !/^\d+$/.test(w));

                            words.forEach(word => {
                                wordFrequency.set(word, (wordFrequency.get(word) || 0) + 1);
                            });
                        }
                    }
                });

                // Identify CORE keywords (most frequently mentioned in titles)
                const sortedWords = [...wordFrequency.entries()]
                    .sort((a, b) => b[1] - a[1])
                    .slice(0, 10)
                    .map(([word]) => word);

                console.log('[Magnet] Frequent words in titles:', sortedWords.slice(0, 5));
                console.log(`[Magnet] Collected ${asins.length} ASINs for attribute scraping`);

                // Use the SEED's core noun as the required word
                const requiredWord = this.coreNoun || sortedWords[0] || seed.split(' ')[0];
                console.log(`[Magnet] Requiring keywords to contain: "${requiredWord}"`);

                // Extract n-grams that contain the REQUIRED core noun
                titles.forEach(title => {
                    const words = title
                        .replace(/[^\w\s-]/g, ' ')
                        .split(/\s+/)
                        .filter(w => w.length > 2 && !/^\d+$/.test(w));

                    // 2-word combinations (must contain the required word)
                    for (let i = 0; i < words.length - 1 && keywords.length < 30; i++) {
                        const ngram = `${words[i]} ${words[i + 1]}`;
                        if (ngram.length > 6 && ngram.includes(requiredWord) && !keywords.includes(ngram)) {
                            keywords.push(ngram);
                        }
                    }

                    // 3-word combinations (must contain the required word)
                    for (let i = 0; i < words.length - 2 && keywords.length < 50; i++) {
                        const ngram = `${words[i]} ${words[i + 1]} ${words[i + 2]}`;
                        if (ngram.length > 10 && ngram.includes(requiredWord) && !keywords.includes(ngram)) {
                            keywords.push(ngram);
                        }
                    }
                });
            }
        } catch (e) {
            console.warn(`[Magnet] Title extraction error:`, e.message);
        }

        return {
            keywords: [...new Set(keywords)],
            asins: asins
        };
    }

    /**
     * Helper to get a deterministic hash from a string to generate stable mock data
     */
    getDeterministicHash(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = (hash << 5) - hash + str.charCodeAt(i);
            hash |= 0; // Convert to 32bit integer
        }
        return Math.abs(hash);
    }

    /**
     * Get metrics for a single keyword (WITHOUT rank - key difference from Cerebro)
     */
    async getKeywordMetrics(keyword) {
        const origin = window.location.origin;
        const urlParams = new URLSearchParams(window.location.search);
        const langParam = urlParams.get('language');
        const langSuffix = langParam ? `&language=${langParam}` : '';
        const searchUrl = `${origin}/s?k=${encodeURIComponent(keyword)}${langSuffix}`;

        try {
            const response = await fetch(searchUrl, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Accept': 'text/html,application/xhtml+xml',
                }
            });

            if (!response.ok) {
                return this.getDefaultMetrics();
            }

            const html = await response.text();

            if (html.includes('captcha') || html.includes('Enter the characters')) {
                console.warn(`[Magnet] Captcha detected for "${keyword}"`);
                return this.getDefaultMetrics();
            }

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Get competing products count
            let competingProducts = this.extractCompetingProducts(doc, 0);

            // Get products for analysis using precise selectors identical to SerpParser
            const mainResultsContainer = doc.querySelector('.s-main-slot.s-result-list.s-search-results');
            let productsList = [];
            
            if (mainResultsContainer) {
                const productCards = mainResultsContainer.querySelectorAll('[data-component-type="s-search-result"]');
                productsList = Array.from(productCards)
                    .filter(card => {
                        const asin = card.getAttribute('data-asin');
                        return asin && asin.length === 10;
                    })
                    .slice(0, 20);
            }

            // Safe fallback if the main results container wasn't found or was empty
            if (productsList.length === 0) {
                const allProducts = doc.querySelectorAll('[data-asin]:not([data-asin=""])');
                productsList = Array.from(allProducts)
                    .filter(card => {
                        const asin = card.getAttribute('data-asin');
                        return asin && asin.length === 10;
                    })
                    .slice(0, 20);
            }

            // Safe fallback if we couldn't parse the SERP counter
            if (!competingProducts || competingProducts <= 0) {
                const allProducts = doc.querySelectorAll('[data-asin]:not([data-asin=""])');
                competingProducts = allProducts.length || productsList.length;
            }

            // Calculate metrics and parsed products for difficulty using SerpParser parity helpers
            let titleDensity = 0;
            let totalPrice = 0;
            let totalReviews = 0;
            let sponsoredCount = 0;
            let priceCount = 0;
            let reviewCount = 0;

            const keywordLower = this.cleanArabicText(keyword.toLowerCase());
            const parsedProducts = [];

            productsList.forEach(product => {
                const price = this.extractProductPrice(product);
                if (!price || price <= 0) {
                    return; // Skip unavailable products matching SerpParser parity!
                }
                const title = this.extractProductTitle(product);
                const rating = this.extractProductRating(product);
                const reviews = this.extractProductReviewCount(product);
                const brand = this.extractProductBrand(product);
                const is_sponsored = this.isProductSponsored(product);

                // Title density
                if (title && title.toLowerCase().includes(keywordLower)) {
                    titleDensity++;
                }

                // Price
                if (price > 0) {
                    totalPrice += price;
                    priceCount++;
                }

                // Reviews
                if (reviews > 0) {
                    totalReviews += reviews;
                    reviewCount++;
                }

                // Sponsored
                if (is_sponsored) {
                    sponsoredCount++;
                }

                parsedProducts.push({
                    title,
                    price,
                    reviews,
                    rating,
                    brand,
                    is_sponsored
                });
            });

            const avgPrice = priceCount > 0 ? Math.round(totalPrice / priceCount * 100) / 100 : 0;
            const avgReviews = reviewCount > 0 ? Math.round(totalReviews / reviewCount) : 0;

            // Estimate search volume from competing products
            const searchVolume = this.estimateSearchVolume(competingProducts, avgReviews);

            const difficulty = this.calculateKeywordDifficulty(parsedProducts);
            const difficultyScore = difficulty.score;

            // CPR calculations
            const cpr8day = Math.ceil((searchVolume * 0.02) / 8);
            const cprTotal = cpr8day * 8;

            // Keyword sales estimate
            const keywordSales = Math.round(searchVolume * 0.10 * 0.15); // 10% CVR, 15% click share

            return {
                search_volume: searchVolume,
                magnet_iq_score: difficultyScore,
                competing_products: competingProducts,
                title_density: titleDensity,
                cpr_8day: cpr8day,
                cpr_total: cprTotal,
                keyword_sales: keywordSales,
                avg_price: avgPrice,
                avg_reviews: avgReviews,
                sponsored_count: sponsoredCount
            };

        } catch (e) {
            console.error(`[Magnet] Metrics error for "${keyword}":`, e.message);
            return this.getDefaultMetrics();
        }
    }

    /**
     * Default metrics when scraping fails
     */
    getDefaultMetrics() {
        return {
            search_volume: 0,
            magnet_iq_score: 0,
            competing_products: 0,
            title_density: 0,
            cpr_8day: 0,
            cpr_total: 0,
            keyword_sales: 0,
            avg_price: 0,
            avg_reviews: 0,
            sponsored_count: 0
        };
    }

    /**
     * Estimate search volume from competition and review data
     */
    estimateSearchVolume(competingProducts, avgReviews) {
        // Heuristic: More products and reviews = higher search volume
        if (competingProducts === 0) return 0;

        // Base estimate from product count
        let volume = Math.sqrt(competingProducts) * 50;

        // Adjust based on reviews (more reviews = more established market)
        if (avgReviews > 1000) volume *= 1.5;
        else if (avgReviews > 500) volume *= 1.2;
        else if (avgReviews > 100) volume *= 1.0;
        else volume *= 0.8;

        return Math.round(volume);
    }

    /**
     * Calculate Keyword Difficulty (KD) Score 0-100 using the same formula as in Market Analysis
     */
    calculateKeywordDifficulty(products) {
        const top10 = products.slice(0, 10);
        if (top10.length === 0) {
            return { score: 50, level: 'medium' };
        }

        // 1. Listing Strength (35% weight)
        let totalReviews = 0;
        let totalRating = 0;
        let reviewCount = 0;

        top10.forEach(p => {
            if (p.reviews !== undefined && p.reviews !== null) {
                totalReviews += p.reviews;
                reviewCount++;
            }
            if (p.rating !== undefined && p.rating !== null) {
                totalRating += p.rating;
            }
        });

        const avgReviews = reviewCount > 0 ? totalReviews / reviewCount : 0;
        const avgRating = reviewCount > 0 ? totalRating / reviewCount : 4.0;
        const listingStrength = Math.min(100, (avgReviews / 50) * (avgRating / 5) * 100);

        // 2. Ad Density (25% weight)
        const sponsoredCount = top10.filter(p => p.is_sponsored).length;
        const adDensity = (sponsoredCount / Math.max(top10.length, 1)) * 100;

        // 3. Review Barrier (25% weight)
        const reviews = top10.map(p => p.reviews || 0).sort((a, b) => a - b);
        const medianReviews = reviews[Math.floor(reviews.length / 2)] || 0;
        const reviewBarrier = Math.min(100, (medianReviews / 50) * 100);

        // 4. Brand Dominance (15% weight)
        const placeholderBrands = new Set(['generic', 'unbranded', 'no brand', 'nobrand', 'unknown', 'n/a', 'na', '-', '', 'جينيريك', 'بدون علامة تجارية']);
        const brands = top10
            .map(p => (p.brand || '').trim().toLowerCase())
            .filter(b => b.length > 1 && !placeholderBrands.has(b));
        
        let brandDominance = 0;
        if (brands.length > 0) {
            const brandCounts = {};
            let maxBrandCount = 0;
            brands.forEach(b => {
                brandCounts[b] = (brandCounts[b] || 0) + 1;
                if (brandCounts[b] > maxBrandCount) {
                    maxBrandCount = brandCounts[b];
                }
            });
            brandDominance = Math.round((maxBrandCount / brands.length) * 100);
        }

        const kdScore = Math.round(
            ((listingStrength || 0) * 0.35) +
            ((adDensity || 0) * 0.25) +
            ((reviewBarrier || 0) * 0.25) +
            ((brandDominance || 0) * 0.15)
        );
        const score = isNaN(kdScore) ? 50 : Math.max(0, Math.min(100, kdScore));

        let level = 'medium';
        if (score < 20) level = 'very_easy';
        else if (score < 40) level = 'easy';
        else if (score < 60) level = 'moderate';
        else if (score < 80) level = 'hard';
        else level = 'very_hard';

        console.log(`========== [Magnet] KD Calculation Breakdown ==========`);
        console.log('Top 10 Products parsed:', top10.map((p, i) => ({
            index: i + 1,
            title: p.title?.substring(0, 40) + '...',
            price: p.price,
            rating: p.rating,
            reviews: p.reviews,
            brand: p.brand,
            is_sponsored: p.is_sponsored
        })));
        console.log('Listing Strength Sub-score:', { avgReviews, avgRating, listingStrength });
        console.log('Ad Density Sub-score:', { sponsoredCount, adDensity });
        console.log('Review Barrier Sub-score:', { medianReviews, reviewBarrier });
        console.log('Brand Dominance Sub-score:', { brands, brandDominance });
        console.log('Calculated Final Difficulty Score:', score);
        console.log(`=======================================================`);

        return { score, level };
    }

    /**
     * Advanced product parsing helpers translated from SerpParser with full parity
     */
    convertNumerals(text) {
        if (!text) return '';
        const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        let clean = text.toString();
        // Replace Arabic decimal and thousand separators
        clean = clean.replace(/٫/g, '.').replace(/٬/g, '');
        // Replace Eastern Arabic numerals
        clean = clean.replace(/[٠-٩]/g, d => arabicDigits.indexOf(d));
        return clean;
    }

    extractProductTitle(card) {
        const h2Element = card.querySelector('h2');
        if (h2Element) {
            const ariaLabel = h2Element.getAttribute('aria-label');
            if (ariaLabel) {
                return ariaLabel.replace(/^Sponsored Ad\s*[–-]\s*/i, '').trim();
            }
            const titleSpan = h2Element.querySelector('span');
            if (titleSpan) {
                let title = titleSpan.textContent?.trim() || '';
                title = title.replace(/^Sponsored\s*/gi, '').trim();
                if (title && title !== 'Sponsored') {
                    return title;
                }
            }
        }
        const titleEl = card.querySelector('.s-title-instructions-style span, [data-cy="title-recipe"] span');
        let title = titleEl?.textContent?.trim() || '';
        title = title.replace(/^Sponsored\s*/gi, '').trim();
        return title;
    }

    extractProductPrice(card) {
        const priceWhole = card.querySelector('.a-price .a-price-whole');
        const priceFraction = card.querySelector('.a-price .a-price-fraction');
        if (priceWhole) {
            const whole = this.convertNumerals(priceWhole.textContent).replace(/[^\d]/g, '');
            const fraction = this.convertNumerals(priceFraction?.textContent || '').replace(/[^\d]/g, '') || '00';
            const val = parseFloat(`${whole}.${fraction}`);
            return isNaN(val) ? 0 : val;
        }
        const priceOffscreen = card.querySelector('.a-price .a-offscreen');
        if (priceOffscreen) {
            const text = this.convertNumerals(priceOffscreen.textContent).replace(/[^\d.]/g, '');
            const val = parseFloat(text);
            return isNaN(val) ? 0 : val;
        }
        return 0;
    }

    extractProductRating(card) {
        const ratingEl = card.querySelector('[aria-label*="out of 5"], [aria-label*="من 5"], .a-icon-star-small .a-icon-alt, [aria-label*="نجوم"]');
        if (ratingEl) {
            const text = this.convertNumerals(ratingEl.getAttribute('aria-label') || ratingEl.textContent);
            const match = text.match(/([\d.]+)/);
            if (match) {
                const val = parseFloat(match[1]);
                return isNaN(val) ? 0 : val;
            }
        }
        return 0;
    }

    extractProductReviewCount(card) {
        const ratingLinks = card.querySelectorAll('a[aria-label*="rating"], a[aria-label*="review"], a[aria-label*="تقييم"], a[aria-label*="مراجعة"]');
        for (const link of ratingLinks) {
            const ariaLabel = this.convertNumerals(link.getAttribute('aria-label') || '');
            const match = ariaLabel.match(/([\d,]+)\s*(?:rating|review|تقييم|مراجعة)/i);
            if (match) {
                const val = parseInt(match[1].replace(/,/g, ''));
                return isNaN(val) ? 0 : val;
            }
        }
        const reviewLink = card.querySelector('[href*="#customerReviews"]');
        if (reviewLink) {
            const ariaLabel = this.convertNumerals(reviewLink.getAttribute('aria-label') || '');
            const ariaMatch = ariaLabel.match(/([\d,]+)\s*(?:rating|review|تقييم|مراجعة)/i);
            if (ariaMatch) {
                const val = parseInt(ariaMatch[1].replace(/,/g, ''));
                return isNaN(val) ? 0 : val;
            }
            const text = this.convertNumerals(reviewLink.textContent).replace(/[^\d]/g, '');
            if (text) {
                const val = parseInt(text);
                return isNaN(val) ? 0 : val;
            }
        }
        const reviewsBlock = card.querySelector('[data-cy="reviews-block"], .a-row.a-size-small');
        if (reviewsBlock) {
            const spans = reviewsBlock.querySelectorAll('span');
            for (const span of spans) {
                const text = this.convertNumerals(span.textContent.trim());
                const match = text.match(/^\(?(\d+)\)?$/);
                if (match) {
                    const val = parseInt(match[1]);
                    return isNaN(val) ? 0 : val;
                }
            }
        }
        const ratingContainer = card.querySelector('[aria-label*="star"], [aria-label*="نجوم"]');
        if (ratingContainer) {
            const parent = ratingContainer.closest('.a-row, .a-section');
            if (parent) {
                const links = parent.querySelectorAll('a, span');
                for (const link of links) {
                    const text = this.convertNumerals(link.textContent.trim());
                    const match = text.match(/^\(?(\d[\d,]*)\)?$/);
                    if (match) {
                        const val = parseInt(match[1].replace(/,/g, ''));
                        return isNaN(val) ? 0 : val;
                    }
                }
            }
        }
        return 0;
    }

    extractProductBrand(card) {
        const checkBrandValid = (text) => {
            if (!text) return false;
            const textLower = text.toLowerCase().trim();
            const skipBrands = new Set(['list:', 'was:', 'list', 'was', 'save', 'saving', 'off', 'egp', 'usd', 'price', 'item', 'details']);
            if (skipBrands.has(textLower) || 
                textLower.includes('stock') || 
                textLower.includes('order') || 
                textLower.includes('delivery') || 
                textLower.includes('shipping') || 
                textLower.includes('arrives') ||
                textLower.includes('متبق') ||
                textLower.includes('شحن') ||
                textLower.includes('باق') ||
                textLower.includes('save by') ||
                textLower.includes('buying') ||
                textLower.length <= 1) {
                return false;
            }
            return true;
        };

        const byBrandElements = card.querySelectorAll('.a-size-base.s-underline-text, .a-link-normal.s-no-outline');
        for (const el of byBrandElements) {
            const text = el.textContent?.trim();
            if (text && !text.includes('sponsored') && text.length < 50 && checkBrandValid(text)) {
                const parent = el.parentElement;
                if (parent && parent.textContent?.toLowerCase().includes('by ')) {
                    return text;
                }
            }
        }
        const brandLink = card.querySelector('a[href*="brand="]');
        if (brandLink) {
            const text = brandLink.textContent?.trim();
            if (text && text.length < 50 && checkBrandValid(text)) {
                return text;
            }
        }
        const storeLink = card.querySelector('a[href*="/stores/"]');
        if (storeLink) {
            const text = storeLink.textContent?.trim();
            if (text) {
                const cleanText = text.replace(/^Visit (the )?/i, '').replace(/ Store$/i, '').trim();
                if (checkBrandValid(cleanText)) {
                    return cleanText;
                }
            }
        }
        const brandRow = card.querySelector('.a-row.a-size-base .a-size-base:not(.a-price)');
        if (brandRow) {
            const text = brandRow.textContent?.trim();
            if (text && text.length < 40 && !text.match(/^\d|EGP|USD|\$/) && checkBrandValid(text)) {
                return text;
            }
        }
        return null;
    }

    isProductSponsored(card) {
        const sponsoredEl = card.querySelector('.s-label-popover-default, [data-component-type="sp-sponsored-result"]');
        const text = card.textContent.toLowerCase();
        return sponsoredEl !== null || text.includes('sponsored') || text.includes('إعلان');
    }

    /**
     * Calculate relevance score (0-100) to seed keyword
     */
    calculateRelevance(keyword, seed) {
        if (keyword === seed) return 100;

        const kwWords = new Set(keyword.toLowerCase().split(' '));
        const seedWords = seed.toLowerCase().split(' ');

        // Count matching words
        let matches = 0;
        seedWords.forEach(sw => {
            if (kwWords.has(sw)) matches++;
        });

        // Calculate base relevance from word overlap
        const baseRelevance = (matches / seedWords.length) * 100;

        // Boost if keyword starts with seed
        const startsWithBoost = keyword.startsWith(seed) ? 20 : 0;

        // Penalty for much longer keywords
        const lengthPenalty = Math.max(0, (kwWords.size - seedWords.length - 3) * 5);

        return Math.max(0, Math.min(100, Math.round(baseRelevance + startsWithBoost - lengthPenalty)));
    }

    /**
     * Save results to backend
     */
    async saveToBackend(analysisData, name = null) {
        try {
            const payload = {
                marketplace: this.marketplace,
                seed_keyword: this.seedKeyword,
                name: name || `Magnet: ${this.seedKeyword}`,
                duration_seconds: analysisData.duration_seconds,
                keywords: analysisData.keywords.map(kw => ({
                    keyword: kw.keyword,
                    search_volume: kw.search_volume || 0,
                    magnet_iq_score: kw.magnet_iq_score || 0,
                    competing_products: kw.competing_products || 0,
                    title_density: kw.title_density || 0,
                    word_count: kw.word_count || 1,
                    cpr_8day: kw.cpr_8day || 0,
                    cpr_total: kw.cpr_total || 0,
                    keyword_sales: kw.keyword_sales || 0,
                    avg_price: kw.avg_price || 0,
                    avg_reviews: kw.avg_reviews || 0,
                    sponsored_count: kw.sponsored_count || 0,
                    match_type: kw.match_type || 'autocomplete',
                    relevance_score: kw.relevance_score || 0
                }))
            };

            // Use the same API client pattern as Cerebro
            const response = await fetch('http://127.0.0.1:8000/api/magnet/analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                throw new Error(`Backend error: ${response.status}`);
            }

            return await response.json();

        } catch (error) {
            console.error('[Magnet] Save error:', error);
            throw error;
        }
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
        const mkp = this.marketplaces[this.marketplace] || this.marketplaces['amazon.eg'];
        const currency = mkp.currency;

        const headers = [
            'Keyword',
            'Search Volume',
            'Difficulty',
            'Title Density',
            'Competing Products',
            'Word Count',
            'CPR 8-Day',
            'CPR Total',
            'Keyword Sales',
            `Avg Price (${currency})`,
            'Avg Reviews',
            'Sponsored Count',
            'Match Type',
            'Relevance Score'
        ];

        const rows = keywords.map(kw => [
            kw.keyword,
            kw.search_volume,
            kw.magnet_iq_score, // Stored difficulty value
            kw.title_density,
            kw.competing_products,
            kw.word_count,
            kw.cpr_8day,
            kw.cpr_total,
            kw.keyword_sales,
            kw.avg_price,
            kw.avg_reviews,
            kw.sponsored_count,
            kw.match_type,
            kw.relevance_score
        ]);

        const csv = [headers, ...rows]
            .map(row => row.map(cell => `"${cell}"`).join(','))
            .join('\n');

        return csv;
    }

    /**
     * Apply filters to results
     */
    filterResults(keywords, filters) {
        return keywords.filter(kw => {
            if (filters.volume_min && kw.search_volume < filters.volume_min) return false;
            if (filters.volume_max && kw.search_volume > filters.volume_max) return false;
            if (filters.iq_min && kw.magnet_iq_score < filters.iq_min) return false;
            if (filters.iq_max && kw.magnet_iq_score > filters.iq_max) return false;
            if (filters.words_min && kw.word_count < filters.words_min) return false;
            if (filters.words_max && kw.word_count > filters.words_max) return false;
            if (filters.title_density_max && kw.title_density > filters.title_density_max) return false;
            if (filters.competing_max && kw.competing_products > filters.competing_max) return false;
            if (filters.match_type && filters.match_type !== 'all' && kw.match_type !== filters.match_type) return false;

            if (filters.include_phrase) {
                const phrases = filters.include_phrase.split(',').map(p => p.trim().toLowerCase());
                if (!phrases.some(p => kw.keyword.includes(p))) return false;
            }

            if (filters.exclude_phrase) {
                const phrases = filters.exclude_phrase.split(',').map(p => p.trim().toLowerCase());
                if (phrases.some(p => kw.keyword.includes(p))) return false;
            }

            return true;
        });
    }

    /**
     * Quick filter presets
     */
    applyQuickFilter(keywords, filterName) {
        const presets = {
            'high_volume': { volume_min: 1000 },
            'opportunity': { iq_min: 3, title_density_max: 5 },
            'low_competition': { competing_max: 10000, volume_min: 500 },
            'long_tail': { words_min: 4, volume_min: 100 }
        };

        const preset = presets[filterName];
        if (!preset) return keywords;

        return this.filterResults(keywords, preset);
    }

    /**
     * Fetch configurable settings from backend
     * Settings include: attribute_product_count, use_google_suggestions, etc.
     */
    async fetchBackendSettings() {
        try {
            // Try to get the API base URL from the extension's config
            const apiBase = window.API_CONFIG?.baseUrl || 'http://127.0.0.1:8000';

            console.log('[Magnet] Fetching backend settings from:', `${apiBase}/api/magnet/settings`);

            const response = await fetch(`${apiBase}/api/magnet/settings`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                console.warn('[Magnet] Backend settings request failed:', response.status);
                return null;
            }

            const data = await response.json();
            console.log('[Magnet DEBUG] Raw backend response:', data);
            console.log('[Magnet DEBUG] attribute_variation_scope from backend:', data.settings?.attribute_variation_scope);
            return data.success ? data.settings : null;
        } catch (e) {
            console.warn('[Magnet] Could not fetch backend settings:', e.message);
            return null;
        }
    }
}

// Make available globally
if (typeof window !== 'undefined') {
    window.MagnetAnalyzer = MagnetAnalyzer;
}
