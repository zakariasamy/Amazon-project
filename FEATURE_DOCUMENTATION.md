# 🚀 Amazon Product Analyzer - Feature Documentation

> **Version**: 2.5 | **Last Updated**: January 2026
> **Target Markets**: Amazon US (amazon.com) and Amazon Egypt (amazon.eg)

---

## 📋 Table of Contents

1. [Search Page Market Analysis](#1-search-page-market-analysis)
2. [Reverse ASIN Keyword Discovery](#2-reverse-asin-keyword-discovery)
3. [Search Volume Estimation](#3-search-volume-estimation)
4. [Monthly Sales Estimation](#4-monthly-sales-estimation)
5. [Difficulty Score Calculation](#5-difficulty-score-calculation)
6. [Admin-Configurable Settings](#6-admin-configurable-settings)
7. [Currency & Marketplace Detection](#7-currency--marketplace-detection)
8. [Data Filtering Rules](#8-data-filtering-rules)

---

## 1. Search Page Market Analysis

### Overview
Analyzes the current Amazon search results page to provide market insights for a keyword.

### Step-by-Step Process

```
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 1: Fetch Configuration from Backend                           │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ GET /api/settings                                                   │
│                                                                     │
│ Settings Used:                                                      │
│ • search_page_products_limit (default: 20)                          │
│ • search_page_bsr_parallel_requests (default: 5)                    │
│ • search_page_bsr_delay_ms (default: 300)                           │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 2: Extract Products from Search Page DOM                       │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ Uses: SerpParser.extractProducts()                                  │
│                                                                     │
│ Data Extracted per Product:                                         │
│ • ASIN (product ID)                                                 │
│ • Title                                                             │
│ • Price (filtered: products with price <= 0 are SKIPPED)            │
│ • Rating & Reviews                                                  │
│ • Monthly Sales Badge (e.g., "50+ bought in past month")            │
│ • Is Sponsored (true/false)                                         │
│ • Position (rank on page)                                           │
│ • Image URL                                                         │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 3: Limit Products to Configured Amount                         │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ products = products.slice(0, search_page_products_limit)            │
│                                                                     │
│ Example: If 60 products found, limit to top 20                      │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 4: Enrich Products with BSR (Best Sellers Rank)                │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ Uses: SerpParser.enrichWithBSR()                                    │
│                                                                     │
│ For each product (in parallel batches):                             │
│ 1. Fetch product detail page: amazon.com/dp/{ASIN}                  │
│ 2. Extract BSR from product details table                           │
│ 3. Extract Category from BSR or breadcrumbs                         │
│ 4. Extract Brand from product details                               │
│                                                                     │
│ BSR Extraction Methods (tried in order):                            │
│ • Method 1: Product details table (#productDetails)                 │
│ • Method 2: Detail bullets (#detailBullets)                         │
│ • Method 3: Technical details table                                 │
│ • Method 4: Any text containing "Best Sellers Rank"                 │
│ • Method 5: Category from breadcrumbs (if no BSR found)             │
│                                                                     │
│ If NO BSR Found:                                                    │
│ • monthly_sales = 0 (treated as new product)                        │
│ • is_new_product = true                                             │
│ • Category extracted from breadcrumbs                               │
│                                                                     │
│ Batch Settings:                                                     │
│ • Parallel requests: search_page_bsr_parallel_requests              │
│ • Delay between batches: search_page_bsr_delay_ms                   │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 5: Send to Backend for Estimation                              │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ POST /api/search-volume/estimate                                    │
│                                                                     │
│ Request Body:                                                       │
│ {                                                                   │
│   keyword: "office chair",                                          │
│   marketplace: "amazon.com",                                        │
│   products: [...enriched products with BSR...]                      │
│ }                                                                   │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 6: Backend Calculates & Returns Results                        │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ (See Section 3: Search Volume Estimation for details)               │
│                                                                     │
│ Response Includes:                                                  │
│ • search_volume: { estimated, confidence_level, method }            │
│ • products: [...enriched with monthly_sales, revenue, fees...]      │
│ • product_stats: { avg_price, avg_bsr, total_sales, avg_reviews }   │
│ • difficulty: { score, level, factors }                             │
│ • demand_level: "high" | "medium" | "low"                           │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 7: Display Results in UI                                       │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ Shows dashboard with:                                               │
│ • Keyword metrics (volume, difficulty, demand)                      │
│ • Product table with sales, revenue, BSR, category, fees            │
│ • Products with no BSR shown at 50% opacity with "New" badge        │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 2. Reverse ASIN Keyword Discovery

### Overview
Finds keywords that a specific product (ASIN) ranks for on Amazon.

### Step-by-Step Process

```
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 1: Fetch Configuration from Backend                           │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ GET /api/settings                                                   │
│                                                                     │
│ Settings Used:                                                      │
│ • reverse_asin_products_limit (default: 10)                         │
│ • reverse_asin_bsr_parallel_requests (default: 3)                   │
│ • reverse_asin_bsr_delay_ms (default: 500)                          │
│ • reverse_asin_keywords_limit (default: 50)                         │
│ • reverse_asin_search_delay_ms (default: 1500)                      │
│ • reverse_asin_backend_batch_size (default: 5)                      │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ PHASE 1: Extract Product Context                                    │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ Uses: ProductParser.getKeywordCandidatesForReverseAsin()            │
│                                                                     │
│ Data Sources for Keyword Candidates:                                │
│ 1. Product Title (broken into n-grams)                              │
│ 2. Bullet Points / Features                                         │
│ 3. Product Description                                              │
│ 4. Product Carousel ("Customers who viewed this also viewed...")    │
│ 5. Amazon Search Suggestions (autocomplete)                         │
│                                                                     │
│ Output:                                                             │
│ • candidateKeywords: ["office chair", "ergonomic chair", ...]       │
│ • dominantWord: "chair" (most common word for filtering)            │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ PHASE 2: Filter & Deduplicate Keywords                              │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ Filtering Rules:                                                    │
│ • Minimum length: 3 characters                                      │
│ • Remove pure numbers (e.g., "123")                                 │
│ • Remove keywords with trailing decimals (e.g., "scale .05")        │
│ • Must contain dominant word (e.g., must contain "chair")           │
│                                                                     │
│ Limit Applied: reverse_asin_keywords_limit (default 50)             │
│ keywordsToTest = candidateKeywords.slice(0, 50)                     │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ PHASE 3: Reverse Check (Search for Each Keyword)                    │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ For each keyword (in parallel batches of 5):                        │
│                                                                     │
│ A. Search Amazon: amazon.com/s?k={keyword}                          │
│ B. Extract products using SerpParser.extractProducts()              │
│ C. Find target ASIN position in search results                      │
│ D. Enrich products with BSR (same as Search Page, but fewer):       │
│    • Products: reverse_asin_products_limit (default: 10)            │
│    • Parallel: reverse_asin_bsr_parallel_requests (default: 3)      │
│    • Delay: reverse_asin_bsr_delay_ms (default: 500)                │
│                                                                     │
│ Delay between keywords: reverse_asin_search_delay_ms (1500ms)       │
│ This prevents Amazon captcha/rate limiting                          │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ PHASE 4: Batch Send to Backend for Estimation                       │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ POST /api/search-volume/batch-estimate                              │
│                                                                     │
│ Keywords batched: reverse_asin_backend_batch_size (default: 5)      │
│                                                                     │
│ Request Body:                                                       │
│ {                                                                   │
│   marketplace: "amazon.com",                                        │
│   requests: [                                                       │
│     { keyword: "office chair", products: [...] },                   │
│     { keyword: "ergonomic chair", products: [...] },                │
│     ...                                                             │
│   ]                                                                 │
│ }                                                                   │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ PHASE 5: Display Results Table                                      │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│ Table Columns:                                                      │
│ • Keyword                                                           │
│ • Position (your ASIN's rank for this keyword)                      │
│ • Est. Volume (monthly search volume)                               │
│ • Difficulty (0-100 score + level)                                  │
│ • Total Sales (sum of top products' monthly sales)                  │
│ • Sponsored (count of sponsored products)                           │
│ • Avg Price (marketplace-specific currency)                         │
│ • Avg BSR                                                           │
│                                                                     │
│ Currency: Detected from current Amazon domain                       │
│ (amazon.eg → EGP, amazon.com → $, amazon.co.uk → £, etc.)           │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 3. Search Volume Estimation

### Overview
Estimates monthly search volume for a keyword based on product sales data.

### Calculation Formula

```
┌─────────────────────────────────────────────────────────────────────┐
│ SEARCH VOLUME ESTIMATION ALGORITHM                                  │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ PRIMARY FORMULA:                                                    │
│ ─────────────────                                                   │
│ search_volume = total_monthly_sales × (1 / conversion_rate)         │
│                                                                     │
│ WHERE:                                                              │
│ • total_monthly_sales = SUM of all products' monthly_sales          │
│ • conversion_rate = 0.10 (10% - typical Amazon conversion rate)     │
│                                                                     │
│ EXAMPLE:                                                            │
│ If top 20 products = 5,000 total monthly sales                      │
│ search_volume = 5,000 × (1 / 0.10) = 50,000 monthly searches        │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ DATA SOURCES (in priority order)                                    │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ 1. MONTHLY SALES BADGE (Highest Priority)                           │
│    Source: Amazon's "50+ bought in past month" badge                │
│    Values: 50, 100, 200, 500, 1000, 2000, 5000, 10000               │
│    Confidence: HIGH (direct Amazon data)                            │
│                                                                     │
│ 2. BSR-BASED ESTIMATION (If no sales badge)                         │
│    Source: Best Sellers Rank from product page                      │
│    Formula: monthly_sales = estimateSalesFromBSR(marketplace, bsr)  │
│    Confidence: MEDIUM (estimated from BSR curve)                    │
│                                                                     │
│ 3. CACHE LOOKUP (Fallback)                                          │
│    Source: product_cache table (previously scraped data)            │
│    Confidence: LOW-MEDIUM (may be outdated)                         │
│                                                                     │
│ 4. HISTORICAL ANALYSIS (Last resort)                                │
│    Source: search_analyses and keyword_cache tables                 │
│    Confidence: VERY LOW                                             │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ BSR TO SALES ESTIMATION                                             │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ Formula: monthly_sales = (market_constant / bsr) ^ power_factor     │
│                                                                     │
│ AMAZON.COM (US) Constants:                                          │
│ ┌─────────────────────────────────────────────────────────────────┐ │
│ │ Category            │ Market Constant │ Power Factor │ Max BSR │ │
│ ├─────────────────────┼─────────────────┼──────────────┼─────────┤ │
│ │ Home & Kitchen      │ 450,000         │ 0.85         │ 500,000 │ │
│ │ Kitchen & Dining    │ 350,000         │ 0.85         │ 400,000 │ │
│ │ Electronics         │ 300,000         │ 0.82         │ 350,000 │ │
│ │ Toys & Games        │ 200,000         │ 0.88         │ 300,000 │ │
│ │ Sports & Outdoors   │ 250,000         │ 0.84         │ 350,000 │ │
│ │ Default             │ 300,000         │ 0.85         │ 400,000 │ │
│ └─────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│ AMAZON.EG (Egypt) Constants:                                        │
│ ┌─────────────────────────────────────────────────────────────────┐ │
│ │ Category            │ Market Constant │ Power Factor │ Max BSR │ │
│ ├─────────────────────┼─────────────────┼──────────────┼─────────┤ │
│ │ Home & Kitchen      │ 50,000          │ 0.80         │ 100,000 │ │
│ │ Electronics         │ 40,000          │ 0.78         │ 80,000  │ │
│ │ Default             │ 35,000          │ 0.80         │ 75,000  │ │
│ └─────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ CONFIDENCE LEVELS                                                   │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ • HIGH: 70%+ products have sales badge data                         │
│ • MEDIUM: 30-70% products have data (mix of badge + BSR)            │
│ • LOW: <30% products have data OR using fallback methods            │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 4. Monthly Sales Estimation

### Per-Product Sales Calculation

```
┌─────────────────────────────────────────────────────────────────────┐
│ MONTHLY SALES PER PRODUCT                                           │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ PRIORITY 1: Use Monthly Sales Badge                                 │
│ ────────────────────────────────────                                │
│ If product has "X+ bought in past month" badge:                     │
│   monthly_sales = badge_value (50, 100, 200, 500, 1000, etc.)       │
│   is_sales_estimated = false                                        │
│                                                                     │
│ PRIORITY 2: Estimate from BSR                                       │
│ ─────────────────────────────                                       │
│ If no badge but has BSR:                                            │
│   monthly_sales = estimateSalesFromBSR(marketplace, bsr, category)  │
│   is_sales_estimated = true                                         │
│                                                                     │
│ PRIORITY 3: New Product (No BSR)                                    │
│ ─────────────────────────────────                                   │
│ If no badge AND no BSR:                                             │
│   monthly_sales = 0                                                 │
│   is_new_product = true                                             │
│   category = extracted from breadcrumbs                             │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ REVENUE CALCULATION                                                 │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ revenue = price × monthly_sales                                     │
│                                                                     │
│ Example:                                                            │
│ price = $29.99, monthly_sales = 500                                 │
│ revenue = $29.99 × 500 = $14,995                                    │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│ FBA FEE ESTIMATION                                                  │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ estimated_fees = referral_fee + fulfillment_fee                     │
│                                                                     │
│ WHERE:                                                              │
│ • referral_fee = price × 0.15 (15% Amazon referral)                 │
│ • fulfillment_fee = $3.50 (US) or EGP 25 (Egypt)                    │
│                                                                     │
│ Example (US):                                                       │
│ price = $29.99                                                      │
│ referral_fee = $29.99 × 0.15 = $4.50                                │
│ fulfillment_fee = $3.50                                             │
│ estimated_fees = $4.50 + $3.50 = $8.00                              │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 5. Difficulty Score Calculation

### Overview
Calculates how difficult it would be to rank for a keyword (0-100 scale).

```
┌─────────────────────────────────────────────────────────────────────┐
│ DIFFICULTY SCORE FACTORS                                            │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ FACTOR 1: Competition (25% weight)                                  │
│ ─────────────────────────────────                                   │
│ Based on: Number of products with high reviews                      │
│ High reviews = 500+ reviews                                         │
│ Score: (high_review_count / total_products) × 100                   │
│                                                                     │
│ FACTOR 2: Review Barrier (25% weight)                               │
│ ─────────────────────────────────────                               │
│ Based on: Average review count of top products                      │
│ Score: min(avg_reviews / 50, 100)                                   │
│                                                                     │
│ FACTOR 3: Brand Dominance (20% weight)                              │
│ ─────────────────────────────────────                               │
│ Based on: Market share of top brands                                │
│ Score: (top_brand_share × 100)                                      │
│                                                                     │
│ FACTOR 4: Sponsored Saturation (15% weight)                         │
│ ────────────────────────────────────────────                        │
│ Based on: Percentage of sponsored listings                          │
│ Score: (sponsored_count / total_products) × 100                     │
│                                                                     │
│ FACTOR 5: Price Competition (15% weight)                            │
│ ────────────────────────────────────────                            │
│ Based on: Price variance in market                                  │
│ Score: Based on coefficient of variation                            │
│                                                                     │
│ ─────────────────────────────────────────────────────────────────── │
│                                                                     │
│ FINAL SCORE = weighted_average(all_factors)                         │
│                                                                     │
│ DIFFICULTY LEVELS:                                                  │
│ • 0-25: Easy (green) - Good opportunity                             │
│ • 26-50: Medium (yellow) - Moderate competition                     │
│ • 51-75: Hard (orange) - Significant competition                    │
│ • 76-100: Very Hard (red) - Highly competitive                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 6. Admin-Configurable Settings

### Database Table: `app_settings`

All settings are stored in the backend database and fetched via `/api/settings`.

```
┌─────────────────────────────────────────────────────────────────────┐
│ SEARCH PAGE SETTINGS                                                │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ search_page_products_limit (default: 20)                            │
│ └─ Max products to fetch BSR for when analyzing search page         │
│    Higher = more accurate, slower                                   │
│                                                                     │
│ search_page_bsr_parallel_requests (default: 5)                      │
│ └─ Parallel product page fetches for BSR                            │
│    Lower = safer from rate limits                                   │
│                                                                     │
│ search_page_bsr_delay_ms (default: 300)                             │
│ └─ Delay between BSR fetch batches (milliseconds)                   │
│    Higher = slower but safer                                        │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│ REVERSE ASIN SETTINGS                                               │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ reverse_asin_products_limit (default: 10)                           │
│ └─ Products to fetch BSR for PER KEYWORD                            │
│    Lower = faster (processes many keywords)                         │
│                                                                     │
│ reverse_asin_bsr_parallel_requests (default: 3)                     │
│ └─ Parallel product page fetches per keyword                        │
│    Keep low to avoid rate limits                                    │
│                                                                     │
│ reverse_asin_bsr_delay_ms (default: 500)                            │
│ └─ Delay between BSR fetch batches per keyword                      │
│                                                                     │
│ reverse_asin_keywords_limit (default: 50)                           │
│ └─ Maximum keywords to process per session                          │
│                                                                     │
│ reverse_asin_search_delay_ms (default: 1500)                        │
│ └─ Delay between Amazon searches to avoid captcha                   │
│                                                                     │
│ reverse_asin_backend_batch_size (default: 5)                        │
│ └─ Keywords per backend API call for estimation                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 7. Currency & Marketplace Detection

### Automatic Currency Symbol

```
┌─────────────────────────────────────────────────────────────────────┐
│ MARKETPLACE → CURRENCY MAPPING                                      │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ Domain              │ Currency Symbol                               │
│ ────────────────────┼─────────────────                              │
│ amazon.eg           │ EGP                                           │
│ amazon.com          │ $                                             │
│ amazon.co.uk        │ £                                             │
│ amazon.de           │ €                                             │
│ amazon.fr           │ €                                             │
│ amazon.it           │ €                                             │
│ amazon.es           │ €                                             │
│ amazon.ae           │ AED                                           │
│ amazon.sa           │ SAR                                           │
│ amazon.in           │ ₹                                             │
│ amazon.jp           │ ¥                                             │
│ amazon.ca           │ C$                                            │
│ amazon.com.mx       │ MX$                                           │
│ amazon.com.br       │ R$                                            │
│                                                                     │
│ Detection: Based on window.location.hostname                        │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 8. Data Filtering Rules

### Products Filtered OUT of Analysis

```
┌─────────────────────────────────────────────────────────────────────┐
│ PRODUCT FILTERING RULES                                             │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ 1. NO PRICE (Unavailable Products)                                  │
│    └─ Products with price = 0, null, or undefined are SKIPPED       │
│       (Currently unavailable, out of stock)                         │
│       Applied in: SerpParser.extractProducts()                      │
│       Applied in: ReverseAsin.fallbackExtractProducts()             │
│                                                                     │
│ 2. NO ASIN                                                          │
│    └─ Products without a valid ASIN are SKIPPED                     │
│                                                                     │
│ NOTE: Products WITHOUT BSR are NOT filtered                         │
│ ────────────────────────────────────────────                        │
│ • They are treated as new products with 0 sales                     │
│ • Category is extracted from breadcrumbs                            │
│ • Displayed with 50% opacity and "New" badge                        │
└─────────────────────────────────────────────────────────────────────┘
```

### Display Rules for Missing Data

```
┌─────────────────────────────────────────────────────────────────────┐
│ DISPLAY VALUES FOR MISSING DATA                                     │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                     │
│ Data Field        │ Missing Value Display                           │
│ ──────────────────┼────────────────────                             │
│ Monthly Sales     │ "0" (gray color) + "New" badge if new product   │
│ Revenue           │ "0" (gray color)                                │
│ Reviews           │ "0"                                             │
│ Rating            │ "—" (dash)                                      │
│ BSR               │ "—" (dash)                                      │
│ Category          │ "—" (dash)                                      │
│ Price             │ Product is FILTERED OUT (not shown)             │
│                                                                     │
│ NEW PRODUCT INDICATOR:                                              │
│ • Row displayed at 50% opacity                                      │
│ • "New" badge shown under sales                                     │
│ • Tooltip: "No sales data available (new product?)"                 │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Summary: Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                    COMPLETE DATA FLOW                               │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────┐     ┌──────────────────┐     ┌──────────────────┐
│   Amazon SERP    │────▶│   SerpParser     │────▶│  enrichWithBSR   │
│   (Search Page)  │     │ extractProducts  │     │ (Fetch product   │
│                  │     │                  │     │  pages for BSR)  │
└──────────────────┘     └──────────────────┘     └────────┬─────────┘
                                                           │
                                                           ▼
┌──────────────────┐     ┌──────────────────┐     ┌──────────────────┐
│    UI Display    │◀────│   Backend API    │◀────│  Enriched Data   │
│  (Shadow Panel)  │     │   /estimate      │     │ (products + BSR) │
│                  │     │   /batch-estimate│     │                  │
└──────────────────┘     └──────────────────┘     └──────────────────┘
         │                        │
         │                        ▼
         │               ┌──────────────────┐
         │               │  product_cache   │
         │               │ (Store fresh BSR │
         │               │  for analytics)  │
         │               └──────────────────┘
         │
         ▼
┌──────────────────────────────────────────────────────────────────────┐
│ DISPLAYED METRICS:                                                   │
│ • Search Volume = total_sales × 10 (assuming 10% conversion)         │
│ • Difficulty Score = weighted factors (reviews, brands, sponsored)   │
│ • Product Sales = badge value OR estimated from BSR OR 0 (new)       │
│ • Revenue = price × monthly_sales                                    │
│ • Fees = (price × 15%) + fulfillment_fee                             │
└──────────────────────────────────────────────────────────────────────┘
```

---

*Document generated: January 2026*
*For technical implementation details, see the source code in `/chrome-extension/src/` and `/backend-laravel8/app/`*
