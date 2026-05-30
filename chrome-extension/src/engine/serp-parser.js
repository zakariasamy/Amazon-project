// SERP Parser - Extracts product data from Amazon search result pages
class SerpParser {
    constructor(root = document) {
        this.root = root;
        this.marketplace = window.location.hostname.includes('.eg') ? 'amazon.eg' : 'amazon.com';
    }

    /**
     * Check if current page is a search results page
     */
    static isSearchPage() {
        const url = window.location.href;
        return url.includes('/s?') || url.includes('/s/?');
    }

    /**
     * Extract search keyword from URL
     */
    extractKeyword() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('k') || urlParams.get('field-keywords') || '';
    }

    /**
     * Extract all products from search results page
     */
    extractProducts() {
        const productsMap = new Map(); // Use Map to deduplicate by ASIN

        // Only select products from the MAIN search results container
        // This excludes "Related to items you've viewed", "Customers who viewed...", etc.
        const mainResultsContainer = this.root.querySelector('.s-main-slot.s-result-list.s-search-results');
        if (!mainResultsContainer) {
            console.warn('SERP Parser: Main results container not found');
            return [];
        }

        // Get only direct child products or products within the main container
        const productCards = mainResultsContainer.querySelectorAll('[data-component-type="s-search-result"]');

        let position = 0;

        productCards.forEach((card) => {
            const asin = card.getAttribute('data-asin');
            if (!asin || asin.length !== 10) return; // Skip non-product elements

            const title = this.extractTitle(card);
            const price = this.extractPrice(card);
            const rating = this.extractRating(card);
            const reviews = this.extractReviewCount(card);
            const is_sponsored = this.isSponsored(card);
            const monthly_sales = this.extractMonthlySales(card);
            const image = this.extractImage(card);
            const brand = this.extractBrand(card);

            // Check if we already have this ASIN
            if (productsMap.has(asin)) {
                // Merge data - keep the more complete version
                const existing = productsMap.get(asin);

                // Update with new data if it's better (non-empty title, higher price, sales data, etc.)
                if (!existing.title && title) existing.title = title;
                if (!existing.price && price) existing.price = price;
                if (!existing.rating && rating) existing.rating = rating;
                if (!existing.reviews && reviews) existing.reviews = reviews;
                if (!existing.monthly_sales && monthly_sales) existing.monthly_sales = monthly_sales;
                if (!existing.image && image) existing.image = image;
                if (!existing.brand && brand) existing.brand = brand;
                if (!existing.seller_count) {
                    const seller_count = this.extractSellerCount(card);
                    if (seller_count) existing.seller_count = seller_count;
                }
                // Don't override is_sponsored if already true
                if (is_sponsored) existing.is_sponsored = true;
            } else {
                // Skip products without price (unavailable/out of stock)
                if (!price || price <= 0) {
                    console.log(`SERP Parser: Skipping unavailable product ${asin} (no price)`);
                    return;
                }

                const seller_count = this.extractSellerCount(card);

                position++;
                productsMap.set(asin, {
                    asin,
                    position,
                    title,
                    brand,
                    price,
                    rating,
                    reviews,
                    bsr: null, // BSR not available on search page - will be enriched
                    is_sponsored,
                    monthly_sales,
                    category: null, // Not available on search page
                    seller_count, // Will be enriched from product page
                    image
                });
            }
        });

        const products = Array.from(productsMap.values());
        console.log(`SERP Parser: Found ${products.length} unique products (from ${productCards.length} elements)`);
        return products;
    }

    convertNumerals(text) {
        if (!text) return '';
        const arabicDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        let clean = text.toString();
        // Replace Arabic decimal and thousand separators
        clean = clean.replace(/٫/g, '.').replace(/٬/g, '');
        // Replace Eastern Arabic numerals
        clean = clean.replace(/[٠-٩]/g, d => arabicDigits['٠١٢٣٤٥٦٧٨٩'.indexOf(d)]);
        return clean;
    }

    extractTitle(card) {
        // First try to get title from h2 aria-label (most reliable, includes full title)
        const h2Element = card.querySelector('h2');
        if (h2Element) {
            // Check aria-label first - it has the clean, full title
            const ariaLabel = h2Element.getAttribute('aria-label');
            if (ariaLabel) {
                // Remove "Sponsored Ad – " prefix if present
                return ariaLabel.replace(/^Sponsored Ad\s*[–-]\s*/i, '').trim();
            }

            // Fallback: get text from the span inside h2 (skip any parent sponsored labels)
            const titleSpan = h2Element.querySelector('span');
            if (titleSpan) {
                let title = titleSpan.textContent?.trim() || '';
                // Remove any "Sponsored" prefix
                title = title.replace(/^Sponsored\s*/gi, '').trim();
                if (title && title !== 'Sponsored') {
                    return title;
                }
            }
        }

        // Final fallback: try other selectors
        const titleEl = card.querySelector('.s-title-instructions-style span, [data-cy="title-recipe"] span');
        let title = titleEl?.textContent?.trim() || '';
        title = title.replace(/^Sponsored\s*/gi, '').trim();

        return title;
    }

    extractPrice(card) {
        const priceWhole = card.querySelector('.a-price .a-price-whole');
        const priceFraction = card.querySelector('.a-price .a-price-fraction');
        if (priceWhole) {
            const whole = this.convertNumerals(priceWhole.textContent).replace(/[^\d]/g, '');
            const fraction = this.convertNumerals(priceFraction?.textContent || '').replace(/[^\d]/g, '') || '00';
            const val = parseFloat(`${whole}.${fraction}`);
            return isNaN(val) ? 0 : val;
        }

        // Fallback
        const priceOffscreen = card.querySelector('.a-price .a-offscreen');
        if (priceOffscreen) {
            const text = this.convertNumerals(priceOffscreen.textContent).replace(/[^\d.]/g, '');
            const val = parseFloat(text);
            return isNaN(val) ? 0 : val;
        }

        return 0;
    }

    extractRating(card) {
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

    extractReviewCount(card) {
        // Method 1: Look for aria-label with "ratings" or "reviews"
        const ratingLinks = card.querySelectorAll('a[aria-label*="rating"], a[aria-label*="review"], a[aria-label*="تقييم"], a[aria-label*="مراجعة"]');
        for (const link of ratingLinks) {
            const ariaLabel = this.convertNumerals(link.getAttribute('aria-label') || '');
            const match = ariaLabel.match(/([\d,]+)\s*(?:rating|review|تقييم|مراجعة)/i);
            if (match) {
                const val = parseInt(match[1].replace(/,/g, ''));
                return isNaN(val) ? 0 : val;
            }
        }

        // Method 2: Look for review count link with #customerReviews
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

        // Method 3: Look for span with parentheses like "(5)"
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

        // Method 4: Fallback - look near star rating
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

    /**
     * Extract brand name from product card
     */
    extractBrand(card) {
        // Method 1: Look for "by Brand" text pattern
        const byBrandElements = card.querySelectorAll('.a-size-base.s-underline-text, .a-link-normal.s-no-outline');
        for (const el of byBrandElements) {
            const text = el.textContent?.trim();
            if (text && !text.includes('sponsored') && text.length < 50) {
                // Check if it's after "by" in parent
                const parent = el.parentElement;
                if (parent && parent.textContent?.toLowerCase().includes('by ')) {
                    return text;
                }
            }
        }

        // Method 2: Look for brand-specific link with "brand=" in href
        const brandLink = card.querySelector('a[href*="brand="]');
        if (brandLink) {
            const text = brandLink.textContent?.trim();
            if (text && text.length < 50) {
                return text;
            }
        }

        // Method 3: Look for "Visit the X Store" or brand row
        const storeLink = card.querySelector('a[href*="/stores/"]');
        if (storeLink) {
            const text = storeLink.textContent?.trim();
            if (text) {
                // Remove "Visit the " and " Store" parts
                return text.replace(/^Visit (the )?/i, '').replace(/ Store$/i, '').trim();
            }
        }

        // Method 4: Look for brand in a-row after title
        const brandRow = card.querySelector('.a-row.a-size-base .a-size-base:not(.a-price)');
        if (brandRow) {
            const text = brandRow.textContent?.trim();
            if (text && text.length < 40 && !text.match(/^\d|EGP|USD|\$/)) {
                return text;
            }
        }

        return null;
    }

    /**
     * Extract monthly sales from "X+ bought in past month" badge
     */
    extractMonthlySales(card) {
        // First try to find specific badge elements (more reliable)
        const badgeSelectors = [
            '.a-size-base.a-color-secondary:not(.a-text-strike)',  // Common badge class
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
                        console.log(`SERP: Found sales badge: "${text}" -> ${sales}`);
                        return sales;
                    }
                }
            }
        }

        // Fallback: Search card text more carefully (only specific patterns)
        // This is less reliable so we're more strict about what we match
        const spans = card.querySelectorAll('span.a-size-base');
        for (const span of spans) {
            const text = span.textContent?.trim() || '';
            // Only match if the ENTIRE span content is about buying
            if (text.match(/^\d+.*?(bought|purchased|تم شراء)/i) ||
                text.match(/^.*?(bought|purchased|تم شراء).*?\d+/i)) {
                const sales = this.parseSalesFromText(text);
                if (sales !== null) {
                    console.log(`SERP: Found sales text: "${text}" -> ${sales}`);
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
     * Extract seller count directly from search result card if present
     */
    extractSellerCount(card) {
        // Method 1: Look for links pointing to offer listing or olp
        const offerLinks = card.querySelectorAll('a[href*="offer-listing"], a[href*="olp"], a[href*="/gp/aod/"], a[data-action="s-show-all-offers-display"]');
        for (const link of offerLinks) {
            const text = link.textContent?.trim() || '';
            // Match pattern like "(X new offers)", "(X offers)", "(X new offer)"
            // Also handles Arabic e.g. "(X عروض جديدة)" or "(X جديد)"
            const match = text.match(/\(?(\d+)\s*(?:new\s+)?(?:offers?|sellers?|جديد|مستعمل|عروض|بائع)/i) || text.match(/\((\d+)\)/);
            if (match) {
                const count = parseInt(match[1], 10);
                if (count > 0) {
                    console.log(`SerpParser: Found ${count} sellers directly from card link text: "${text}"`);
                    return count;
                }
            }
        }

        // Method 2: Check card text for "More Buying Choices" or Arabic equivalent
        const cardText = card.textContent || '';
        const buyingChoicesPattern = /(?:More Buying Choices|خيارات شراء أخرى).*?\b(\d+)\s*(?:new\s+)?(?:offers?|sellers?|جديد|مستعمل|عروض|بائع)/i;
        const textMatch = cardText.match(buyingChoicesPattern);
        if (textMatch) {
            const count = parseInt(textMatch[1], 10);
            if (count > 0) {
                console.log(`SerpParser: Found ${count} sellers directly from card text match`);
                return count;
            }
        }

        // Default to null (will be enriched via BSR fetching, or default to 1 if skipped and remains null)
        return null;
    }

    isSponsored(card) {
        const sponsoredEl = card.querySelector('.s-label-popover-default, [data-component-type="sp-sponsored-result"]');
        const text = card.textContent.toLowerCase();
        return sponsoredEl !== null || text.includes('sponsored') || text.includes('إعلان');
    }

    extractImage(card) {
        const img = card.querySelector('img.s-image, .s-product-image-container img');
        return img?.src || '';
    }

    /**
     * Get page metadata
     */
    getPageInfo() {
        const keyword = this.extractKeyword();
        const resultCount = this.extractResultCount();

        return {
            keyword,
            resultCount,
            marketplace: this.marketplace,
            pageUrl: window.location.href
        };
    }

    extractResultCount() {
        const resultEl = this.root.querySelector('.s-breadcrumb .a-text-bold, [data-component-type="s-result-info-bar"]');
        if (resultEl) {
            const text = resultEl.textContent;
            const match = text.match(/[\d,]+/);
            if (match) {
                return parseInt(match[0].replace(/,/g, '')) || 0;
            }
        }
        return 0;
    }

    // ==================== BSR ENRICHMENT ====================

    /**
     * Enrich products with BSR by fetching product pages
     * @param {Array} products - Products from SERP
     * @param {Object} options - Configuration options
     * @param {Number} options.limit - Max products to enrich (default 15)
     * @param {Number} options.batchSize - Parallel requests per batch (default 3)
     * @param {Number} options.batchDelay - Ms between batches (default 500)
     * @param {Function} options.onProgress - Progress callback (current, total, message)
     * @returns {Promise<Array>} Products with BSR added where needed
     */
    async enrichWithBSR(products, options = {}) {
        // Support legacy call: enrichWithBSR(products, limit) where limit is a number
        if (typeof options === 'number') {
            options = { limit: options };
        }

        // Extract settings with safe defaults (optimized for speed + avoiding rate limits)
        const TOP_N = options.limit || 15;
        const BATCH_SIZE = options.batchSize || 3;
        const BATCH_DELAY = options.batchDelay || 500;
        const onProgress = options.onProgress || null;

        // Enrich products that need BSR data (skip if they already have BSR and category)
        const needsEnrichment = products
            .slice(0, TOP_N)
            .map((p, idx) => ({ product: p, originalIdx: idx }))
            .filter(item => {
                const hasBsr = !!item.product.bsr && !!item.product.category;
                // Don't skip enrichment even if they have sales badge, because we need category, brand, BSR and actual sellers
                return !hasBsr;
            });

        if (needsEnrichment.length === 0) {
            console.log('All products already have BSR data');
            if (onProgress) onProgress(0, 0, 'All products have BSR data');
            return products;
        }

        console.log(`Enriching ${needsEnrichment.length} products (of ${TOP_N}) with BSR [batch: ${BATCH_SIZE}, delay: ${BATCH_DELAY}ms]`);
        const enriched = [...products];
        let completed = 0;

        for (let i = 0; i < needsEnrichment.length; i += BATCH_SIZE) {
            const batch = needsEnrichment.slice(i, i + BATCH_SIZE);

            // Fetch batch in parallel
            const results = await Promise.all(
                batch.map(item => this.fetchProductBSR(item.product.asin))
            );

            // Update products with BSR, category, and brand
            results.forEach((result, idx) => {
                const originalIdx = batch[idx].originalIdx;
                if (result) {
                    // Always set category (from BSR or breadcrumbs)
                    if (result.category) {
                        enriched[originalIdx].category = result.category;
                        enriched[originalIdx].bsr_category = result.category;
                    }

                    if (result.bsr) {
                        enriched[originalIdx].bsr = result.bsr;
                        console.log(`Got BSR ${result.bsr} in "${result.category}" for ${batch[idx].product.asin}`);
                    } else if (result.noSalesData) {
                        // Product has no BSR - mark as 0 sales (new product)
                        enriched[originalIdx].bsr = null;
                        enriched[originalIdx].monthly_sales = 0; // Explicit 0 for new products
                        enriched[originalIdx].is_new_product = true;
                        console.log(`No BSR for ${batch[idx].product.asin}, treating as 0 sales (new product)`);
                    }

                    if (result.brand) {
                        enriched[originalIdx].brand = result.brand;
                        console.log(`Got Brand "${result.brand}" for ${batch[idx].product.asin}`);
                    }

                    // Store seller count
                    if (result.seller_count !== null && result.seller_count !== undefined) {
                        const existingCount = enriched[originalIdx].seller_count;
                        if (existingCount !== null && existingCount !== undefined) {
                            enriched[originalIdx].seller_count = Math.max(existingCount, result.seller_count);
                        } else {
                            enriched[originalIdx].seller_count = result.seller_count;
                        }
                        console.log(`Got ${result.seller_count} sellers for ${batch[idx].product.asin} (final: ${enriched[originalIdx].seller_count})`);
                    }
                }
            });

            completed = Math.min(i + BATCH_SIZE, needsEnrichment.length);

            // Progress callback
            if (onProgress) {
                onProgress(completed, needsEnrichment.length, `Fetching BSR... (${completed}/${needsEnrichment.length})`);
            }

            // Delay between batches (except last)
            if (i + BATCH_SIZE < needsEnrichment.length) {
                await new Promise(r => setTimeout(r, BATCH_DELAY));
            }
        }

        return enriched;
    }

    /**
     * Fetch a single product page and extract BSR, category, and brand
     * @returns {Object|null} { bsr: number, category: string, brand: string } or null
     */
    async fetchProductBSR(asin) {
        const url = `${window.location.origin}/dp/${asin}`;
        try {
            const response = await fetch(url);
            if (!response.ok) {
                console.warn(`BSR fetch failed for ${asin}: HTTP ${response.status} - URL: ${url}`);
                return null;
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // ===== Extract Brand =====
            let brand = null;
            let dateAvailable = null;

            // ===== Extract Date First Available =====
            const datePatterns = ['Date First Available', 'تاريخ توفر أول منتج'];

            // Method 1: Table rows
            const dateThs = doc.querySelectorAll('th');
            for (const th of dateThs) {
                if (datePatterns.some(p => th.textContent.includes(p))) {
                    const td = th.nextElementSibling;
                    if (td) {
                        dateAvailable = td.textContent.trim();
                        break;
                    }
                }
            }

            // Method 2: List items
            if (!dateAvailable) {
                const listItems = doc.querySelectorAll('li .a-list-item, #detailBullets_feature_div li');
                for (const li of listItems) {
                    const text = li.textContent;
                    if (datePatterns.some(p => text.includes(p))) {
                        // The text often looks like "Date First Available : 15 August 2021"
                        const parts = text.split(/:|：/);
                        if (parts.length > 1) {
                            dateAvailable = parts[1].trim().replace(/\u200e|\u200f/g, '');
                            break;
                        }
                    }
                }
            }

            // ===== Extract Brand =====
            // Try bylineInfo (e.g., "Visit the Sony Store" or "Brand: Sony")
            const byline = doc.querySelector('#bylineInfo');
            if (byline) {
                const bylineText = byline.textContent.trim();
                // "Visit the X Store" -> extract X
                const visitMatch = bylineText.match(/Visit the (.+?) Store/i);
                if (visitMatch) {
                    brand = visitMatch[1].trim();
                } else if (bylineText.includes('Brand:')) {
                    brand = bylineText.replace('Brand:', '').trim();
                } else {
                    // Just use the link text (usually brand name)
                    brand = bylineText.replace(/^by\s+/i, '').trim();
                }
            }

            // Fallback: Product Overview table
            if (!brand) {
                const brandRow = doc.querySelector('.po-brand .a-span9, .po-brand .po-break-word');
                if (brandRow) {
                    brand = brandRow.textContent.trim();
                }
            }

            // Fallback: Detail bullets
            if (!brand) {
                const detailBullets = doc.querySelectorAll('#detailBullets_feature_div li, .detail-bullet-list li');
                for (const li of detailBullets) {
                    const text = li.textContent;
                    if (text.includes('Brand') || text.includes('العلامة التجارية')) {
                        const match = text.match(/(?:Brand|العلامة التجارية)[:\s]+([^\n]+)/i);
                        if (match) {
                            brand = match[1].trim().replace(/\u200e|\u200f/g, ''); // Remove RTL markers
                        }
                        break;
                    }
                }
            }

            // ===== Extract Seller Count =====
            // The "Other sellers on Amazon" section may exist in static HTML
            let sellerCount = null;

            // Method 1: Look for aod-ingress-link
            const otherSellersLink = doc.querySelector('#aod-ingress-link');
            if (otherSellersLink) {
                const linkText = otherSellersLink.textContent || '';
                // Simple and robust: just look for any number in parentheses
                // This handles "New (36) from", "Used (5) from", or Arabic "(35) جديد من"
                const sellersMatch = linkText.match(/\((\d+)\)/);
                if (sellersMatch) {
                    sellerCount = parseInt(sellersMatch[1], 10);
                    console.log(`Got ${sellerCount} sellers for ${asin} (aod-ingress-link)`);
                }
            }

            // Method 2: Look for daodi-content section
            if (sellerCount === null) {
                const daodiContent = doc.querySelector('.daodi-content');
                if (daodiContent) {
                    const text = daodiContent.textContent || '';
                    // Simple regex: look for any number in parentheses
                    const sellersMatch = text.match(/\((\d+)\)/);
                    if (sellersMatch) {
                        sellerCount = parseInt(sellersMatch[1], 10);
                        console.log(`Got ${sellerCount} sellers for ${asin} (daodi-content)`);
                    }
                }
            }

            // Method 3: Look for olp (offer listing page) links with seller count in URL or text
            if (sellerCount === null) {
                const offerLinks = doc.querySelectorAll('a[href*="offer-listing"], a[href*="olp"]');
                for (const link of offerLinks) {
                    const text = link.textContent || '';
                    // Match patterns like "36 offers", "(36) from", "New (36)"
                    const sellersMatch = text.match(/\((\d+)\)|(\d+)\s*(?:offers?|sellers?|عرض|بائع)/i);
                    if (sellersMatch) {
                        sellerCount = parseInt(sellersMatch[1] || sellersMatch[2], 10);
                        console.log(`Got ${sellerCount} sellers for ${asin} (offer link)`);
                        break;
                    }
                }
            }

            // Method 4: Search for "See All Buying Options" section
            if (sellerCount === null) {
                const buyingOptionsBtn = doc.querySelector('#buybox-see-all-buying-choices, #seeAllBuyingChoices, [data-action="show-all-offers-display"]');
                if (buyingOptionsBtn) {
                    const parent = buyingOptionsBtn.closest('.a-box, .a-section') || buyingOptionsBtn.parentElement;
                    if (parent) {
                        const text = parent.textContent || '';
                        const sellersMatch = text.match(/(?:New|Used|جديد|مستعمل)\s*\((\d+)\)/i);
                        if (sellersMatch) {
                            sellerCount = parseInt(sellersMatch[1], 10);
                            console.log(`Got ${sellerCount} sellers for ${asin} (buying options)`);
                        }
                    }
                }
            }

            // Method 5: Look for embedded JSON data in script tags
            if (sellerCount === null) {
                const scripts = doc.querySelectorAll('script[type="text/javascript"], script:not([type])');
                for (const script of scripts) {
                    const content = script.textContent || '';
                    // Look for offer count in JSON
                    const offerCountMatch = content.match(/"offerCount"\s*:\s*(\d+)/i);
                    if (offerCountMatch) {
                        sellerCount = parseInt(offerCountMatch[1], 10);
                        console.log(`Got ${sellerCount} sellers for ${asin} (embedded JSON)`);
                        break;
                    }
                    // Look for numberOfItems or itemCount
                    const itemCountMatch = content.match(/"(?:numberOfItems|itemCount|sellerCount)"\s*:\s*(\d+)/i);
                    if (itemCountMatch) {
                        sellerCount = parseInt(itemCountMatch[1], 10);
                        console.log(`Got ${sellerCount} sellers for ${asin} (embedded JSON itemCount)`);
                        break;
                    }
                }
            }

            // Method 6: General text search as last resort
            if (sellerCount === null) {
                const bodyText = doc.body?.textContent || '';
                // Try multiple patterns
                const patterns = [
                    /Other\s*sellers\s*on\s*Amazon.*?(?:New|Used|جديد|مستعمل)\s*\((\d+)\)/is,
                    /(?:New|Used)\s*\((\d+)\)\s*from/i,
                    /(\d+)\s*(?:new|used)\s*(?:offers?|from)/i,
                    /See All\s*(\d+)\s*Buying/i,
                    /بائعين آخرين.*?\((\d+)\)/is
                ];
                for (const pattern of patterns) {
                    const match = bodyText.match(pattern);
                    if (match) {
                        sellerCount = parseInt(match[1], 10);
                        console.log(`Got ${sellerCount} sellers for ${asin} (body text pattern)`);
                        break;
                    }
                }
            }

            // Method 7: Check for "Ships from and sold by" section - indicates 1 seller (Amazon or single merchant)
            if (sellerCount === null) {
                const shipsFrom = doc.querySelector('#merchant-info, #tabular-buybox-truncate-0');
                if (shipsFrom) {
                    const text = shipsFrom.textContent || '';
                    if (text.includes('Amazon') || text.includes('أمازون')) {
                        // Product sold directly by Amazon, so at least 1 seller
                        sellerCount = 1;
                        console.log(`Got ${sellerCount} sellers for ${asin} (Amazon direct)`);
                    }
                }
            }

            // Default to 1 if no seller count found (every product has at least 1 seller)
            if (sellerCount === null) {
                sellerCount = 1;
                console.log(`Defaulting to ${sellerCount} seller for ${asin} (no other sellers section found)`);
            }

            console.log(`Final seller count for ${asin}: ${sellerCount}`);

            // ===== Extract BSR =====
            // Method 1: Look for ALL table rows with prodDetSectionEntry class
            const allBsrTh = doc.querySelectorAll('th.prodDetSectionEntry, th.a-color-secondary');
            for (const th of allBsrTh) {
                const thText = th.textContent || '';
                if (thText.includes('Best Sellers Rank') || thText.includes('Amazon Best Sellers Rank') || thText.includes('تصنيف الأفضل مبيعاً')) {
                    const bsrTd = th.nextElementSibling;
                    if (bsrTd) {
                        // Get the first ranking (main category)
                        const firstItem = bsrTd.querySelector('.a-list-item span, span');
                        if (firstItem) {
                            const text = firstItem.textContent;
                            const match = text.match(/#([,\d]+)\s+(?:in|في)\s+([^(\n]+)/i);
                            if (match) {
                                const bsr = parseInt(match[1].replace(/,/g, ''), 10);
                                let category = match[2]?.trim() || 'Unknown';
                                category = category.replace(/See Top.*$/i, '').replace(/\(.*$/i, '').trim();
                                console.log(`Got BSR ${bsr} in "${category}" for ${asin} (table row method)`);
                                return { bsr, category, brand, date_available: dateAvailable, seller_count: sellerCount };
                            }
                        }
                        // Maybe direct text content has BSR
                        const tdText = bsrTd.textContent || '';
                        const tdMatch = tdText.match(/#([,\d]+)\s+(?:in|في)\s+([^(\n]+)/i);
                        if (tdMatch) {
                            const bsr = parseInt(tdMatch[1].replace(/,/g, ''), 10);
                            let category = tdMatch[2]?.trim() || 'Unknown';
                            category = category.replace(/See Top.*$/i, '').replace(/\(.*$/i, '').trim();
                            console.log(`Got BSR ${bsr} in "${category}" for ${asin} (table td text method)`);
                            return { bsr, category, brand, seller_count: sellerCount };
                        }
                    }
                }
            }

            // Method 2: Look for ul.zg_hrsr (bestseller list) or vertical list
            const bsrLists = doc.querySelectorAll('ul.zg_hrsr, .a-unordered-list.a-nostyle.a-vertical');
            for (const bsrList of bsrLists) {
                const items = bsrList.querySelectorAll('li .a-list-item span, li span');
                for (const item of items) {
                    const text = item.textContent;
                    const match = text.match(/#([,\d]+)\s+(?:in|في)\s+([^(\n]+)/i);
                    if (match) {
                        const bsr = parseInt(match[1].replace(/,/g, ''), 10);
                        let category = match[2]?.trim() || 'Unknown';
                        category = category.replace(/See Top.*$/i, '').replace(/\(.*$/i, '').trim();
                        console.log(`Got BSR ${bsr} in "${category}" for ${asin} (list method)`);
                        return { bsr, category, brand, seller_count: sellerCount };
                    }
                }
            }

            // Method 3: Try multiple selectors for BSR
            const selectors = [
                '#productDetails_detailBullets_sections1',
                '#detailBulletsWrapper_feature_div',
                '#prodDetails',
                '#productDetails_techSpec_section_1',
                '.detail-bullet-list',
                '#detailBullets_feature_div',
                '#productDetails_db_sections',
                '.pdTab',
                '#detailBulletsV2_feature_div',
                'table.a-keyvalue'
            ];

            for (const selector of selectors) {
                const el = doc.querySelector(selector);
                if (el) {
                    const text = el.textContent;
                    // Match patterns like "#1,234 in Kitchen", "رقم 1,234 في المطبخ"
                    // Capture both the number and the category
                    const match = text.match(/#([,\d]+)\s+(?:in|في)\s+([^(\n#]+)/i);
                    if (match) {
                        const bsr = parseInt(match[1].replace(/,/g, ''), 10);
                        let category = match[2]?.trim() || 'Unknown';
                        // Clean up category - remove trailing parentheses content, "See Top 100", etc.
                        category = category.replace(/\s*\(.*$/, '').replace(/See Top.*$/i, '').trim();
                        console.log(`Got BSR ${bsr} in "${category}" for ${asin} (selector: ${selector})`);
                        return { bsr, category, brand, seller_count: sellerCount };
                    }
                }
            }

            // Method 4: Fallback - search entire body for BSR pattern
            const bodyText = doc.body?.textContent || '';
            // Look for "Best Sellers Rank:" followed by a number
            const bsrMatch = bodyText.match(/(?:Best Sellers Rank|تصنيف الأفضل مبيعاً)[:\s]+#?([,\d]+)\s+(?:in|في)\s+([^(\n#]+)/i);
            if (bsrMatch) {
                const bsr = parseInt(bsrMatch[1].replace(/,/g, ''), 10);
                let category = bsrMatch[2]?.trim() || 'Unknown';
                category = category.replace(/\s*\(.*$/, '').replace(/See Top.*$/i, '').trim();
                console.log(`Got BSR ${bsr} in "${category}" for ${asin} (fallback body search)`);
                return { bsr, category, brand, seller_count: sellerCount };
            }

            // Method 5: Extract category from breadcrumbs when no BSR found
            // Breadcrumbs format: Home & Kitchen › Furniture › ... › Chair Mats
            let breadcrumbCategory = null;
            const breadcrumbContainer = doc.querySelector('#wayfinding-breadcrumbs_feature_div, .a-breadcrumb');
            if (breadcrumbContainer) {
                // Get all category links (excluding dividers)
                const categoryLinks = breadcrumbContainer.querySelectorAll('a.a-link-normal');
                if (categoryLinks.length > 0) {
                    // Use the FIRST category (main category like "Home & Kitchen") for BSR estimation
                    // This matches how BSR categories are typically reported
                    breadcrumbCategory = categoryLinks[0].textContent.trim();
                    console.log(`Got category from breadcrumbs for ${asin}: "${breadcrumbCategory}"`);
                }
            }

            // BSR not found - return with category from breadcrumbs (for 0 sales estimation)
            // Products without BSR are treated as new/low-sales products
            const finalCategory = breadcrumbCategory || 'default';
            if (brand || breadcrumbCategory) {
                console.log(`No BSR for ${asin}, using category "${finalCategory}" for estimation (0 sales assumed)`);
                return { bsr: null, category: finalCategory, brand, noSalesData: true, seller_count: sellerCount };
            }

            // Nothing found - still return with default category
            console.warn(`No BSR or category found for ${asin} - URL: ${url}`);
            return { bsr: null, category: 'default', brand: null, noSalesData: true, seller_count: sellerCount };
        } catch (e) {
            console.warn(`Failed to fetch BSR for ${asin}: ${e.message} - URL: ${url}`);
            return { bsr: null, category: 'default', brand: null, noSalesData: true, seller_count: null };
        }
    }
}

// Make available globally
if (typeof window !== 'undefined') {
    window.SerpParser = SerpParser;
}

