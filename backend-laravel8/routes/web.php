<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\CerebroWebController;
use App\Http\Controllers\MagnetWebController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\Api\CerebroFolderController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\DashboardFolderController;
use App\Http\Controllers\DashboardListController;
use App\Http\Controllers\Admin\PricingAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Models\PricingPlan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public pages
Route::get('/', function () {
    $plans = PricingPlan::active()->get();
    return view('welcome', compact('plans'));
})->name('home');

// Product Research Methods Guide (public, bilingual)
Route::get('/guide', [GuideController::class, 'index'])->name('guide');

// Suppliers Directory (public, bilingual)
Route::get('/suppliers', [App\Http\Controllers\SupplierController::class, 'index'])->name('suppliers.index');
Route::get('/suppliers/products', [App\Http\Controllers\SupplierController::class, 'products'])->name('suppliers.products');
Route::get('/suppliers/products/{id}', [App\Http\Controllers\SupplierController::class, 'showProduct'])->name('suppliers.products.show');
Route::get('/suppliers/apply', [App\Http\Controllers\SupplierController::class, 'showApplicationForm'])->name('suppliers.apply');
Route::post('/suppliers/apply', [App\Http\Controllers\SupplierController::class, 'submitApplication'])->name('suppliers.submit');
Route::get('/suppliers/{id}', [App\Http\Controllers\SupplierController::class, 'show'])->name('suppliers.show');

Route::get('/home', function () {
    $plans = PricingPlan::active()->get();
    return view('welcome', compact('plans'));
});

// Authentication routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);
});

// Protected routes (authenticated users only)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [WebAuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // Standard user subscription routes
    Route::get('/subscription/upgrade', [\App\Http\Controllers\SubscriptionWebController::class, 'upgrade'])->name('subscription.upgrade');
    Route::get('/subscription/pay/{plan_id}', [\App\Http\Controllers\SubscriptionWebController::class, 'pay'])->name('subscription.pay');
    Route::post('/subscription/pay', [\App\Http\Controllers\SubscriptionWebController::class, 'submitPay'])->name('subscription.pay.submit');
    
    // Cerebro / Competitor Keyword Analyzer - Analyses
    Route::get('/cerebro', [CerebroWebController::class, 'index'])->name('cerebro.index');
    Route::get('/cerebro/{id}', [CerebroWebController::class, 'show'])->name('cerebro.show');
    Route::get('/cerebro/{id}/export', [CerebroWebController::class, 'export'])->name('cerebro.export');
    Route::delete('/cerebro/{id}', [CerebroWebController::class, 'destroy'])->name('cerebro.destroy');
    
    // Magnet / Keyword Suggestion Tool
    Route::get('/magnet', [MagnetWebController::class, 'index'])->name('magnet.index');
    Route::post('/magnet/search', [MagnetWebController::class, 'search'])->name('magnet.search');
    Route::get('/magnet/proxy/suggestions', [MagnetWebController::class, 'proxySuggestions'])->name('magnet.proxy.suggestions');
    Route::get('/magnet/proxy/search-page', [MagnetWebController::class, 'proxySearchPage'])->name('magnet.proxy.searchPage');
    Route::post('/magnet/save-analysis', [MagnetWebController::class, 'saveAnalysis'])->name('magnet.saveAnalysis');
    Route::get('/magnet/{id}', [MagnetWebController::class, 'show'])->name('magnet.show');
    Route::get('/magnet/{id}/export', [MagnetWebController::class, 'export'])->name('magnet.export');
    Route::delete('/magnet/{id}', [MagnetWebController::class, 'destroy'])->name('magnet.destroy');
    
    // Cerebro Folders - Views
    Route::get('/dashboard/cerebro/folders', [CerebroWebController::class, 'folders'])->name('cerebro.folders');
    Route::get('/dashboard/cerebro/folders/{id}', [CerebroWebController::class, 'folderShow'])->name('cerebro.folder.show');
    
    // Cerebro Folders - AJAX Actions
    Route::post('/cerebro/folders', [CerebroFolderController::class, 'store'])->name('cerebro.folder.store');
    Route::put('/cerebro/folders/{id}', [CerebroFolderController::class, 'update'])->name('cerebro.folder.update');
    Route::delete('/cerebro/folders/{id}', [CerebroFolderController::class, 'destroy'])->name('cerebro.folder.destroy');
    Route::post('/cerebro/folders/{id}/keywords', [CerebroFolderController::class, 'addKeywords'])->name('cerebro.folder.addKeywords');
    Route::delete('/cerebro/folders/{id}/keywords', [CerebroFolderController::class, 'removeKeywords'])->name('cerebro.folder.removeKeywords');
    Route::post('/cerebro/folders/{id}/import-csv', [CerebroFolderController::class, 'importCsv'])->name('cerebro.folder.importCsv');
    Route::get('/cerebro/folders/{id}/export-csv', [CerebroFolderController::class, 'exportCsv'])->name('cerebro.folder.exportCsv');

    // ─── Dashboard Folders & Lists (starts with /dashboard prefix) ────────────
    Route::prefix('dashboard')->group(function () {
        Route::get('/folders',                           [DashboardFolderController::class, 'index'])->name('folders.index');
        Route::get('/folders/{id}',                      [DashboardFolderController::class, 'show'])->name('folders.show');
        Route::post('/folders',                          [DashboardFolderController::class, 'store'])->name('folders.store');
        Route::put('/folders/{id}',                      [DashboardFolderController::class, 'update'])->name('folders.update');
        Route::delete('/folders/{id}',                   [DashboardFolderController::class, 'destroy'])->name('folders.destroy');

        // Dashboard Lists
        Route::post('/folders/{folderId}/lists',         [DashboardListController::class, 'store'])->name('lists.store');
        Route::get('/lists/{id}',                        [DashboardListController::class, 'show'])->name('lists.show');
        Route::delete('/lists/{id}',                     [DashboardListController::class, 'destroy'])->name('lists.destroy');

        // List Items
        Route::post('/lists/{listId}/items',             [DashboardListController::class, 'storeItem'])->name('lists.items.store');
        Route::delete('/lists/{listId}/items',           [DashboardListController::class, 'destroyItems'])->name('lists.items.destroyBulk');
        Route::delete('/lists/{listId}/items/{itemId}',  [DashboardListController::class, 'destroyItem'])->name('lists.items.destroy');
    });

    // ─── Admin Settings & Pricing ──────────────────────────────────────────────
    Route::middleware('admin')->group(function () {
        Route::get('/admin/settings', [AdminSettingsController::class, 'edit'])->name('admin.settings');
        Route::post('/admin/settings', [AdminSettingsController::class, 'update'])->name('admin.settings.update');

        Route::prefix('admin/users')->name('admin.users.')->group(function () {
            Route::get('/', [UserAdminController::class, 'index'])->name('index');
            Route::get('/create', [UserAdminController::class, 'create'])->name('create');
            Route::post('/', [UserAdminController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [UserAdminController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UserAdminController::class, 'update'])->name('update');
            Route::post('/{id}/trial', [UserAdminController::class, 'giveTrial'])->name('giveTrial');
        });

        Route::prefix('admin/pricing')->name('admin.pricing.')->group(function () {
            Route::get('/',                              [PricingAdminController::class, 'index'])->name('index');
            Route::get('/create',                        [PricingAdminController::class, 'create'])->name('create');
            Route::post('/',                             [PricingAdminController::class, 'store'])->name('store');
            Route::get('/{id}/edit',                     [PricingAdminController::class, 'edit'])->name('edit');
            Route::put('/{id}',                          [PricingAdminController::class, 'update'])->name('update');
            Route::delete('/{id}',                       [PricingAdminController::class, 'destroy'])->name('destroy');

            // InstaPay settings
            Route::post('/instapay',                     [PricingAdminController::class, 'updateInstapay'])->name('instapay.update');

            // Subscriptions
            Route::get('/subscriptions',                 [PricingAdminController::class, 'subscriptions'])->name('subscriptions');
            Route::get('/subscriptions/{id}',            [PricingAdminController::class, 'viewSubscription'])->name('subscriptions.show');
            Route::post('/subscriptions/{id}/approve',   [PricingAdminController::class, 'approveSubscription'])->name('subscriptions.approve');
            Route::post('/subscriptions/{id}/reject',    [PricingAdminController::class, 'rejectSubscription'])->name('subscriptions.reject');
            Route::post('/subscriptions/{id}/reset-limits', [PricingAdminController::class, 'resetUserLimits'])->name('subscriptions.resetLimits');
            Route::post('/limits/{limitId}',             [PricingAdminController::class, 'updateUserLimit'])->name('limits.update');
        });
    });
});
