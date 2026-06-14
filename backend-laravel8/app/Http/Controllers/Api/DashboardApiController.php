<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardFolder;
use App\Models\DashboardList;
use App\Models\DashboardListItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Token-authenticated API controller for the Chrome extension.
 * Allows the extension to list folders/lists and save items.
 */
class DashboardApiController extends Controller
{
    /**
     * GET /api/dashboard/folders
     * Returns all folders for the authenticated user (flat list with parent_id).
     */
    public function folders(Request $request)
    {
        $folders = DashboardFolder::where('user_id', Auth::id())
            ->orderByRaw('COALESCE(parent_id, 0), name')
            ->get(['id', 'parent_id', 'name', 'color']);

        return response()->json(['success' => true, 'folders' => $folders]);
    }

    /**
     * GET /api/dashboard/folders/{id}/lists
     * Returns all lists in a specific folder.
     */
    public function listsInFolder(Request $request, $id)
    {
        $folder = DashboardFolder::where('user_id', Auth::id())->findOrFail($id);

        $lists = DashboardList::where('folder_id', $folder->id)
            ->orderBy('name')
            ->get(['id', 'folder_id', 'name', 'type', 'item_count']);

        return response()->json(['success' => true, 'lists' => $lists]);
    }

    /**
     * POST /api/dashboard/lists
     * Create a new list inside a folder (from extension).
     */
    public function createList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'folder_id'   => 'required|integer|exists:dashboard_folders,id',
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:' . implode(',', DashboardList::TYPES),
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Verify folder ownership
        $folder = DashboardFolder::where('user_id', Auth::id())->find($request->folder_id);
        if (!$folder) {
            return response()->json(['success' => false, 'message' => 'Folder not found.'], 404);
        }

        $list = DashboardList::create([
            'user_id'     => Auth::id(),
            'folder_id'   => $folder->id,
            'name'        => $request->name,
            'type'        => $request->type,
            'description' => $request->description,
            'item_count'  => 0,
        ]);

        return response()->json(['success' => true, 'list' => $list], 201);
    }

    /**
     * POST /api/dashboard/lists/{id}/items
     * Save one or more items from the extension into a list.
     * Body: { items: [ { ...row payload... }, ... ] }
     */
    public function saveItems(Request $request, $id)
    {
        $list = DashboardList::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'items'       => 'required|array|min:1',
            'items.*'     => 'required|array',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Save description metadata (e.g. analyzed products for competitor_keyword_analyzer)
        // Only update when description is explicitly provided and not null
        if ($request->has('description') && !is_null($request->description)) {
            $list->update(['description' => $request->description]);
        }

        $added = 0;
        foreach ($request->items as $itemData) {
            DashboardListItem::create([
                'list_id' => $list->id,
                'data'    => $itemData,
            ]);
            $added++;
        }

        $list->syncItemCount();

        return response()->json([
            'success' => true,
            'added'   => $added,
            'total'   => $list->item_count,
            'message' => "{$added} item(s) saved to \"{$list->name}\".",
        ], 201);
    }

    /**
     * GET /api/dashboard/items/check/{asin}
     * Checks if a product with the given ASIN is already saved in any of the user's lists
     * and returns the list of matching folders/lists.
     */
    public function checkItem(Request $request, $asin)
    {
        $type = $request->query('type');

        $items = \App\Models\DashboardListItem::with(['list.folder'])
            ->whereHas('list', function ($query) use ($type) {
                $query->where('user_id', Auth::id());
                if ($type) {
                    $query->where('type', $type);
                }
            })->where('data->asin', $asin)
            ->get();

        $savedIn = $items->map(function ($item) {
            return [
                'item_id' => $item->id,
                'list_id' => $item->list->id,
                'list_name' => $item->list->name,
                'folder_id' => $item->list->folder->id ?? null,
                'folder_name' => $item->list->folder->name ?? 'Root',
                'folder_color' => $item->list->folder->color ?? '#6366f1',
            ];
        });

        return response()->json([
            'success' => true,
            'exists' => $savedIn->isNotEmpty(),
            'saved_in' => $savedIn
        ]);
    }

    /**
     * PATCH /api/dashboard/lists/{id}
     * Update metadata (e.g. description) for an existing list without adding items.
     * Body: { description: "..." }
     */
    public function updateList(Request $request, $id)
    {
        $list = DashboardList::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string',
            'name'        => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $updates = [];
        if ($request->has('description') && !is_null($request->description)) {
            $updates['description'] = $request->description;
        }
        if ($request->has('name') && !is_null($request->name)) {
            $updates['name'] = $request->name;
        }

        if (!empty($updates)) {
            $list->update($updates);
        }

        return response()->json(['success' => true, 'list' => $list]);
    }

    /**
     * DELETE /api/dashboard/lists/{listId}/items/{itemId}
     * Delete an item from a list (from extension).
     */
    public function destroyItem(Request $request, $listId, $itemId)
    {
        $list = \App\Models\DashboardList::where('user_id', Auth::id())->findOrFail($listId);
        $item = \App\Models\DashboardListItem::where('list_id', $list->id)->findOrFail($itemId);
        $item->delete();
        $list->syncItemCount();

        return response()->json(['success' => true, 'message' => 'Item removed successfully.']);
    }
}
