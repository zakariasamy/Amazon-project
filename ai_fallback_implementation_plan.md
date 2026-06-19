# AI LLM Fallback Chain & Feature Plan

This plan details the integration of a robust LLM AI provider system into the Amazon Product Analyzer application. It introduces a configurable fallback chain (Gemini, OpenRouter, NVIDIA NIM, OpenAI, Groq) to prioritize free tiers, a comprehensive admin settings interface to manage, order, and test providers, and specific AI-driven keyword suggestion features across the seller tools.

All AI features proposed are **globally enabled/disabled by the Admin** in the backend settings; the extension and dashboard users will experience these features automatically with no frontend choice toggles. **If disabled by the admin, each tool falls back entirely to its existing programmatic logic and works exactly as it does today.**

---

## User Review Required

Please review the revised plan detailing prompt constraints, admin-only controls, and cost-reduction mechanisms:

> [!IMPORTANT]
> **Admin System Prompt & Parameter Controls**: The admin dashboard settings page will feature:
> - **System Prompt Editor**: Textarea to refine the instructions passed to the LLMs.
> - **Returned Keyword Limit**: Admin can specify the maximum/target number of keywords returned by the AI (e.g., `15` or `20`).
> - **Granular Context Content Controls**: Every single input sent to the AI has a corresponding checkbox toggle in the settings panel. If unchecked, that specific variable is completely omitted from the generated prompt.
> - **Independent Provider Connection Testing**: The admin settings panel allows the administrator to trigger a real-time connection check against **each individual LLM provider** separately. The test runs a mock completion request using the current system prompt template, displaying either the successfully generated keywords or the raw API error response.
> - **Centralized API Credentials & Reuse**: API keys and base URLs are stored once at the provider/website level (e.g., OpenRouter, Gemini, NVIDIA NIM, OpenAI, Groq). When adding, selecting, or ordering models in the fallback list, the admin simply associates each model with its parent provider website. They **do not** need to re-enter or duplicate the API token/credentials for multiple models belonging to the same website/provider.
>
> **Last Fallback Safeguard (Normal Behavior without AI)**:
> - If all enabled LLM providers in the fallback chain fail (due to API key errors, service outages, rate limits, or network timeouts), the request catches the exception silently, logs the details to `storage/logs/ai.log`, and **falls back automatically to the standard, non-AI programmatic keyword extraction logic**. The tool continues working smoothly using the normal behavior.

> [!TIP]
> **Cost Reduction (Minimize Input & Output Tokens)**:
> - **Input Compression**: To keep input token costs minimal, the backend will compress raw data before sending it:
>   - Truncate competitor titles to a maximum of 60 characters (keeping only the main keyword descriptors).
>   - Limit competitor lists in prompts to the top 3-5 results instead of all 10.
>   - De-duplicate product overview attributes and exclude generic words.
> - **Output Compression (Comma-Separated Keywords)**: The default system prompt instructs the LLM to return **only** the keywords separated by commas (`,`), with no intro/outro text, markdown, or headers. This drastically lowers output token costs and makes parsing simple.

---

## Analysis of Scraper & Database Gaps (Missing Inputs)

During code analysis of the active extension tools and backend database schemas, the following crucial gaps (missing possible inputs for AI) were discovered and will be addressed in this implementation:

### 1. Cerebro Scraper Gaps
* **The Issue**: `cerebro-analyzer.js` currently only extracts `{ asin, title, price, rating, reviews, image }` for competitor listings. It does **not** scrape Brand Name, Categories, or Overview Attributes (Color, Material, etc.). Therefore, even if the admin toggles these inputs, they are currently missing from the extension payload.
* **The Solution**: We will modify `getProductInfoFromProductPage(asin)` and `getProductInfoFromSearchPage(asin)` in `cerebro-analyzer.js` to extract and populate `brand`, `category`, and `attributes` (scraped from `#productOverview_feature_div` or page tables) so they are available in the payload sent to the backend.

### 2. Keyword Magnet Scraper Gaps
* **The Issue**: In `magnet-analyzer.js`, the background page fetcher `fetchProductBSR(asin)` (located in `serp-parser.js`) only parses `bsr`, `category`, `brand`, and `seller_count`. It does **not** extract overview attributes (Color, Material, etc.).
* **The Solution**: We will enhance `fetchProductBSR(asin)` in `serp-parser.js` to parse `#productOverview_feature_div` elements for overview attributes and store them in the results array so they can be sent as context to the AI suggest endpoint.

### 3. Reverse ASIN Database Gaps
* **The Issue**: When launching Reverse ASIN suggestions from the saved lists dashboard, the backend queries the `product_cache` table. However, `product_cache` does **not** store Brand, Weight, Dimensions, Bullet Points, Description, or Overview Attributes. The list item data payload also lacks these.
* **The Solution**: 
  1. We will update the save list handler (`save-to-list.js`) to scrape and include `brand`, `weight`, `dimensions`, `bullets`, `description`, and `attributes` in the list item JSON payload when saving.
  2. For a bulletproof fallback, we will update the backend `suggestKeywords` controller/service to automatically fetch the Amazon product page in the background (using a Guzzle cURL wrapper) on a cache miss, scrape the missing fields, and cache/return them.

### 4. Marketplace Language/Locale Gap
* **The Issue**: To prevent the AI from generating English keywords for Arabic search pages (e.g., in Egypt or Saudi Arabia) or vice-versa, the LLM prompt must have context on the target marketplace.
* **The Solution**: The scraper will always pass `marketplace` (e.g., `amazon.eg`) and `language` (e.g., `Arabic` or `English`) to the backend, which will append them as instructions to the system prompt (e.g., "Language: Arabic (Egyptian dialect)").

---

## Open Questions

> [!WARNING]
> Please confirm if there are any specific local Egyptian dialect terms or formatting styles the AI should prioritize for the Arabic marketplace queries.

---

## Tool-by-Tool AI Feature Explanation & Inputs

The table below explains in plain English what each AI feature does, how it works, what exact inputs are sent to the AI, and shows a clear **Before AI** vs. **After AI** example of the improvement.

> [!IMPORTANT]
> **AI Role vs. Programmatic Metrics**: The AI is **strictly** used to suggest logical, natural keyword/search terms (phrases). The AI does **NOT** estimate search volume, difficulty, click share, or sales metrics. Once the AI lists the candidate keywords, the application computes all volumes, difficulty scores, and prices programmatically using the existing database cache and scraper algorithms.

| Feature | How It Works | Context Inputs Sent to AI (Each Individually Toggled by Admin) | Before AI (Programmatic) | After AI (LLM Enabled) |
| :--- | :--- | :--- | :--- | :--- |
| **1. Competitor Keyword Analyzer (Cerebro)** | Admin-controlled (`ai_cerebro_enabled`). Analyzes up to 10 competitors at once. Launched on search results pages or dashboard ASIN comparator. | • Product Titles of the compared ASINs<br>• Product Categories & Category Path<br>• Rating/Review Count metrics<br>• Competitor Brand Names<br>• Competitor Product Overview Attributes (Color, Material, etc.) | Splits titles and concatenates random words.<br>*Example output:* **"chair office ergonomic high wheels black nylon"** (Jumbled/unnatural terms) | Synthesizes competitor titles, categories, and attributes of top competitors to output natural phrases.<br>*Example output:* **"mesh ergonomic office chair"**, **"adjustable computer desk chair"**<br>*(Ranks, volume, and sales are computed programmatically)* |
| **2. Keyword Magnet** | Admin-controlled (`ai_keyword_magnet_enabled`). Launched on search results pages based on a seed keyword. | • Seed Keyword (entered by user)<br>• Product Titles (scraped from search result listings)<br>• Brand Names of competitor products<br>• Active Search Query (from the URL / search box)<br>• **Background-scraped overview attributes** (e.g., Color, Material) extracted by fetching top ASIN pages in the background<br>• Competitor Product Categories | Appends raw specs to the seed keyword blindly.<br>*Example output:* **"portal scale body weight glass pink battery"** (Nonsensical, zero search volume) | Evaluates seed keyword, listing titles, and attributes to output natural search phrases.<br>*Example output:* **"digital weight scale for body"**, **"precision food scale"**<br>*(Search volumes, prices, and metrics are computed programmatically)* |
| **3. Reverse ASIN Suggestions** | Admin-controlled (`ai_reverse_asin_enabled`). Accessed via a new **🔑 AI Keywords** button next to saved products in the dashboard list show page. | • Product Title (capped at 60 chars)<br>• Product Category & Category Path<br>• Brand Name of the product<br>• Dimensions & Weight metrics (indicates size tier e.g. mini, heavy duty)<br>• **Product Bullet Points** (scraped directly by extension DOM or fetched by backend)<br>• **Product Description** (truncated to first 200 chars)<br>• **Overview Attributes** (e.g. Color, Material) | Programmatically extracts word combinations from a single title.<br>*Example output:* **"scale kitchen digital 5kg"** | Queries the LLM with the product details to generate highly relevant search terms.<br>*Example output:* **"baking scale with bowl"**, **"gram food scale"**<br>*(Metrics are computed programmatically)* |

---

## Default System Prompt Specification

To keep LLM outputs highly deterministic, short, and cheap, the default System Prompt loaded in the admin settings is:

```text
You are an expert Amazon SEO and keyword research tool.
Generate up to {limit} logical, natural search terms that customers would type to find these products.
Constraint: You MUST output ONLY the keywords, separated by a comma. Do not include introductory text, explanations, markdown, or line numbers.
Example output format: keyword one, keyword two, keyword three
```

---

## Proposed Changes

### 1. Database Layer (Backend)

#### [NEW] [create_ai_providers_table.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/database/migrations/2026_06_17_000001_create_ai_providers_table.php)
- Creates the `ai_providers` table to store centralized credentials/settings per AI platform (website).
- **Columns**: `id`, `provider_key` (unique key e.g. `gemini`, `openrouter`, `nvidia`, `openai`, `groq`), `label` (e.g. `OpenRouter`), `api_key` (stored once for all models of this provider), `api_base_url`, `is_active` (boolean), `timestamps`.

#### [NEW] [create_ai_fallback_models_table.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/database/migrations/2026_06_17_000002_create_ai_fallback_models_table.php)
- Creates the `ai_fallback_models` table to manage the configurable execution order in the fallback chain.
- **Columns**: `id`, `provider_id` (foreign key pointing to `ai_providers`), `model_name` (e.g. `meta-llama/llama-3-70b-instruct`), `label` (user-friendly name), `is_enabled` (boolean), `sort_order` (integer), `timestamps`.

#### [NEW] [modify_cerebro_keywords_match_type_table.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/database/migrations/2026_06_17_000003_modify_cerebro_keywords_match_type_table.php)
- Modifies the `match_type` column in the `cerebro_keywords` table from an enum to a string (`varchar(50)`), matching the structure of `magnet_keywords`.
- This enables storing `"ai:<model_name>"` (e.g., `"ai:gemini-1.5-flash"`) directly inside the existing `match_type` column for both Magnet and Cerebro, indicating the keyword source and model without adding any new columns or tables.

#### [NEW] [create_ai_suggestions_cache_table.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/database/migrations/2026_06_17_000004_create_ai_suggestions_cache_table.php)
- Creates the `ai_suggestions_cache` table to permanently cache raw suggested keywords inside the database.
- **Columns**: `id`, `type` (string, either `'asin'` or `'keyword'`), `query_key` (ASIN string or seed keyword), `marketplace` (string, 30), `suggestions` (JSON array of keyword phrases with model metadata), `created_at`, `updated_at`.
- **Index**: Unique index on `['type', 'query_key', 'marketplace']`.
- **Cross-Tool Reuse Benefit**: Storing cache by general type (`'asin'` or `'keyword'`) allows different tools to share the same cached suggestions. For instance, suggestions cached for a competitor ASIN during Reverse ASIN analysis are immediately available if Cerebro later queries suggestions for that competitor ASIN, maximizing efficiency and saving LLM costs.

#### [NEW] [AiProvider.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/app/Models/AiProvider.php)
- Eloquent model representing the centralized provider credentials.

#### [NEW] [AiFallbackModel.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/app/Models/AiFallbackModel.php)
- Eloquent model representing models in the fallback chain. Contains helper methods to load the active chain ordered.

#### [NEW] [AiSuggestionsCache.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/app/Models/AiSuggestionsCache.php)
- Eloquent model representing cached AI suggestions in the database. Provides convenience methods to retrieve and write cached suggestions permanently.

---

### 2. Backend Services & Controllers (Backend)

#### [NEW] [AiService.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/app/Services/AiService.php)
- Coordinates provider requests.
- **Key Methods**:
  - `generateKeywords(string $seed, array $titles, array $attributes): array`
  - `testProvider(int $providerId): array`
- Truncates inputs (titles capped at 60 chars, descriptions capped at 200 chars, max 3 competitor listings) to ensure minimal input costs.
- Passes the `{limit}` variable to the system prompt based on admin configuration limits.
- Parses the comma-separated output string into a clean PHP array by exploding on commas: `explode(',', $llmOutput)`.
- Handles exceptions silently across the fallback array, logging errors to `storage/logs/ai.log`.

#### [NEW] [AiController.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/app/Http/Controllers/Api/AiController.php)
- Authenticated endpoints:
  - `POST /api/ai/suggest-keywords`
r
#### [NEW] [AdminAiController.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/app/Http/Controllers/AdminAiController.php)
- Web dashboard controller for:
  - `updateProviders` (saving keys, models, toggling prompt setting checkboxes).
  - `reorderProviders` (updates sorts via sorting inputs/drag).
  - `testProvider` (runs AJAX connection check).

#### [MODIFY] [ReverseAsinController.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/app/Http/Controllers/Api/ReverseAsinController.php)
- Update `suggestKeywords` to invoke the `AiService` when settings indicate AI suggestions are enabled.
- Implement hybrid database-driven caching in `suggestKeywords`: check the `ai_suggestions_cache` table for a record matching `type = 'asin'`, `query_key = $asin`, and `marketplace = $marketplace`.
  - **Recent Cache (Under 24 Hours)**: If the cache record is less than 24 hours old, return the cached keywords *along with their stored stats* immediately.
  - **Old Cache (Over 24 Hours)**: If the cache record is older than 24 hours, extract the cached keyword phrases, but re-evaluate/refresh their search volume, sales, and rank statistics using the latest database lookups (`analyzeKeywords`) before returning them.
  - **No Cache**: If no cache record exists, invoke `AiService` to generate suggestions, evaluate fresh stats, cache them inside the `ai_suggestions_cache` table, and return the array.
- The returned suggestions will explicitly include `"is_ai"` and `"ai_model"` attributes.
- Update `saveResults` to accept and store these AI metadata attributes (`"is_ai"`, `"ai_model"`) directly inside the `keywords_data` JSON.
- Implement background-scraping logic (using a fallback HTTP Guzzle helper) to fetch product detail pages from Amazon on cache miss, extracting brand, bullet points, description, overview attributes, dimensions, and weight.

#### [MODIFY] [MagnetController.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/app/Http/Controllers/Api/MagnetController.php)
- Implement hybrid database-backed caching for Keyword Magnet suggestions: before initiating an LLM call for a seed keyword, query `ai_suggestions_cache` for `type = 'keyword'`, `query_key = $seed_keyword`, and `marketplace = $marketplace`.
  - **Recent Cache (Under 24 Hours)**: If the cache record is less than 24 hours old, return the keywords *and their cached statistics* immediately.
  - **Old Cache (Over 24 Hours)**: If the cache record is older than 24 hours, retrieve only the cached keyword phrases, but re-evaluate/scrape their statistics fresh in real-time.
  - **No Cache / Cache Miss**: If no cache record exists, query the LLMs, evaluate stats, store the results inside the `ai_suggestions_cache` table, and proceed.
- Update `store` validation to accept `"ai:<model_name>"` format in the `match_type` column for keywords.
- Save these values directly into the existing `match_type` column of the `magnet_keywords` table.

#### [MODIFY] [CerebroController.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/app/Http/Controllers/Api/CerebroController.php)
- Update `store` validation to accept `"ai:<model_name>"` format in the `match_type` column for keywords (now modified to string format).
- Save these values directly into the modified `match_type` column of the `cerebro_keywords` table.

#### [MODIFY] [AdminSettingsController.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/app/Http/Controllers/AdminSettingsController.php)
- Merge prompt configuration keys (`ai_prompt_include_titles`, `ai_system_prompt_template`, `ai_returned_keywords_limit`, etc.) and toggles into `$this->defaults()`.

#### [MODIFY] [api.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/routes/api.php)
- Register endpoints under Sanctum authentication middleware.

#### [MODIFY] [web.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/routes/web.php)
- Register admin routes for provider updates and testing.

---

### 3. Frontend Admin Settings & Dashboard Views (Backend)

#### [MODIFY] [settings.blade.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/resources/views/admin/settings.blade.php)
- Add the `🤖 AI LLM Fallback Plan` tab.
- **Tab Layout**:
  - **Prompt Configuration Card**:
    - **System Prompt Editor**: Textarea field to display and edit the System Prompt template.
    - **Returned Keywords Limit Input**: Number field (`ai_returned_keywords_limit`) controlling maximum returned keyword phrases.
    - **Global Tool AI Enablement Toggles**: Main checkboxes to enable/disable AI logic per tool:
      - **Cerebro AI**: `ai_cerebro_enabled` (if off, falls back entirely to existing programmatic results).
      - **Keyword Magnet AI**: `ai_keyword_magnet_enabled` (if off, falls back entirely to existing autocomplete/related query parsing).
      - **Reverse ASIN AI**: `ai_reverse_asin_enabled` (if off, falls back entirely to local single-title programmatic candidate parsing).
    - **Context Content Controls**: Granular checkboxes separated per tool:
      - **Cerebro Toggles**: `cerebro_prompt_include_titles`, `cerebro_prompt_include_categories`, `cerebro_prompt_include_reviews`, `cerebro_prompt_include_attributes`, `cerebro_prompt_include_brand`.
      - **Keyword Magnet Toggles**: `magnet_prompt_include_seed`, `magnet_prompt_include_competitor_titles`, `magnet_prompt_include_scraped_attributes`, `magnet_prompt_include_categories`, `magnet_prompt_include_brand`, `magnet_prompt_include_query`.
      - **Reverse ASIN Toggles**: `reverse_asin_prompt_include_title`, `reverse_asin_prompt_include_category`, `reverse_asin_prompt_include_bullets`, `reverse_asin_prompt_include_description`, `reverse_asin_prompt_include_attributes`, `reverse_asin_prompt_include_brand`, `reverse_asin_prompt_include_dimensions`.
  - **AI Provider Centralized Settings & Fallback Chain**: 
    - **Provider Cards/Forms**: Fields for Centralized API Key, Base URL, and Toggle Active per website/provider (Gemini, OpenRouter, Groq, OpenAI, NVIDIA NIM).
    - **Reorderable Fallback Models List**: A list where the admin can add specific models (e.g. `gemini-1.5-flash`, `meta-llama/llama-3-70b-instruct`) and arrange their fallback execution order. Each model links to a parent provider website.
    - **No Key Duplication**: Selecting or reordering multiple models of the same provider website automatically shares the parent provider's single API token.
    - **Test Connection Button**: Triggers AJAX connection checks for a model, utilizing the parent provider's API key and base URL, and displaying success/failure details immediately.

#### [MODIFY] [show.blade.php](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/backend-laravel8/resources/views/lists/show.blade.php)
- Under `products` list type rows, add an **🔑 AI Keywords** button next to each product.
- Clicking the button opens a modal running an AJAX request to `/api/reverse-asin/{asin}/suggest` and rendering the list of logical keywords returned from the LLM.

---

### 4. Chrome Extension Integration

#### [MODIFY] [serp-parser.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/engine/serp-parser.js)
- Update `fetchProductBSR(asin)` to extract and return competitor product overview attributes (e.g. Color, Material, style, etc.) from `#productOverview_feature_div` or page detail tables.

#### [MODIFY] [save-to-list.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/ui/save-to-list.js)
- Enhance the list save payload in the extension to scrape and package `brand`, `weight`, `dimensions`, `bullets`, `description`, and `attributes` in the list item JSON data when saving products, allowing the backend to immediately utilize these context attributes.

#### [MODIFY] [magnet-analyzer.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/api/magnet-analyzer.js)
- Check backend config parameters (`ai_keyword_suggestions_enabled`).
- If enabled, query backend `POST /api/ai/suggest-keywords` automatically with scraped details during seed keyword analysis.
- Merge the resulting keywords into the table with a match type of `AI`.

#### [MODIFY] [magnet-ui.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/ui/magnet-ui.js)
- Display progress bar texts like `"Executing AI search models..."` automatically if AI is active.
- Render the `AI` match type chip in the result tables.

#### [MODIFY] [cerebro-analyzer.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/api/cerebro-analyzer.js)
- Update competitor scrapers `getProductInfoFromProductPage(asin)` and `getProductInfoFromSearchPage(asin)` to extract Brand name, category/breadcrumbs path, and Overview Attributes (Color, Material, etc.).
- Check backend configuration settings, and if Cerebro AI keyword suggestions are enabled, call the backend AI suggest-keywords endpoint to fetch LLM-suggested candidate keywords before running the programmatic search checks.

#### [MODIFY] [cerebro-ui.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/ui/cerebro-ui.js)
- Render progress messages detailing AI keyword suggest execution.
- Add support for the `AI` match type identifier in search result grids.

---

## Verification Plan

### Automated Tests
- Database migration validation:
  `php artisan migrate`
- Mock request test suites for fallback logic testing:
  `php artisan test --filter=AiServiceTest`

### Manual Verification
1. **Prompt settings tests**:
   - Turn off "Include attributes" in settings -> run suggestions -> verify in request payload or query prompts that attributes are excluded.
2. **Provider connection checks**:
   - Save invalid credentials -> Click "Test Connection" -> check error output.
   - Save valid credentials -> Click "Test Connection" -> check success validation.
3. **Execution checks**:
   - Confirm in logs that fallback loops work correctly (if a model fails, the system executes the next ordered model).
