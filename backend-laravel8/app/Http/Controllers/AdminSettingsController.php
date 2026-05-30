<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminSettingsController extends Controller
{
    public function edit()
    {
        $rows = DB::table('app_settings')
            ->whereIn('key', $this->managedKeys())
            ->orderBy('category')
            ->orderBy('key')
            ->get()
            ->keyBy('key');

        $defaults = $this->defaults();
        $settings = [];

        foreach ($defaults as $key => $default) {
            $row = $rows->get($key);
            $settings[$key] = [
                'value' => $row ? $this->castValue($row->value, $row->type) : $default['value'],
                'type' => $row->type ?? $default['type'],
                'description' => $row->description ?? $default['description'],
            ];
        }

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'search_page_products_limit' => ['required', 'integer', 'min:0', 'max:60'],
            'search_page_bsr_parallel_requests' => ['required', 'integer', 'min:1', 'max:10'],
            'search_page_bsr_delay_ms' => ['required', 'integer', 'min:0', 'max:5000'],
            'cerebro_fetch_bsr_enabled' => ['nullable', 'boolean'],
            'cerebro_bsr_products_limit' => ['required', 'integer', 'min:0', 'max:60'],
            'cerebro_bsr_parallel_requests' => ['required', 'integer', 'min:1', 'max:8'],
            'cerebro_bsr_delay_ms' => ['required', 'integer', 'min:0', 'max:5000'],
            'cerebro_search_delay_ms' => ['required', 'integer', 'min:0', 'max:10000'],
            'cerebro_parallel_keywords' => ['required', 'integer', 'min:1', 'max:20'],
            'cerebro_use_backend_cache' => ['nullable', 'boolean'],
            'reverse_asin_products_limit' => ['required', 'integer', 'min:0', 'max:60'],
            'reverse_asin_bsr_parallel_requests' => ['required', 'integer', 'min:1', 'max:10'],
            'reverse_asin_bsr_delay_ms' => ['required', 'integer', 'min:0', 'max:5000'],
            'reverse_asin_keywords_limit' => ['required', 'integer', 'min:1', 'max:500'],
            'reverse_asin_search_delay_ms' => ['required', 'integer', 'min:0', 'max:10000'],
            'reverse_asin_backend_batch_size' => ['required', 'integer', 'min:1', 'max:50'],
            'test_mode_enabled' => ['nullable', 'boolean'],
            'test_mode_keyword' => ['required', 'string', 'max:255'],
            'test_mode_product_url' => ['required', 'string', 'max:2048'],
        ]);

        $data['cerebro_fetch_bsr_enabled'] = $request->boolean('cerebro_fetch_bsr_enabled');
        $data['cerebro_use_backend_cache'] = $request->boolean('cerebro_use_backend_cache');
        $data['test_mode_enabled'] = $request->boolean('test_mode_enabled');

        foreach ($this->defaults() as $key => $default) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $default['type'] === 'boolean'
                        ? ($data[$key] ? 'true' : 'false')
                        : (string) $data[$key],
                    'type' => $default['type'],
                    'description' => $default['description'],
                    'category' => strpos($key, 'test_mode') === 0
                        ? 'test_mode'
                        : (strpos($key, 'reverse_asin_') === 0
                            ? 'reverse_asin'
                            : (strpos($key, 'search_page_') === 0 ? 'search_page' : 'cerebro')),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        Cache::forget('app_settings');

        return redirect()
            ->route('admin.settings')
            ->with('status', 'Settings saved.');
    }

    private function managedKeys(): array
    {
        return array_keys($this->defaults());
    }

    private function defaults(): array
    {
        return [
            'search_page_products_limit' => [
                'value' => 20,
                'type' => 'integer',
                'description' => '[Market Analysis] Max search-page products to fetch BSR for. Higher = closer sales/volume estimate, slower analysis.',
            ],
            'search_page_bsr_parallel_requests' => [
                'value' => 5,
                'type' => 'integer',
                'description' => '[Market Analysis] Parallel product page fetches for BSR.',
            ],
            'search_page_bsr_delay_ms' => [
                'value' => 300,
                'type' => 'integer',
                'description' => '[Market Analysis] Delay in milliseconds between BSR fetch batches.',
            ],
            'cerebro_fetch_bsr_enabled' => [
                'value' => true,
                'type' => 'boolean',
                'description' => '[Competitor Keyword Analyzer] Fetch product pages for BSR per keyword SERP. More accurate volume, slower analysis.',
            ],
            'cerebro_use_backend_cache' => [
                'value' => true,
                'type' => 'boolean',
                'description' => '[Competitor Keyword Analyzer] Skip BSR fetching for keywords that already have a cached search volume on the backend. Drastically increases speed.',
            ],
            'cerebro_bsr_products_limit' => [
                'value' => 20,
                'type' => 'integer',
                'description' => '[Competitor Keyword Analyzer] Max SERP products to fetch BSR for per keyword. Match Search Page Market Analysis for closest volume parity.',
            ],
            'cerebro_bsr_parallel_requests' => [
                'value' => 3,
                'type' => 'integer',
                'description' => '[Competitor Keyword Analyzer] Parallel product page fetches for BSR.',
            ],
            'cerebro_bsr_delay_ms' => [
                'value' => 500,
                'type' => 'integer',
                'description' => '[Competitor Keyword Analyzer] Delay in milliseconds between BSR fetch batches.',
            ],
            'cerebro_search_delay_ms' => [
                'value' => 500,
                'type' => 'integer',
                'description' => '[Competitor Keyword Analyzer] Delay in milliseconds between keyword SERP searches.',
            ],
            'cerebro_parallel_keywords' => [
                'value' => 5,
                'type' => 'integer',
                'description' => '[Competitor Keyword Analyzer] Number of keywords to search concurrently.',
            ],
            'reverse_asin_products_limit' => [
                'value' => 20,
                'type' => 'integer',
                'description' => '[Reverse ASIN] Max organic and sponsored products to enrich with BSR per keyword.',
            ],
            'reverse_asin_bsr_parallel_requests' => [
                'value' => 3,
                'type' => 'integer',
                'description' => '[Reverse ASIN] Parallel product page BSR fetches.',
            ],
            'reverse_asin_bsr_delay_ms' => [
                'value' => 500,
                'type' => 'integer',
                'description' => '[Reverse ASIN] Delay in milliseconds between BSR fetch batches.',
            ],
            'reverse_asin_keywords_limit' => [
                'value' => 50,
                'type' => 'integer',
                'description' => '[Reverse ASIN] Max competitor keywords to analyze per ASIN search.',
            ],
            'reverse_asin_search_delay_ms' => [
                'value' => 1500,
                'type' => 'integer',
                'description' => '[Reverse ASIN] Delay in milliseconds between keyword search scraping requests.',
            ],
            'reverse_asin_backend_batch_size' => [
                'value' => 5,
                'type' => 'integer',
                'description' => '[Reverse ASIN] Keywords per batch to process concurrently with backend.',
            ],
            'test_mode_enabled' => [
                'value' => false,
                'type' => 'boolean',
                'description' => 'Enable Test Mode to override analysis tools (Competitor Keyword Analyzer, Reverse ASIN, and Market Analysis) with a specific test product and seed keyword.',
            ],
            'test_mode_keyword' => [
                'value' => 'portal scale body',
                'type' => 'string',
                'description' => 'The forced seed keyword to use when Test Mode is enabled (default: portal scale body).',
            ],
            'test_mode_product_url' => [
                'value' => 'https://www.amazon.eg/-/en/Portal-Accuracy-Digital-Kitchen-Scale/dp/B08P5MP4YC/ref=sr_1_2?crid=36LHSH8O6O2ES&dib=eyJ2IjoiMSJ9.byp1-SMWW_mZPJswYRC3P2tk2Yew88kCJajm3ZFck2nhXCOasuoJf2RJbbuWGRS-MJuIPlZ-T_uNgFVpN-11t-IrOqYC6BEvQ3_ThFuctNOS0zO6PRA7jebIlHTZTINKkpBkIpci2fdfZtTkdId7detczK02-VXD4t38Xg7InXtkbqj5CZGChq3n-TvakSe0Uf5J_a3a0YfxeZJzTqLcg7yda3647QWbZehfKtrk4l4BQEzS4AImvQRwOCyKYqtLR3raBl192ThqUMKR_Utqi55tMLVGDBoEcpW2yg57iZc.ZtlfuF8ML_Imdyqwweol8cl-OQhqEJB5HxZkgKQj4V8&dib_tag=se&keywords=portal+scale+body&qid=1779798144&sprefix=portal+scale+body%2Caps%2C209&sr=8-2',
                'type' => 'string',
                'description' => 'The forced Amazon product URL to analyze when Test Mode is enabled.',
            ],
        ];
    }

    private function castValue(string $value, string $type)
    {
        if ($type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if ($type === 'integer') {
            return (int) $value;
        }

        return $value;
    }
}
