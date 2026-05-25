// Amazon Keyword Suggestions API Client
// Direct calls to Amazon's autocomplete - no CORS issues for extensions

class KeywordSuggestions {
    constructor(marketplace = 'amazon.com') {
        this.marketplace = marketplace;
        this.tld = this.getTLD(marketplace);
        this.mid = this.getMarketplaceId(marketplace);
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
    }

    getTLD(marketplace) {
        const tldMap = {
            'amazon.com': 'com',
            'amazon.eg': 'eg',
            'amazon.co.uk': 'co.uk',
            'amazon.de': 'de',
            'amazon.fr': 'fr',
            'amazon.es': 'es',
            'amazon.it': 'it',
            'amazon.ca': 'ca',
            'amazon.com.au': 'com.au',
            'amazon.in': 'in',
            'amazon.ae': 'ae',
            'amazon.sa': 'sa'
        };
        return tldMap[marketplace] || 'com';
    }

    getMarketplaceId(marketplace) {
        const midMap = {
            'amazon.com': 'ATVPDKIKX0DER',
            'amazon.eg': 'ARBP9OOSHTCHU',
            'amazon.co.uk': 'A1F83G8C2ARO7P',
            'amazon.de': 'A1PA6795UKMFR9',
            'amazon.fr': 'A13V1IB3VIYZZH',
            'amazon.es': 'A1RKKUPIHCS9HS',
            'amazon.it': 'APJ6JRA9NG5V4',
            'amazon.ca': 'A2EUQ1WTGCTBG2',
            'amazon.com.au': 'A39IBJ37TRP1C6',
            'amazon.in': 'A21TJRUUN4KGV',
            'amazon.ae': 'A2VIGQ35RCS4UG',
            'amazon.sa': 'A17E79C6D8DWNP'
        };
        return midMap[marketplace] || 'ATVPDKIKX0DER';
    }

    /**
     * Get keyword suggestions from Amazon autocomplete
     * @param {string} prefix - Search term
     * @returns {Promise<Array>} - Array of keyword suggestions
     */
    async getSuggestions(prefix) {
        if (!prefix || prefix.length < 2) {
            return [];
        }

        // Check cache first
        const cacheKey = `${this.marketplace}:${prefix.toLowerCase()}`;
        const cached = this.cache.get(cacheKey);
        if (cached && Date.now() - cached.timestamp < this.cacheTimeout) {
            return cached.suggestions;
        }

        try {
            const sessionId = await this.getSessionId();

            const params = new URLSearchParams({
                prefix: prefix,
                alias: 'aps',
                limit: '11',
                'suggestion-type': 'KEYWORD',
                'page-type': 'Search',
                'site-variant': 'desktop',
                'version': '3',
                'session-id': sessionId,
                'mid': this.mid
            });

            const url = `https://www.amazon.${this.tld}/suggestions?${params}`;

            const response = await fetch(url, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                console.error('Amazon suggestions API error:', response.status);
                return [];
            }

            const data = await response.json();

            const suggestions = (data.suggestions || [])
                .filter(s => s.type === 'KEYWORD')
                .map(s => ({
                    keyword: s.value,
                    source: 'amazon_autocomplete',
                    marketplace: this.marketplace
                }));

            // Cache the results
            this.cache.set(cacheKey, {
                suggestions,
                timestamp: Date.now()
            });

            return suggestions;
        } catch (error) {
            console.error('Keyword suggestion error:', error);
            return [];
        }
    }

    /**
     * Get expanded keyword suggestions (seed + variations)
     * @param {string} seed - Base keyword
     * @returns {Promise<Array>} - Expanded keyword list
     */
    async getExpandedSuggestions(seed) {
        const results = new Set();

        // Get base suggestions
        const baseSuggestions = await this.getSuggestions(seed);
        baseSuggestions.forEach(s => results.add(s.keyword));

        // Get alphabet expansion (seed + a, seed + b, etc.)
        const alphabet = 'abcdefghijklmnopqrstuvwxyz';
        const expansionPromises = [];

        for (const letter of alphabet) {
            expansionPromises.push(
                this.getSuggestions(`${seed} ${letter}`)
            );
        }

        // Execute expansions with rate limiting
        const batchSize = 5;
        for (let i = 0; i < expansionPromises.length; i += batchSize) {
            const batch = expansionPromises.slice(i, i + batchSize);
            const batchResults = await Promise.all(batch);
            batchResults.flat().forEach(s => results.add(s.keyword));

            // Small delay between batches
            await this.delay(200);
        }

        return Array.from(results).map(keyword => ({
            keyword,
            source: 'amazon_autocomplete',
            marketplace: this.marketplace
        }));
    }

    /**
     * Extract keywords from product title
     * @param {string} title - Product title
     * @returns {Array<string>} - Extracted keyword phrases
     */
    extractKeywordsFromTitle(title) {
        if (!title) return [];

        // Clean the title
        let cleaned = title
            .toLowerCase()
            .replace(/[^\w\s\-]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();

        // Remove common stop words and brands at the end
        const stopWords = ['for', 'with', 'and', 'the', 'by', 'in', 'of', 'to', 'a', 'an'];
        const words = cleaned.split(' ').filter(w => !stopWords.includes(w) && w.length > 2);

        // Generate 2-word and 3-word combinations
        const keywords = new Set();

        // Single significant words
        words.slice(0, 5).forEach(w => {
            if (w.length > 3) keywords.add(w);
        });

        // 2-word combinations
        for (let i = 0; i < words.length - 1; i++) {
            keywords.add(`${words[i]} ${words[i + 1]}`);
        }

        // 3-word combinations
        for (let i = 0; i < words.length - 2; i++) {
            keywords.add(`${words[i]} ${words[i + 1]} ${words[i + 2]}`);
        }

        return Array.from(keywords).slice(0, 20); // Max 20 keywords
    }

    async getSessionId() {
        try {
            // Try to get session-id from Amazon cookies
            if (typeof chrome !== 'undefined' && chrome.cookies) {
                const cookie = await chrome.cookies.get({
                    url: `https://www.amazon.${this.tld}`,
                    name: 'session-id'
                });
                if (cookie?.value) {
                    return cookie.value;
                }
            }
        } catch (error) {
            console.log('Could not get session cookie, generating fallback');
        }

        // Fallback: generate session-like ID
        return this.generateSessionId();
    }

    generateSessionId() {
        return `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    clearCache() {
        this.cache.clear();
    }
}

// Make available globally
if (typeof window !== 'undefined') {
    window.KeywordSuggestions = KeywordSuggestions;
}
