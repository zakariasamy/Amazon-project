<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CerebroFolder;
use App\Models\CerebroFolderKeyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CerebroFolderController extends Controller
{
    /**
     * List all folders for the authenticated user
     */
    public function index(Request $request)
    {
        $folders = CerebroFolder::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'folders' => $folders
        ]);
    }

    /**
     * Create a new folder
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $folder = CerebroFolder::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'color' => $request->color ?? '#6366f1',
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'folder' => $folder,
            'message' => 'Folder created successfully'
        ], 201);
    }

    /**
     * Get folder details with keywords
     */
    public function show(Request $request, $id)
    {
        $folder = CerebroFolder::where('user_id', Auth::id())
            ->with('keywords')
            ->findOrFail($id);

        // Apply filters
        $query = $folder->keywords();

        if ($request->has('min_volume')) {
            $query->where('search_volume', '>=', $request->min_volume);
        }
        if ($request->has('max_volume')) {
            $query->where('search_volume', '<=', $request->max_volume);
        }
        if ($request->has('min_iq')) {
            $query->where('cerebro_iq_score', '>=', $request->min_iq);
        }
        if ($request->has('search')) {
            $query->where('keyword', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'search_volume');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $keywords = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'folder' => $folder,
            'keywords' => $keywords
        ]);
    }

    /**
     * Update folder
     */
    public function update(Request $request, $id)
    {
        $folder = CerebroFolder::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $folder->update($request->only(['name', 'color', 'description']));

        return response()->json([
            'success' => true,
            'folder' => $folder,
            'message' => 'Folder updated successfully'
        ]);
    }

    /**
     * Delete folder
     */
    public function destroy($id)
    {
        $folder = CerebroFolder::where('user_id', Auth::id())->findOrFail($id);
        $folder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Folder deleted successfully'
        ]);
    }

    /**
     * Add keywords to folder
     */
    public function addKeywords(Request $request, $id)
    {
        $folder = CerebroFolder::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'keywords' => 'required|array|min:1',
            'keywords.*.keyword' => 'required|string|max:200',
            'keywords.*.search_volume' => 'nullable|integer',
            'keywords.*.cerebro_iq_score' => 'nullable|numeric',
            'keywords.*.cpr_8day' => 'nullable|integer',
            'keywords.*.word_count' => 'nullable|integer',
            'keywords.*.competing_products' => 'nullable|integer',
            'keywords.*.organic_ranks' => 'nullable|array',
            'keywords.*.sponsored_ranks' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $added = 0;
        $duplicates = 0;

        foreach ($request->keywords as $kw) {
            // Check for duplicate
            $exists = $folder->keywords()->where('keyword', $kw['keyword'])->exists();
            
            if ($exists) {
                $duplicates++;
                continue;
            }

            CerebroFolderKeyword::create([
                'folder_id' => $folder->id,
                'keyword' => $kw['keyword'],
                'search_volume' => $kw['search_volume'] ?? 0,
                'cerebro_iq_score' => $kw['cerebro_iq_score'] ?? 0,
                'cpr_8day' => $kw['cpr_8day'] ?? null,
                'word_count' => $kw['word_count'] ?? str_word_count($kw['keyword']),
                'competing_products' => $kw['competing_products'] ?? 0,
                'organic_ranks' => $kw['organic_ranks'] ?? null,
                'sponsored_ranks' => $kw['sponsored_ranks'] ?? null,
                'source' => $kw['source'] ?? 'manual',
            ]);
            $added++;
        }

        $folder->updateKeywordCount();

        return response()->json([
            'success' => true,
            'added' => $added,
            'duplicates' => $duplicates,
            'total_keywords' => $folder->keyword_count,
            'message' => "Added {$added} keywords, {$duplicates} duplicates skipped"
        ]);
    }

    /**
     * Remove keywords from folder
     */
    public function removeKeywords(Request $request, $id)
    {
        $folder = CerebroFolder::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'keyword_ids' => 'required|array|min:1',
            'keyword_ids.*' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $deleted = $folder->keywords()
            ->whereIn('id', $request->keyword_ids)
            ->delete();

        $folder->updateKeywordCount();

        return response()->json([
            'success' => true,
            'removed' => $deleted,
            'total_keywords' => $folder->keyword_count,
            'message' => "Removed {$deleted} keywords"
        ]);
    }

    /**
     * Import CSV file into folder
     */
    public function importCsv(Request $request, $id)
    {
        $folder = CerebroFolder::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        // Read header row
        $header = fgetcsv($handle);
        $header = array_map('strtolower', $header);
        $header = array_map('trim', $header);

        // Map CSV columns to database fields
        $columnMap = [
            'keyword' => ['keyword', 'keywords', 'search term', 'search_term', 'term'],
            'search_volume' => ['search volume', 'volume', 'search_volume', 'sv'],
            'cerebro_iq_score' => ['iq score', 'iq', 'cerebro_iq', 'cerebro_iq_score'],
            'cpr_8day' => ['cpr', 'cpr 8-day', 'cpr_8day', 'cpr8'],
            'word_count' => ['word count', 'words', 'word_count'],
            'competing_products' => ['competing products', 'competition', 'competing_products'],
        ];

        $headerIndex = [];
        foreach ($columnMap as $field => $possibleNames) {
            foreach ($possibleNames as $name) {
                $idx = array_search($name, $header);
                if ($idx !== false) {
                    $headerIndex[$field] = $idx;
                    break;
                }
            }
        }

        // Keyword column is required
        if (!isset($headerIndex['keyword'])) {
            fclose($handle);
            return response()->json([
                'success' => false,
                'message' => 'CSV must have a "keyword" column'
            ], 422);
        }

        $added = 0;
        $duplicates = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $keyword = trim($row[$headerIndex['keyword']] ?? '');
            
            if (empty($keyword)) {
                $errors++;
                continue;
            }

            // Check for duplicate
            if ($folder->keywords()->where('keyword', $keyword)->exists()) {
                $duplicates++;
                continue;
            }

            try {
                CerebroFolderKeyword::create([
                    'folder_id' => $folder->id,
                    'keyword' => $keyword,
                    'search_volume' => isset($headerIndex['search_volume']) 
                        ? intval($row[$headerIndex['search_volume']] ?? 0) : 0,
                    'cerebro_iq_score' => isset($headerIndex['cerebro_iq_score']) 
                        ? floatval($row[$headerIndex['cerebro_iq_score']] ?? 0) : 0,
                    'cpr_8day' => isset($headerIndex['cpr_8day']) 
                        ? intval($row[$headerIndex['cpr_8day']] ?? 0) : null,
                    'word_count' => isset($headerIndex['word_count']) 
                        ? intval($row[$headerIndex['word_count']] ?? 0) : str_word_count($keyword),
                    'competing_products' => isset($headerIndex['competing_products']) 
                        ? intval($row[$headerIndex['competing_products']] ?? 0) : 0,
                    'source' => 'csv_import',
                ]);
                $added++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        fclose($handle);
        $folder->updateKeywordCount();

        return response()->json([
            'success' => true,
            'added' => $added,
            'duplicates' => $duplicates,
            'errors' => $errors,
            'total_keywords' => $folder->keyword_count,
            'message' => "Imported {$added} keywords, {$duplicates} duplicates, {$errors} errors"
        ]);
    }

    /**
     * Export folder keywords to CSV
     */
    public function exportCsv($id)
    {
        $folder = CerebroFolder::where('user_id', Auth::id())->findOrFail($id);
        $keywords = $folder->keywords()->get();

        $filename = 'cerebro_folder_' . $folder->id . '_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($keywords) {
            $handle = fopen('php://output', 'w');
            
            // Header row
            fputcsv($handle, [
                'Keyword',
                'Search Volume',
                'IQ Score',
                'CPR 8-Day',
                'Word Count',
                'Competing Products',
                'Source',
                'Notes'
            ]);

            foreach ($keywords as $kw) {
                fputcsv($handle, [
                    $kw->keyword,
                    $kw->search_volume,
                    $kw->cerebro_iq_score,
                    $kw->cpr_8day ?? '',
                    $kw->word_count,
                    $kw->competing_products,
                    $kw->source,
                    $kw->notes ?? ''
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
