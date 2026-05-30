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

// *** MAIN PRODUCT ANALYSIS ENDPOINT ***
// This is the main endpoint that extension calls for all calculations
Route::post('/analyze', [ProductAnalysisController::class, 'analyze']);
Route::post('/products/cache/batch', [ProductCacheController::class, 'batch']);

// Constants & Configuration (Read-only, public)
Route::get('/constants', [ConstantsController::class, 'index']);
Route::get('/settings', [ConstantsController::class, 'settings']); // New settings endpoint
Route::get('/constants/version', [ConstantsController::class, 'version']);
Route::get('/constants/{marketplace}', [ConstantsController::class, 'byMarketplace']);

// Fees (Read-only, public)
Route::get('/fees/{marketplace}', [FeesController::class, 'byMarketplace']);
Route::post('/fees/calculate-profit', [FeesController::class, 'calculateProfit']);

// Seasonality (Read-only, public)
Route::get('/seasonality', [SeasonalityController::class, 'index']);

// Keywords - Popular (Read-only, public)
Route::get('/keywords/popular/{marketplace}', [KeywordsController::class, 'popular']);

// Reverse ASIN - Get Keywords (Read-only, public)
Route::get('/reverse-asin/{asin}/keywords', [ReverseAsinController::class, 'getKeywords']);
Route::get('/reverse-asin/{asin}/suggest', [ReverseAsinController::class, 'suggestKeywords']);
Route::get('/reverse-asin/{asin}/history', [ReverseAsinController::class, 'getHistory']);
// Reverse ASIN - Submit Rankings (Public for extension)
Route::post('/reverse-asin/ranking', [ReverseAsinController::class, 'submitRanking']);
Route::post('/reverse-asin/results', [ReverseAsinController::class, 'saveResults']);
Route::post('/reverse-asin/analyze', [ReverseAsinController::class, 'analyzeKeywords']);

// Search Volume Estimation (Public for extension - with high rate limit)
use App\Http\Controllers\Api\SearchVolumeController;
Route::post('/search-volume/estimate', [SearchVolumeController::class, 'estimate'])
    ->middleware('throttle:heavy');
Route::post('/search-volume/batch-estimate', [SearchVolumeController::class, 'batchEstimate'])
    ->middleware('throttle:heavy');
Route::post('/search-volume/batch-cached', [SearchVolumeController::class, 'batchCached'])
    ->middleware('throttle:heavy');

// Cerebro - Multi-ASIN Keyword Analysis
use App\Http\Controllers\Api\CerebroController;
Route::post('/cerebro/analyze', [CerebroController::class, 'store']);
Route::get('/cerebro/history', [CerebroController::class, 'history']);
Route::get('/cerebro/{id}', [CerebroController::class, 'show']);
Route::delete('/cerebro/{id}', [CerebroController::class, 'destroy']);
Route::get('/cerebro/{id}/export', [CerebroController::class, 'export']);

// Magnet - Keyword Suggestion Tool
use App\Http\Controllers\Api\MagnetController;
Route::get('/magnet/marketplaces', [MagnetController::class, 'marketplaces']); // Public endpoint
Route::get('/magnet/settings', [ConstantsController::class, 'magnetSettings']); // Configurable settings

// Protected Magnet routes (require token authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/magnet/analyze', [MagnetController::class, 'store']);
    Route::get('/magnet/history', [MagnetController::class, 'history']);
    Route::get('/magnet/{id}', [MagnetController::class, 'show']);
    Route::delete('/magnet/{id}', [MagnetController::class, 'destroy']);
    Route::get('/magnet/{id}/export', [MagnetController::class, 'export']);
});

// Cerebro Folders routes are in web.php for session auth


// =================== Protected Routes (Require Auth) ===================

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
    Route::post('/analytics/product', [AnalyticsController::class, 'analyzeProduct']);
    
    // Calibration (Enterprise users)
    Route::get('/calibration/status', [CalibrationController::class, 'status']);
    Route::post('/calibration/trigger', [CalibrationController::class, 'trigger']);
    Route::post('/calibration/run-full', [CalibrationController::class, 'runFull']);
});
