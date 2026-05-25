// Enhanced Amazon Product Data Scraper
class DataScraper {
    constructor(root = document) {
        this.root = root;
        this.marketplace = window.location.hostname.includes('.eg') ? 'amazon.eg' : 'amazon.com';
    }

    /**
     * Extract complete product data
     */
    extractProductData() {
        return {
            // Basic Info
            asin: this.extractASIN(),
            title: this.extractTitle(),
            brand: this.extractBrand(),

            // Pricing
            price: this.extractPrice(),
            originalPrice: this.extractOriginalPrice(),
            discountPercent: this.extractDiscountPercent(),

            // Ratings & Reviews
            rating: this.extractRating(),
            reviewCount: this.extractReviewCount(),
            answeredQuestions: this.extractAnsweredQuestions(),

            // Inventory & Availability
            inStock: this.extractInStock(),
            availability: this.extractAvailability(),

            // Category & Rankings
            category: this.extractCategory(),
            categoryPath: this.extractCategoryPath(),
            bsr: this.extractBSR(),
            bsrCategory: this.extractBSRCategory(),

            // Product Details
            dimensions: this.extractDimensions(),
            weight: this.extractWeight(),

            // Seller Info
            seller: this.extractSeller(),
            sellerCount: this.extractSellerCount(),
            isFBA: this.checkIfFBA(),

            // Additional
            url: window.location.href,
            marketplace: this.marketplace,
            scrapedAt: new Date().toISOString()
        };
    }

    extractASIN() {
        const match = window.location.href.match(/\/dp\/([A-Z0-9]{10})/);
        return match ? match[1] : null;
    }

    extractTitle() {
        return this.root.querySelector('#productTitle')?.textContent?.trim() || '';
    }

    extractBrand() {
        let brand = this.root.querySelector('#bylineInfo')?.textContent?.trim() ||
            this.root.querySelector('.po-brand .po-break-word')?.textContent?.trim() || '';

        // Remove common prefixes in English and Arabic
        brand = brand.replace('Brand:', '')
            .replace('Visit the', '')
            .replace('Store', '')
            .replace('قم بزيارة متجر', '')  // Arabic "Visit the store"
            .replace('قم بزيارة', '')       // Arabic "Visit"  
            .replace('متجر', '')           // Arabic "store"
            .trim();

        return brand;
    }

    extractPrice() {
        const priceWhole = this.root.querySelector('.a-price .a-price-whole')?.textContent?.trim();
        const priceFraction = this.root.querySelector('.a-price .a-price-fraction')?.textContent?.trim();

        if (priceWhole) {
            return `${priceWhole}${priceFraction || '00'}`.replace(/[^0-9.]/g, '');
        }

        // Fallback
        const offscreen = this.root.querySelector('.a-price .a-offscreen')?.textContent?.trim();
        return offscreen ? offscreen.replace(/[^0-9.]/g, '') : '0';
    }

    extractOriginalPrice() {
        const listPrice = this.root.querySelector('.a-price.a-text-price .a-offscreen')?.textContent?.trim();
        return listPrice ? listPrice.replace(/[^0-9.]/g, '') : null;
    }

    extractDiscountPercent() {
        const discount = this.root.querySelector('.savingsPercentage')?.textContent?.trim();
        return discount ? discount.replace(/[^0-9]/g, '') : '0';
    }

    extractRating() {
        const ratingText = this.root.querySelector('[data-hook="rating-out-of-text"]')?.textContent?.trim();
        if (ratingText) {
            const match = ratingText.match(/(\d+\.?\d*)/);
            return match ? match[1] : '0';
        }
        return '0';
    }

    extractReviewCount() {
        const reviewText = this.root.querySelector('#acrCustomerReviewText')?.textContent?.trim();
        return reviewText ? reviewText.replace(/[^0-9]/g, '') : '0';
    }

    extractAnsweredQuestions() {
        const qaText = this.root.querySelector('#askATFLink')?.textContent?.trim();
        return qaText ? qaText.replace(/[^0-9]/g, '') : '0';
    }

    extractInStock() {
        const availability = this.root.querySelector('#availability span')?.textContent?.trim().toLowerCase() || '';
        // Check for English and Arabic availability text
        return availability.includes('in stock') ||
            availability.includes('available') ||
            availability.includes('متوفر') ||  // Arabic for "available"
            availability.includes('متاح');     // Another Arabic word for "available"
    }

    extractAvailability() {
        return this.root.querySelector('#availability span')?.textContent?.trim() || 'Unknown';
    }

    extractCategory() {
        const breadcrumb = this.root.querySelector('#wayfinding-breadcrumbs_feature_div ul');
        if (breadcrumb) {
            const items = breadcrumb.querySelectorAll('li');
            if (items.length > 0) {
                return items[items.length - 1]?.textContent?.trim() || '';
            }
        }
        return '';
    }

    extractCategoryPath() {
        const breadcrumb = this.root.querySelector('#wayfinding-breadcrumbs_feature_div ul');
        if (breadcrumb) {
            const items = Array.from(breadcrumb.querySelectorAll('li'));
            return items
                .map(item => item.textContent?.trim())
                .filter(text => text && text !== '›' && text !== '>'); // Filter out separators
        }
        return [];
    }

    extractBSR() {
        // Convert Arabic numbers to Latin
        const arabicToLatin = (str) => {
            const arabicNums = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            const latinNums = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            let result = str;
            arabicNums.forEach((arabic, index) => {
                result = result.replace(new RegExp(arabic, 'g'), latinNums[index]);
            });
            return result;
        };

        const extractAllRankings = (text) => {
            const rankings = [];
            text = arabicToLatin(text);

            // Split by lines and find all BSR entries
            const lines = text.split('\n');
            for (let i = 0; i < lines.length; i++) {
                const line = lines[i].trim();

                // Match patterns like "#4 in Home & Kitchen" or "#٤ في Home & Kitchen"
                let match = line.match(/#([\d,]+)\s+(?:in|في)\s+(.+?)(?:\(|\s*\(|$)/); // Adjusted regex to handle trailing parentheses or end of line

                if (match) {
                    const rank = match[1].replace(/,/g, '');
                    let category = match[2].trim();

                    // Clean up category name
                    category = category.replace(/\s*\(.*?\)\s*/g, '').trim();

                    rankings.push({
                        rank: parseInt(rank),
                        category: category
                    });
                }
            }

            return rankings;
        };

        // Look in product details table
        const detailBullets = this.root.querySelector('#detailBullets_feature_div');
        if (detailBullets) {
            const rankings = extractAllRankings(detailBullets.textContent);
            if (rankings.length > 0) {
                // Return main category (first one) but include all rankings
                return {
                    rank: rankings[0].rank.toString(),
                    category: rankings[0].category,
                    allRankings: rankings  // Store all rankings
                };
            }
        }

        // Look in prodDetails table
        const prodDetails = this.root.querySelector('#prodDetails');
        if (prodDetails) {
            const rankings = extractAllRankings(prodDetails.textContent);
            if (rankings.length > 0) {
                return {
                    rank: rankings[0].rank.toString(),
                    category: rankings[0].category,
                    allRankings: rankings
                };
            }
        }

        // Specific Table Row Check (Targeting the user's specific HTML structure)
        const thElements = this.root.querySelectorAll('th.prodDetSectionEntry, th.a-color-secondary');
        for (const th of thElements) {
            const headerText = th.textContent.trim().toLowerCase();
            if (headerText.includes('best sellers rank') || headerText.includes('تصنيف الأفضل مبيعاً')) {
                const td = th.nextElementSibling;
                if (td) {
                    const rankings = extractAllRankings(td.textContent);
                    if (rankings.length > 0) {
                        return {
                            rank: rankings[0].rank.toString(),
                            category: rankings[0].category,
                            allRankings: rankings
                        };
                    }
                }
            }
        }

        // Fallback: try old method for single BSR
        const detailText = this.root.textContent || document.body.textContent; // Fallback to body if root text empty? No, root should have text.
        const arabicText = arabicToLatin(detailText);

        // Try English pattern
        let match = arabicText.match(/#([\d,]+)\s+in\s+([^(]+)/);
        // Try Arabic pattern
        if (!match) {
            match = arabicText.match(/#([\d,]+)\s+في\s+([^(]+)/);
        }

        if (match) {
            return {
                rank: match[1].replace(/,/g, ''),
                category: match[2].trim(),
                allRankings: [{
                    rank: parseInt(match[1].replace(/,/g, '')),
                    category: match[2].trim()
                }]
            };
        }

        return null;
    }

    extractBSRCategory() {
        const bsr = this.extractBSR();
        return bsr ? bsr.category : '';
    }

    extractDimensions() {
        console.log('--- Extracting Dimensions ---');
        const rules = [
            { selector: '#productDetails_detailBullets_sections1 tr', label: 'th', value: 'td', name: 'Details Table' },
            { selector: '#detailBullets_feature_div li', label: null, value: null, isList: true, name: 'Detail Bullets' },
            { selector: '#featurebullets_feature_div li', label: null, value: null, isList: true, name: 'Feature Bullets (Alt)' },
            { selector: '#prodDetails tr', label: 'th, td.label', value: 'td', name: 'ProdDetails Table' },
            { selector: '#productOverview_feature_div tr', label: 'td:first-child', value: 'td:last-child', name: 'Product Overview' }
        ];

        const keywords = ['dimensions', 'size', 'أبعاد', 'القياسات', 'الحجم'];

        for (const rule of rules) {
            const elements = this.root.querySelectorAll(rule.selector);
            console.log(`Checking ${rule.name}: found ${elements.length} elements`);

            for (const el of elements) {
                let text = '';

                if (rule.isList) {
                    text = el.textContent.replace(/\s+/g, ' ').trim();
                } else {
                    const label = el.querySelector(rule.label)?.textContent?.trim().toLowerCase() || '';
                    if (keywords.some(k => label.includes(k))) {
                        let valEl = el.querySelector(rule.value);
                        if (valEl && valEl.classList.contains('label')) {
                            valEl = el.querySelector('td:not(.label)') || el.lastElementChild;
                        }

                        const val = valEl?.textContent?.trim() || '';
                        if (val) {
                            console.log(`Found match in ${rule.name} (Table): ${val}`);
                            // Clean up: remove text after semicolon (often weight)
                            return val.split(';')[0].trim();
                        }
                        continue;
                    }
                    continue;
                }

                const lowerText = text.toLowerCase();
                if (keywords.some(k => lowerText.includes(k))) {
                    console.log(`Found potential match in ${rule.name} (List): "${text}"`);

                    const regex = new RegExp(`(?:${keywords.join('|')})[^0-9]*([0-9].*)`, 'i');
                    const match = text.match(regex);
                    if (match && match[1]) {
                        console.log(`Regex matched: "${match[1].trim()}"`);
                        // Clean up: remove text after semicolon
                        return match[1].split(';')[0].trim();
                    }

                    const parts = text.split(/[:\u200e\u200f]/);
                    if (parts.length > 1) {
                        for (let i = 1; i < parts.length; i++) {
                            if (/[0-9]/.test(parts[i])) {
                                console.log(`Fallback split matched: "${parts[i].trim()}"`);
                                return parts[i].split(';')[0].trim();
                            }
                        }
                    }
                }
            }
        }
        console.log('--- Dimensions NOT found ---');
        return '';
    }

    extractWeight() {
        console.log('--- Extracting Weight ---');

        // 1. Try to extract from dimensions string first (e.g. "25x25x2 cm; 320g")
        // We temporarily re-run a lightweight search for dimensions to find that string
        const dimKeywords = ['dimensions', 'size', 'أبعاد'];
        const listItems = this.root.querySelectorAll('#detailBullets_feature_div li, #featurebullets_feature_div li');
        for (const item of listItems) {
            const text = item.textContent.replace(/\s+/g, ' ').trim();
            if (dimKeywords.some(k => text.toLowerCase().includes(k)) && text.includes(';')) {
                const parts = text.split(';');
                if (parts.length > 1) {
                    const potentialWeight = parts[1].trim();
                    // Check if it looks like weight (contains g, kg, lb)
                    if (/[0-9]+.*(?:g|kg|lb|oz|gram)/i.test(potentialWeight)) {
                        console.log(`Extracted weight from dimensions line: ${potentialWeight}`);
                        return potentialWeight;
                    }
                }
            }
        }

        const rules = [
            { selector: '#productDetails_detailBullets_sections1 tr', label: 'th', value: 'td', name: 'Details Table' },
            { selector: '#detailBullets_feature_div li', label: null, value: null, isList: true, name: 'Detail Bullets' },
            { selector: '#featurebullets_feature_div li', label: null, value: null, isList: true, name: 'Feature Bullets (Alt)' },
            { selector: '#prodDetails tr', label: 'th, td.label', value: 'td', name: 'ProdDetails Table' },
            { selector: '#productOverview_feature_div tr', label: 'td:first-child', value: 'td:last-child', name: 'Product Overview' }
        ];

        const keywords = ['weight', 'الوزن', 'وزن'];
        // Terms to exclude to avoid "Weight Limit", "Capacity", "Shipping Weight" (if unwanted)
        const excludeTerms = ['limit', 'capacity', 'maximum', 'shipping', 'حد', 'السعة', 'تحميل'];

        for (const rule of rules) {
            const elements = this.root.querySelectorAll(rule.selector);
            console.log(`Checking ${rule.name}: found ${elements.length} elements`);

            for (const el of elements) {
                let text = '';

                if (rule.isList) {
                    text = el.textContent.replace(/\s+/g, ' ').trim();
                } else {
                    const label = el.querySelector(rule.label)?.textContent?.trim().toLowerCase() || '';
                    if (keywords.some(k => label.includes(k)) && !excludeTerms.some(ex => label.includes(ex))) {
                        let valEl = el.querySelector(rule.value);
                        if (valEl && valEl.classList.contains('label')) {
                            valEl = el.querySelector('td:not(.label)') || el.lastElementChild;
                        }

                        const val = valEl?.textContent?.trim() || '';
                        if (val) {
                            console.log(`Found match in ${rule.name} (Table): ${val}`);
                            return val;
                        }
                    }
                    continue;
                }

                const lowerText = text.toLowerCase();
                // For lists, be stricter: must NOT contain exclude terms
                if (keywords.some(k => lowerText.includes(k)) && !excludeTerms.some(ex => lowerText.includes(ex))) {
                    console.log(`Found potential match in ${rule.name} (List): "${text}"`);

                    const match = text.match(new RegExp(`(?:${keywords.join('|')})[^0-9]*([0-9].*)`, 'i'));
                    if (match && match[1]) {
                        console.log(`Regex matched: "${match[1].trim()}"`);
                        return match[1].trim();
                    }

                    const parts = text.split(/[:\u200e\u200f]/);
                    if (parts.length > 1) {
                        for (let i = 1; i < parts.length; i++) {
                            if (/[0-9]/.test(parts[i])) {
                                console.log(`Fallback split matched: "${parts[i].trim()}"`);
                                return parts[i].trim();
                            }
                        }
                    }
                }
            }
        }
        console.log('--- Weight NOT found ---');
        return '';
    }

    extractImages() {
        const images = [];

        // Main image
        const mainImg = this.root.querySelector('#landingImage, #imgBlkFront');
        if (mainImg) {
            images.push(mainImg.src);
        }

        // Thumbnail images
        const thumbnails = this.root.querySelectorAll('#altImages img');
        thumbnails.forEach(img => {
            if (img.src && !images.includes(img.src)) {
                images.push(img.src.replace(/._.*_\./, '.'));
            }
        });

        return images.slice(0, 5); // Return max 5 images
    }

    extractSeller() {
        const sellerLink = this.root.querySelector('#sellerProfileTriggerId');
        if (sellerLink) {
            return sellerLink.textContent?.trim() || '';
        }

        const merchantInfo = this.root.querySelector('#merchant-info');
        if (merchantInfo) {
            return merchantInfo.textContent?.trim() || '';
        }
        return 'Amazon';
    }

    extractSellerCount() {
        // Method 1: Look for aod-ingress-link (Other sellers section)
        const otherSellersLink = this.root.querySelector('#aod-ingress-link');
        if (otherSellersLink) {
            const linkText = otherSellersLink.textContent || '';
            // Match pattern like "New (35) from" or "Used (5) from"
            const sellersMatch = linkText.match(/\((\d+)\)/);
            if (sellersMatch) {
                return parseInt(sellersMatch[1], 10);
            }
        }

        // Method 2: Look for offer listing links
        const offerLinks = this.root.querySelectorAll('a[href*="offer-listing"], a[href*="olp"]');
        for (const link of offerLinks) {
            const text = link.textContent || '';
            const match = text.match(/\((\d+)\)|(\d+)\s*(?:offers?|sellers?)/i);
            if (match) {
                return parseInt(match[1] || match[2], 10);
            }
        }

        // Default to 1 (at least the current seller exists)
        return 1;
    }

    checkIfFBA() {
        // Method 1: Check for specific fulfiller info structure (most reliable)
        const fulfillerDiv = document.querySelector('#fulfillerInfoFeature_feature_div');
        if (fulfillerDiv) {
            const labelText = fulfillerDiv.querySelector('.offer-display-feature-label')?.textContent?.toLowerCase() || '';
            const valueText = fulfillerDiv.querySelector('.offer-display-feature-text-message')?.textContent?.toLowerCase() || '';

            if (labelText.includes('fulfilled by') && valueText.includes('amazon')) {
                return true;
            }
        }

        // Method 2: Check for isAmazonFulfilled=1 in any link (very reliable indicator)
        const links = document.querySelectorAll('a[href*="isAmazonFulfilled=1"]');
        if (links.length > 0) {
            return true;
        }

        // Method 3: Check page text for FBA indicators
        const pageText = (this.root.textContent || '').toLowerCase();

        const fbaIndicators = [
            'fulfilled by amazon',
            'fulfillment by amazon',
            'ships from and sold by amazon',
            'ships from amazon',
        ];

        for (const indicator of fbaIndicators) {
            if (pageText.includes(indicator)) {
                return true;
            }
        }

        // Method 4: Check for the pattern "Fulfilled by" followed by "Amazon" with whitespace
        const fulfilledByMatch = pageText.match(/fulfilled\s+by\s*[\s\n]*amazon/i);
        if (fulfilledByMatch) {
            return true;
        }

        // Method 5: Check Arabic indicators
        const arabicText = this.root.textContent || '';
        const fbaIndicatorsArabic = [
            'تنفيذ بواسطة أمازون',
            'يتم الشحن من أمازون',
            'يشحن من أمازون',
            'تشحن من قبل أمازون'
        ];

        for (const indicator of fbaIndicatorsArabic) {
            if (arabicText.includes(indicator)) {
                return true;
            }
        }

        return false;
    }
}
// DataScraper is now available globally
