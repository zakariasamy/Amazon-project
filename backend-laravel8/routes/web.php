<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\CerebroWebController;
use App\Http\Controllers\MagnetWebController;
use App\Http\Controllers\Api\CerebroFolderController;
use App\Http\Controllers\GuideController;

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
    return view('welcome');
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
    return view('welcome');
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
    
    // Cerebro / Keyword Analyzer Pro - Analyses
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
});
