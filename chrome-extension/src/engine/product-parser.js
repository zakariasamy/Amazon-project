// Product Page Parser
// Extracts product data, related products carousel, and generates keyword candidates

class ProductParser {
    constructor(doc = null) {
        // Ensure we have a valid document with querySelector
        if (doc && typeof doc.querySelector === 'function') {
            this.doc = doc;
        } else {
            // Fallback to global document
            this.doc = document;
        }
    }

    /**
     * Extract basic product info from product page
     */
    extractProductInfo() {
        const asin = this.extractAsin();
        const title = this.doc.querySelector('#productTitle')?.textContent?.trim() || '';
        const category = this.extractCategory();
        const brand = this.extractBrand();

        return {
            asin,
            title,
            category,
            brand
        };
    }

    extractAsin() {
        // Try multiple sources for ASIN
        const asinInput = this.doc.querySelector('input[name="ASIN"], input[name="asin"]');
        if (asinInput) return asinInput.value;

        // Try URL
        const urlMatch = window.location.pathname.match(/\/dp\/([A-Z0-9]{10})/i);
        if (urlMatch) return urlMatch[1];

        // Try data attribute
        const asinEl = this.doc.querySelector('[data-asin]');
        if (asinEl) return asinEl.getAttribute('data-asin');

        return null;
    }

    extractCategory() {
        // Try breadcrumb
        const breadcrumbs = this.doc.querySelectorAll('#wayfinding-breadcrumbs_feature_div li a, .a-breadcrumb li a');
        if (breadcrumbs.length > 0) {
            // Return the last category (most specific)
            return breadcrumbs[breadcrumbs.length - 1]?.textContent?.trim() || null;
        }
        return null;
    }

    extractBrand() {
        const byline = this.doc.querySelector('#bylineInfo');
        if (byline) {
            const text = byline.textContent.trim();
            const visitMatch = text.match(/Visit the (.+?) Store/i);
            if (visitMatch) return visitMatch[1].trim();
            if (text.includes('Brand:')) return text.replace('Brand:', '').trim();
            return text.replace(/^by\s+/i, '').trim();
        }
        return null;
    }

    /**
     * Extract products from "Customers who viewed this item also viewed" carousel
     * Returns array of { asin, title } objects
     */
    extractCarouselProducts() {
        const products = [];
        const seen = new Set();

        // Find all carousel items
        const carouselItems = this.doc.querySelectorAll('.a-carousel-card, [data-csa-c-item-type="asin"]');

        for (const item of carouselItems) {
            // Get ASIN
            const asinSpan = item.querySelector('[data-csa-c-item-id]');
            let asin = asinSpan?.getAttribute('data-csa-c-item-id')?.replace('amzn1.asin.', '');

            // Fallback: try data-asin
            if (!asin) {
                asin = item.getAttribute('data-asin') || item.querySelector('[data-asin]')?.getAttribute('data-asin');
            }

            if (!asin || asin.length < 10) continue;

            // Get title from truncated div
            const titleDiv = item.querySelector(
                '.p13n-sc-truncate-desktop-type2, ' +
                '.p13n-sc-line-clamp-4, ' +
                '.p13n-sc-line-clamp-5, ' +
                '.p13n-sc-line-clamp-6, ' +
                '[class*="p13n-sc-css-line-clamp"]'
            );
            const title = titleDiv?.textContent?.trim() || '';

            if (title && !seen.has(asin)) {
                seen.add(asin);
                products.push({ asin, title });
            }
        }

        console.log(`Extracted ${products.length} products from carousel`);
        return products;
    }

    /**
     * Alternative: Extract ASIN list from carousel data-options JSON
     * This gets all ASINs even if not rendered yet
     */
    extractCarouselAsinList() {
        const carousel = this.doc.querySelector('[data-a-carousel-options]');
        if (!carousel) return [];

        try {
            const optionsStr = carousel.getAttribute('data-a-carousel-options');
            const options = JSON.parse(optionsStr);

            if (options.ajax && options.ajax.id_list) {
                return options.ajax.id_list.map(item => {
                    try {
                        const parsed = JSON.parse(item);
                        return parsed.id;
                    } catch {
                        return null;
                    }
                }).filter(Boolean);
            }
        } catch (e) {
            console.warn('Failed to parse carousel options:', e);
        }
        return [];
    }

    /**
     * Stop words to filter out - these don't belong in search keywords
     */
    getStopWords() {
        return new Set([
            'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
            'from', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had',
            'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must',
            'this', 'that', 'these', 'those', 'it', 'its', 'you', 'your', 'we', 'our', 'they', 'their',
            // Size/quantity words - kept only the most generic ones
            'new', 'best', 'top', 'most', 'more', 'less', 'free',
            // Feature words that are likely NOT search terms
            'tare', 'function', 'feature', 'features', 'included', 'includes', 'include',
            'button', 'buttons',
            'battery', 'batteries', 'powered', 'power', 'charging',
            'brand', 'brands', 'original', 'official',
            'year', 'warranty', 'guarantee', 'return', 'shipping', 'delivery'
        ]);
    }

    /**
     * Get core product words (nouns that define what the product IS)
     */
    getCoreProductWords() {
        return new Set([
            // Common product type words
            'scale', 'scales', 'balance', 'weighing',
            'kitchen', 'food', 'cooking', 'baking',
            'digital', 'electronic', 'electric',
            'weight', 'gram', 'ounce', 'pound',
            'body', 'bathroom', 'health', 'fitness',
            'jewelry', 'pocket', 'mini', 'postal', 'shipping',
            'coffee', 'espresso', 'timer',
            'luggage', 'travel', 'hanging',
            'industrial', 'commercial', 'lab', 'laboratory'
        ]);
    }

    /**
     * Clean and normalize a product title for keyword extraction
     */
    cleanTitle(title) {
        let cleaned = title
            .toLowerCase()
            .replace(/[^\w\s]/g, ' ')  // Remove all special chars including -
            .replace(/\b\d+\s*(kg|g|lb|oz|ml|l|cm|mm|m|inch|in|pcs|pc|pack)\b/gi, '') // Remove measurements
            .replace(/\b(black|white|red|blue|green|yellow|pink|gray|grey|silver|gold|orange|purple|brown)\b/gi, '') // Remove colors
            .replace(/\s+/g, ' ')
            .trim();

        return cleaned;
    }

    /**
     * Filter words to keep only meaningful ones for search keywords
     */
    filterWords(words) {
        const stopWords = this.getStopWords();
        return words.filter(w => w.length > 2 && !stopWords.has(w));
    }

    /**
     * Get weak words (adjectives/modifiers) that shouldn't stand alone as a keyword phrase
     * They must be combined with other words (nouns) to form a valid keyword
     */
    getWeakWords() {
        return new Set([
            'high', 'low', 'ultra', 'super', 'extra', 'max', 'mini', 'large', 'small',
            'accuracy', 'accurate', 'precision', 'precise', 'sensitive', 'sensitivity',
            'quality', 'premium', 'professional', 'pro', 'advanced', 'smart', 'intelligent',
            'automatic', 'auto', 'manual', 'adjustable', 'multi', 'multifunction',
            'durable', 'sturdy', 'strong', 'lightweight', 'portable', 'compact', 'slim',
            'easy', 'simple', 'convenient', 'practical', 'useful', 'versatile',
            'modern', 'stylish', 'elegant', 'classic', 'design', 'style',
            'digital', 'electronic', 'electric', 'heavy', 'duty', 'industrial', 'commercial'
        ]);
    }

    /**
     * Generate smart n-grams (2-5 word phrases) that are likely search terms
     * IMPORTANT: Always respects original word order from title
     */
    generateNgrams(cleanedTitle) {
        const allWords = cleanedTitle.split(' ');
        const stopWords = this.getStopWords();
        const weakWords = this.getWeakWords();
        const ngrams = new Set(); // Use Set to auto-deduplicate

        // First, extract meaningful words while preserving their ORIGINAL positions
        const meaningfulWordPositions = [];
        for (let i = 0; i < allWords.length; i++) {
            if (allWords[i].length > 2 && !stopWords.has(allWords[i])) {
                meaningfulWordPositions.push({ word: allWords[i], index: i });
            }
        }

        const isValidPhrase = (phraseWords) => {
            // A phrase is valid if it contains at least one word that is NOT weak
            // i.e., at least one noun/core word
            return phraseWords.some(w => !weakWords.has(w));
        };

        // Generate 5-grams (Longest specific phrases FIRST)
        for (let i = 0; i < meaningfulWordPositions.length - 4; i++) {
            const words = meaningfulWordPositions.slice(i, i + 5).map(p => p.word);
            const first = meaningfulWordPositions[i];
            const fifth = meaningfulWordPositions[i + 4];
            if (fifth.index - first.index <= 8) {
                if (isValidPhrase(words)) {
                    ngrams.add(words.join(' '));
                }
            }
        }

        // Generate 4-grams
        for (let i = 0; i < meaningfulWordPositions.length - 3; i++) {
            const words = meaningfulWordPositions.slice(i, i + 4).map(p => p.word);
            const first = meaningfulWordPositions[i];
            const fourth = meaningfulWordPositions[i + 3];
            if (fourth.index - first.index <= 6) {
                if (isValidPhrase(words)) {
                    ngrams.add(words.join(' '));
                }
            }
        }

        // Generate 3-grams
        for (let i = 0; i < meaningfulWordPositions.length - 2; i++) {
            const words = meaningfulWordPositions.slice(i, i + 3).map(p => p.word);
            const first = meaningfulWordPositions[i];
            const third = meaningfulWordPositions[i + 2];
            if (third.index - first.index <= 4) {
                if (isValidPhrase(words)) {
                    ngrams.add(words.join(' '));
                }
            }
        }

        // Generate 2-grams (Most generic LAST)
        for (let i = 0; i < meaningfulWordPositions.length - 1; i++) {
            const curr = meaningfulWordPositions[i];
            const next = meaningfulWordPositions[i + 1];
            if (next.index - curr.index <= 2) {
                if (isValidPhrase([curr.word, next.word])) {
                    ngrams.add(curr.word + ' ' + next.word);
                }
            }
        }

        return Array.from(ngrams);
    }

    /**
     * Extract keyword candidates from carousel products
     * Uses frequency analysis across all titles
     */
    extractKeywordCandidates(carouselProducts) {
        const phraseCount = new Map();
        const phraseBrands = new Map(); // Track which brands use each phrase

        for (const product of carouselProducts) {
            const cleaned = this.cleanTitle(product.title);
            const ngrams = this.generateNgrams(cleaned);

            for (const phrase of ngrams) {
                phraseCount.set(phrase, (phraseCount.get(phrase) || 0) + 1);

                // Track brand diversity (simple: use first word of title as proxy)
                const brand = cleaned.split(' ')[0] || 'unknown';
                if (!phraseBrands.has(phrase)) {
                    phraseBrands.set(phrase, new Set());
                }
                phraseBrands.get(phrase).add(brand);
            }
        }

        // Filter: keep phrases that appear in multiple products from different "brands"
        const candidates = [];
        for (const [phrase, count] of phraseCount.entries()) {
            const brandCount = phraseBrands.get(phrase)?.size || 0;

            // Keep if appears 2+ times OR from 2+ different sources
            if (count >= 2 || brandCount >= 2) {
                candidates.push({
                    phrase,
                    frequency: count,
                    brandDiversity: brandCount,
                    score: count + brandCount // Simple scoring
                });
            }
        }

        // Sort by score (highest first)
        candidates.sort((a, b) => b.score - a.score);

        console.log(`Generated ${candidates.length} keyword candidates from ${carouselProducts.length} carousel products`);
        return candidates.map(c => c.phrase);
    }

    /**
     * Apply category intent filtering
     * Removes keywords that don't match the product's category context
     */
    filterByIntent(keywords, category, excludeTerms = []) {
        // Default exclude terms for common noise
        const defaultExclude = ['timer', 'clock', 'bluetooth', 'wifi', 'app'];
        const allExclude = [...defaultExclude, ...excludeTerms.map(t => t.toLowerCase())];

        return keywords.filter(kw => {
            const lower = kw.toLowerCase();
            return !allExclude.some(term => lower.includes(term));
        });
    }

    /**
     * Generate n-grams from a raw title, splitting by delimiters first
     * This prevents generating keywords that cross sentence boundaries (e.g. "scale - white")
     */
    generateNgramsFromTitle(rawTitle) {
        if (!rawTitle) return [];

        // Split by common delimiters: - (hyphen), | , . ( ) [ ] / AND unicode dashes (\u2010-\u2015)
        const segments = rawTitle.split(/[-\u2010-\u2015|,|/|(|)|[|\]|\.]+/);
        const allNgrams = [];

        for (const segment of segments) {
            const cleaned = this.cleanTitle(segment);
            // Only process segments that have enough content
            if (cleaned && cleaned.length > 2) {
                const ngrams = this.generateNgrams(cleaned);
                allNgrams.push(...ngrams);
            }
        }

        return allNgrams;
    }

    /**
     * Extract keyword candidates from carousel products
     * Uses frequency analysis across all titles
     */
    extractCarouselKeywords(carouselProducts) {
        const phraseCount = new Map();
        const phraseBrands = new Map(); // Track which brands use each phrase

        for (const product of carouselProducts) {
            // Use segmented n-gram generation
            const ngrams = this.generateNgramsFromTitle(product.title);

            for (const phrase of ngrams) {
                phraseCount.set(phrase, (phraseCount.get(phrase) || 0) + 1);

                // Track brand diversity (simple: use first word of title as proxy)
                const cleaned = this.cleanTitle(product.title);
                const brand = cleaned.split(' ')[0] || 'unknown';
                if (!phraseBrands.has(phrase)) {
                    phraseBrands.set(phrase, new Set());
                }
                phraseBrands.get(phrase).add(brand);
            }
        }

        // Filter: keep phrases that appear in multiple products from different "brands"
        const candidates = [];
        for (const [phrase, count] of phraseCount.entries()) {
            const brandCount = phraseBrands.get(phrase)?.size || 0;

            // Keep if appears 2+ times OR from 2+ different sources
            if (count >= 2 || brandCount >= 2) {
                candidates.push({
                    phrase,
                    frequency: count,
                    brandDiversity: brandCount,
                    score: count + brandCount // Simple scoring
                });
            }
        }

        // Sort by score (highest first)
        candidates.sort((a, b) => b.score - a.score);

        console.log(`Generated ${candidates.length} keyword candidates from ${carouselProducts.length} carousel products`);
        return candidates.map(c => c.phrase);
    }

    /**
     * Main method: Get all keyword candidates for reverse ASIN
     * Prioritizes keywords from main product title, then adds carousel-based keywords
     */
    getKeywordCandidatesForReverseAsin(excludeTerms = []) {
        const productInfo = this.extractProductInfo();
        const carouselProducts = this.extractCarouselProducts();

        // PRIORITY 1: Keywords from the main product title
        // Use segmented generation to avoid cross-boundary phrases
        const mainTitleKeywords = this.generateNgramsFromTitle(productInfo.title);

        console.log('Main title keywords:', mainTitleKeywords);

        // PRIORITY 2: Keywords from carousel products (if available)
        let carouselKeywords = [];
        if (carouselProducts.length > 0) {
            // Add main product to carousel for frequency analysis
            carouselProducts.unshift({ asin: productInfo.asin, title: productInfo.title });
            carouselKeywords = this.extractCarouselKeywords(carouselProducts);
        }

        console.log('Carousel keywords:', carouselKeywords);

        // Combine and deduplicate, with main title keywords FIRST
        const allCandidates = [...new Set([...mainTitleKeywords, ...carouselKeywords])];

        // Apply intent filtering
        let filtered = this.filterByIntent(allCandidates, productInfo.category, excludeTerms);

        // ---------------------------------------------------------
        // DOMINANT WORD FILTERING (User Request: "Restrict Main Word")
        // ---------------------------------------------------------
        // 1. Calculate word frequency across Main Title + Carousel
        const wordCounts = {};
        const stopWords = this.getStopWords();
        const weakWords = this.getWeakWords();
        const blacklist = new Set(['feature', 'function', 'design', 'style', 'type', 'use', 'color', 'size']);

        const countWord = (text, weight) => {
            if (!text) return;
            text.toLowerCase().split(/[\s-]+/).forEach(w => {
                const clean = w.replace(/[^\w]/g, '');
                // Simple singularization (remove 's' at end if > 3 chars)
                const root = clean.length > 3 && clean.endsWith('s') ? clean.slice(0, -1) : clean;

                if (clean.length > 2 && !stopWords.has(clean) && !weakWords.has(clean) && !blacklist.has(clean)) {
                    wordCounts[root] = (wordCounts[root] || 0) + weight;
                }
            });
        };

        // Main Title (High Weight)
        countWord(productInfo.title, 10);

        // Carousel Titles (Normal Weight)
        carouselProducts.forEach(p => countWord(p.title, 1));

        // Find Top Dominant Word
        let topWord = '';
        let maxCount = 0;
        // Debug ranking
        const sortedWords = Object.entries(wordCounts).sort((a, b) => b[1] - a[1]);
        console.log('🔍 Word Frequency Analysis (Dominant Word Detection):', sortedWords.slice(0, 20));

        if (sortedWords.length > 0) {
            topWord = sortedWords[0][0]; // Most frequent root word
            maxCount = sortedWords[0][1];
        }

        // Apply Mandatory Filter if a clear winner exists
        if (topWord && maxCount > 5) { // Threshold to avoid filtering if data is scarce
            console.log(`Enforcing dominant word: "${topWord}"`);
            filtered = filtered.filter(kw => {
                const lower = kw.toLowerCase();
                // Check if keyword contains the topWord (or simple plural)
                return lower.includes(topWord) || lower.includes(topWord + 's');
            });
        }
        // ---------------------------------------------------------

        // Final quality check: remove single words and very short phrases
        filtered = filtered.filter(kw => {
            const words = kw.split(' ');
            return words.length >= 2 && kw.length >= 5;
        });

        console.log(`Final ${filtered.length} keyword candidates:`, filtered);

        return {
            productInfo,
            candidates: filtered, // Return ALL candidates
            dominantWord: topWord, // Expose dominant word for external filtering
            carouselProductCount: carouselProducts.length > 0 ? carouselProducts.length - 1 : 0,
            source: carouselProducts.length > 0 ? 'title_plus_carousel' : 'title_only'
        };
    }
}

// Make available globally
if (typeof window !== 'undefined') {
    window.ProductParser = ProductParser;
}
