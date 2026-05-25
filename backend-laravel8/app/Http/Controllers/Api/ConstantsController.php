<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ConstantsController extends Controller
{
    /**
     * Get all algorithm constants
     */
    public function index(Request $request)
    {
        $constants = Cache::remember('algorithm_constants', 3600, function () {
            return DB::table('algorithm_constants')
                ->where('is_active', true)
                ->orderBy('marketplace')
                ->orderBy('category')
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $constants,
            'version' => $this->getCurrentVersion()
        ]);
    }

    /**
     * Get constants for specific marketplace
     */
    public function byMarketplace(string $marketplace)
    {
        $constants = Cache::remember("constants_{$marketplace}", 3600, function () use ($marketplace) {
            return DB::table('algorithm_constants')
                ->where('is_active', true)
                ->where('marketplace', $marketplace)
                ->get();
        });

        return response()->json([
            'success' => true,
            'marketplace' => $marketplace,
            'data' => $constants,
            'version' => $this->getCurrentVersion()
        ]);
    }

    /**
     * Get current constants version
     */
    public function version()
    {
        return response()->json([
            'version' => $this->getCurrentVersion(),
            'updated_at' => now()->toISOString()
        ]);
    }

    private function getCurrentVersion()
    {
        $latest = DB::table('algorithm_constants')
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->first();

        return $latest->version ?? '2025.01.01';
    }

    /**
     * Get global settings from database
     */
    public function settings()
    {
        $settings = Cache::remember('app_settings', 300, function () {
            $rows = DB::table('app_settings')->get();
            
            $result = [];
            foreach ($rows as $row) {
                // Cast value based on type
                $value = $row->value;
                switch ($row->type) {
                    case 'integer':
                        $value = (int) $value;
                        break;
                    case 'boolean':
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        break;
                    case 'json':
                        $value = json_decode($value, true);
                        break;
                    // string stays as is
                }
                $result[$row->key] = $value;
            }
            
            return $result;
        });

        // Provide defaults if table doesn't exist or is empty
        $defaults = [
            // Search Page settings
            'search_page_products_limit' => 20,
            'search_page_bsr_parallel_requests' => 5,
            'search_page_bsr_delay_ms' => 300,
            // Reverse ASIN settings
            'reverse_asin_products_limit' => 10,
            'reverse_asin_bsr_parallel_requests' => 3,
            'reverse_asin_bsr_delay_ms' => 500,
            'reverse_asin_keywords_limit' => 50,
            'reverse_asin_search_delay_ms' => 1500,
            'reverse_asin_backend_batch_size' => 5,
        ];

        return response()->json([
            'success' => true,
            'settings' => array_merge($defaults, $settings ?: [])
        ]);
    }

    /**
     * Get Magnet tool settings from database
     */
    public function magnetSettings()
    {
        $settings = Cache::remember('magnet_settings', 300, function () {
            $rows = DB::table('magnet_settings')->get();
            
            $result = [];
            foreach ($rows as $row) {
                $value = $row->value;
                // Try to cast to appropriate type
                if (is_numeric($value)) {
                    $value = strpos($value, '.') !== false ? (float) $value : (int) $value;
                } else if ($value === 'true' || $value === 'false') {
                    $value = $value === 'true';
                }
                $result[$row->key] = $value;
            }
            
            return $result;
        });

        // Provide defaults
        $defaults = [
            'attribute_product_count' => 5,
            'max_keywords_limit' => 1000,
            'use_autocomplete' => true,
            'use_related' => true,
            'use_titles' => true,
            'use_attributes' => true,
            'delay_between_requests' => 300,
            'attribute_variation_scope' => 'seed', // Options: 'seed', 'top_n', 'all'
            'attribute_variation_limit' => 10, // Used when scope is 'top_n'
        ];

        return response()->json([
            'success' => true,
            'settings' => array_merge($defaults, $settings ?: [])
        ]);
    }
}
