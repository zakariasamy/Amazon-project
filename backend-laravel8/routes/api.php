<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConstantsController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\FeesController;
use App\Http\Controllers\Api\SeasonalityController;
use App\Http\Controllers\Api\KeywordsController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ReverseAsinController;
use App\Http\Controllers\Api\CalibrationController;
use App\Http\Controllers\Api\ProductAnalysisController;
use App\Http\Controllers\Api\ProductCacheController;
use App\Http\Controllers\Api\DashboardApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// =================== Public Routes (No Auth Required) ===================

// Authentication
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// ─── Public Read-Only / Configuration Endpoints ──────────────────────────────
Route::post('/products/cache/batch', [ProductCacheController::class, 'batch']);

// Constants & Configuration
Route::get('/constants',               [ConstantsController::class, 'index']);
Route::get('/settings',                [ConstantsController::class, 'settings']);
Route::get('/constants/version',       [ConstantsController::class, 'version']);
Route::get('/constants/{marketplace}', [ConstantsController::class, 'byMarketplace']);

// Fees read-only
Route::get('/fees/{marketplace}', [FeesController::class, 'byMarketplace']);

// Seasonality & Keywords read-only
Route::get('/seasonality',                          [SeasonalityController::class, 'index']);
Route::get('/keywords/popular/{marketplace}',        [KeywordsController::class, 'popular']);

// Reverse ASIN read-only (GET)
Route::get('/reverse-asin/{asin}/keywords', [ReverseAsinController::class, 'getKeywords']);
Route::get('/reverse-asin/{asin}/suggest',  [ReverseAsinController::class, 'suggestKeywords']);
Route::get('/reverse-asin/{asin}/history',  [ReverseAsinController::class, 'getHistory']);
// submitRanking is write but doesn't consume a tool limit (just logs rankings)
Route::post('/reverse-asin/ranking', [ReverseAsinController::class, 'submitRanking']);

// Cerebro read-only
use App\Http\Controllers\Api\CerebroController;
Route::get('/cerebro/history',    [CerebroController::class, 'history']);
Route::get('/cerebro/{id}',       [CerebroController::class, 'show']);
Route::get('/cerebro/{id}/export',[CerebroController::class, 'export']);

// Magnet read-only & config
use App\Http\Controllers\Api\MagnetController;
Route::get('/magnet/marketplaces', [MagnetController::class, 'marketplaces']);
Route::get('/magnet/settings',     [ConstantsController::class, 'magnetSettings']);
Route::get('/magnet/history',      [MagnetController::class, 'history']);
Route::get('/magnet/{id}',         [MagnetController::class, 'show']);
Route::get('/magnet/{id}/export',  [MagnetController::class, 'export']);

// Search volume batch-cached is read-only (returns cached data, no compute cost)
use App\Http\Controllers\Api\SearchVolumeController;
Route::post('/search-volume/batch-cached', [SearchVolumeController::class, 'batchCached'])
    ->middleware('throttle:heavy');

// ─── Tool Endpoints (Auth required + usage limit tracking) ───────────────────

// 1. Market Analysis  (/analyze)  — counts as 1 usage per product scan
Route::middleware(['auth:sanctum', 'check.tool:market_analysis'])->group(function () {
    Route::post('/analyze', [ProductAnalysisController::class, 'analyze']);
});

// 2. FBA Calculator  (/fees/calculate-profit)
Route::middleware(['auth:sanctum', 'check.tool:fba_calculator'])->group(function () {
    Route::post('/fees/calculate-profit', [FeesController::class, 'calculateProfit']);
});

// 3. Reverse ASIN  (/reverse-asin/analyze and /results)
Route::middleware(['auth:sanctum', 'check.tool:reverse_asin'])->group(function () {
    Route::post('/reverse-asin/analyze',  [ReverseAsinController::class, 'analyzeKeywords']);
    Route::post('/reverse-asin/results',  [ReverseAsinController::class, 'saveResults']);
});

// 4. Search Volume  (/search-volume/estimate and /batch-estimate)
Route::middleware(['auth:sanctum', 'check.tool:search_volume', 'throttle:heavy'])->group(function () {
    Route::post('/search-volume/estimate',       [SearchVolumeController::class, 'estimate']);
    Route::post('/search-volume/batch-estimate', [SearchVolumeController::class, 'batchEstimate']);
});

// 5. Cerebro  (/cerebro/analyze)
Route::middleware(['auth:sanctum', 'check.tool:cerebro'])->group(function () {
    Route::post('/cerebro/analyze', [CerebroController::class, 'store']);
    Route::delete('/cerebro/{id}',  [CerebroController::class, 'destroy']);
});

// 6. Keyword Magnet  (/magnet/analyze)
Route::middleware(['auth:sanctum', 'check.tool:keyword_magnet'])->group(function () {
    Route::post('/magnet/analyze', [MagnetController::class, 'store']);
    Route::delete('/magnet/{id}',  [MagnetController::class, 'destroy']);
});

// Cerebro Folders routes are in web.php for session auth


// =================== Protected Routes (Require Auth) ===================

use App\Http\Controllers\Api\SubscriptionController;

// Public: list active pricing plans
Route::get('/pricing-plans', [SubscriptionController::class, 'publicPlans']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth endpoints
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    
    // User info (legacy endpoint)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Feedback & Calibration
    Route::post('/feedback/sales', [FeedbackController::class, 'submitSales']);
    Route::post('/feedback/correction', [FeedbackController::class, 'submitCorrection']);
    Route::get('/feedback/history', [FeedbackController::class, 'getHistory']);
    
    // Keywords - Cache (requires auth to prevent spam)
    Route::post('/keywords/cache', [KeywordsController::class, 'cache']);
    
    // Analytics
    Route::get('/analytics/category/{id}', [AnalyticsController::class, 'category']);
    Route::get('/analytics/trends', [AnalyticsController::class, 'trends']);
    Route::middleware('check.tool:analyze_product')->group(function () {
        Route::post('/analytics/product', [AnalyticsController::class, 'analyzeProduct']);
    });
    
    // Calibration (Enterprise users)
    Route::get('/calibration/status', [CalibrationController::class, 'status']);
    Route::post('/calibration/trigger', [CalibrationController::class, 'trigger']);
    Route::post('/calibration/run-full', [CalibrationController::class, 'runFull']);

    // ─── Dashboard Folders & Lists (for Chrome Extension) ────────────────────
    Route::get('/dashboard/folders',                  [DashboardApiController::class, 'folders']);
    Route::get('/dashboard/folders/{id}/lists',       [DashboardApiController::class, 'listsInFolder']);
    Route::post('/dashboard/lists',                   [DashboardApiController::class, 'createList']);
    Route::post('/dashboard/lists/{id}/items',        [DashboardApiController::class, 'saveItems']);
    Route::patch('/dashboard/lists/{id}',             [DashboardApiController::class, 'updateList']);
    Route::get('/dashboard/items/check/{asin}',       [DashboardApiController::class, 'checkItem']);
    Route::delete('/dashboard/lists/{listId}/items/{itemId}', [DashboardApiController::class, 'destroyItem']);

    // ─── Subscriptions ────────────────────────────────────────────────────────
    Route::get('/subscription/status',               [SubscriptionController::class, 'status']);
    Route::post('/subscription/payment-proof',        [SubscriptionController::class, 'uploadProof']);
    Route::post('/subscription/upgrade',              [SubscriptionController::class, 'upgrade']);
    Route::get('/subscription/instapay-info',         [SubscriptionController::class, 'instapayInfo']);
});

