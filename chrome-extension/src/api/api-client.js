// API Client for Backend Communication
// Handles authenticated requests to Laravel backend

class ApiClient {
    constructor() {
        this.baseUrl = 'http://127.0.0.1:8000'; // Development URL
        this.token = null;
        this.initialized = false;
    }

    /**
     * Initialize API client with stored token
     */
    async init() {
        if (this.initialized) return;

        try {
            if (typeof chrome !== 'undefined' && chrome.storage) {
                const data = await chrome.storage.local.get(['authToken']);
                this.token = data.authToken || null;
            }
            this.initialized = true;
        } catch (error) {
            console.error('ApiClient init error:', error);
        }
    }

    /**
     * Set the authentication token
     * @param {string} token - JWT token
     */
    setToken(token) {
        this.token = token;
    }

    /**
     * Make an authenticated API request
     * @param {string} method - HTTP method
     * @param {string} endpoint - API endpoint
     * @param {object} body - Request body
     * @returns {Promise<object>} - Response data
     */
    async request(method, endpoint, body = null) {
        await this.init();

        const options = {
            method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        };

        // Add auth token if available
        if (this.token) {
            options.headers['Authorization'] = `Bearer ${this.token}`;
        }

        if (body) {
            options.body = JSON.stringify(body);
        }

        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, options);

            // Handle 401 - token expired
            if (response.status === 401 && this.token) {
                const refreshed = await this.refreshToken();
                if (refreshed) {
                    options.headers['Authorization'] = `Bearer ${this.token}`;
                    return await fetch(`${this.baseUrl}${endpoint}`, options);
                }
                throw new Error('Session expired. Please login again.');
            }

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `API error: ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API request error:', error);
            throw error;
        }
    }

    /**
     * Refresh the authentication token
     */
    async refreshToken() {
        if (!this.token) return false;

        try {
            const response = await fetch(`${this.baseUrl}/api/auth/refresh`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) return false;

            const data = await response.json();
            this.token = data.token;

            if (typeof chrome !== 'undefined' && chrome.storage) {
                await chrome.storage.local.set({ authToken: this.token });
            }

            return true;
        } catch (error) {
            console.error('Token refresh error:', error);
            return false;
        }
    }

    // =================== Constants API ===================

    /**
     * Get all algorithm constants
     */
    async getConstants() {
        return this.request('GET', '/api/constants');
    }

    /**
     * Get constants for specific marketplace
     * @param {string} marketplace - e.g., 'amazon.com', 'amazon.eg'
     */
    async getConstantsByMarketplace(marketplace) {
        return this.request('GET', `/api/constants/${marketplace}`);
    }

    /**
     * Get constants version
     */
    async getConstantsVersion() {
        return this.request('GET', '/api/constants/version');
    }

    // =================== Fees API ===================

    /**
     * Get FBA fees for marketplace
     * @param {string} marketplace 
     */
    async getFees(marketplace) {
        return this.request('GET', `/api/fees/${marketplace}`);
    }

    /**
     * Get seasonality multipliers
     */
    async getSeasonality() {
        return this.request('GET', '/api/seasonality');
    }

    // =================== Feedback API ===================

    /**
     * Submit sales feedback for calibration
     * @param {object} data - Feedback data
     */
    async submitSalesFeedback(data) {
        return this.request('POST', '/api/feedback/sales', {
            asin: data.asin,
            marketplace: data.marketplace,
            category: data.category,
            bsr: data.bsr,
            estimated_sales: data.estimatedSales,
            actual_sales: data.actualSales,
            sales_window_days: data.salesWindowDays || 30
        });
    }

    /**
     * Submit estimate correction
     * @param {object} data - Correction data
     */
    async submitCorrection(data) {
        return this.request('POST', '/api/feedback/correction', {
            asin: data.asin,
            marketplace: data.marketplace,
            field: data.field,
            original_value: data.originalValue,
            corrected_value: data.correctedValue,
            reason: data.reason
        });
    }

    /**
     * Get user's feedback history
     */
    async getFeedbackHistory() {
        return this.request('GET', '/api/feedback/history');
    }

    // =================== Analytics API ===================

    /**
     * Get category insights
     * @param {string} categoryId 
     */
    async getCategoryAnalytics(categoryId) {
        return this.request('GET', `/api/analytics/category/${categoryId}`);
    }

    /**
     * Get market trends
     */
    async getTrends() {
        return this.request('GET', '/api/analytics/trends');
    }

    /**
     * Analyze scraped product data
     * @param {object} productData 
     */
    async analyzeProduct(productData) {
        return this.request('POST', '/api/analytics/product', productData);
    }

    // =================== Keywords API ===================

    /**
     * Get popular keywords for marketplace
     * @param {string} marketplace 
     */
    async getPopularKeywords(marketplace) {
        return this.request('GET', `/api/keywords/popular/${marketplace}`);
    }

    /**
     * Cache keywords discovered by extension
     * @param {string} marketplace 
     * @param {Array} keywords 
     */
    async cacheKeywords(marketplace, keywords) {
        return this.request('POST', '/api/keywords/cache', {
            marketplace,
            keywords
        });
    }

    // =================== Reverse ASIN API ===================

    /**
     * Get keywords for an ASIN
     * @param {string} asin 
     */
    async getReverseAsinKeywords(asin) {
        return this.request('GET', `/api/reverse-asin/${asin}/keywords`);
    }

    /**
     * Submit keyword ranking data
     * @param {object} data 
     */
    async submitKeywordRanking(data) {
        return this.request('POST', '/api/reverse-asin/ranking', {
            asin: data.asin,
            marketplace: data.marketplace,
            keyword: data.keyword,
            position: data.position,
            is_sponsored: data.isSponsored
        });
    }
}

// Singleton instance
const apiClient = new ApiClient();

// Make available globally
if (typeof window !== 'undefined') {
    window.ApiClient = apiClient;
}
