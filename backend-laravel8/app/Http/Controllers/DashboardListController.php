<?php

namespace App\Http\Controllers;

use App\Models\DashboardFolder;
use App\Models\DashboardList;
use App\Models\DashboardListItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashboardListController extends Controller
{
    /**
     * Create a new list inside a folder.
     */
    public function store(Request $request, $folderId)
    {
        $folder = DashboardFolder::where('user_id', Auth::id())->findOrFail($folderId);

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:' . implode(',', DashboardList::TYPES),
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $list = DashboardList::create([
            'user_id'     => Auth::id(),
            'folder_id'   => $folder->id,
            'name'        => $request->name,
            'type'        => $request->type,
            'description' => $request->description,
            'item_count'  => 0,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'list' => $list], 201);
        }

        return redirect()->route('folders.show', $folder->id)->with('success', 'List created successfully.');
    }

    /**
     * Show a list and its saved items.
     */
    public function show(Request $request, $id)
    {
        $list = DashboardList::where('user_id', Auth::id())->with('folder')->findOrFail($id);

        $items = $list->items()->paginate(50);

        $breadcrumb = $list->folder ? $list->folder->breadcrumb() : [];

        return view('lists.show', compact('list', 'items', 'breadcrumb'));
    }

    /**
     * Delete an entire list (cascade removes items).
     */
    public function destroy(Request $request, $id)
    {
        $list = DashboardList::where('user_id', Auth::id())->findOrFail($id);
        $folderId = $list->folder_id;
        $list->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'List deleted.']);
        }

        return redirect()->route('folders.show', $folderId)->with('success', 'List deleted.');
    }

    /**
     * Save one or more items into a list.
     * Accepts JSON: { items: [ { ...row data... }, ... ] }
     */
    public function storeItem(Request $request, $listId)
    {
        $list = DashboardList::where('user_id', Auth::id())->findOrFail($listId);

        $validator = Validator::make($request->all(), [
            'items'       => 'required|array|min:1',
            'items.*'     => 'required|array',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

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
     * Remove a single item from a list.
     */
    public function destroyItem(Request $request, $listId, $itemId)
    {
        $list = DashboardList::where('user_id', Auth::id())->findOrFail($listId);

        $item = DashboardListItem::where('list_id', $list->id)->findOrFail($itemId);
        $item->delete();

        $list->syncItemCount();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Item removed.']);
        }

        return redirect()->back()->with('success', 'Item removed.');
    }

    /**
     * Bulk-remove selected items from a list.
     * Accepts JSON: { item_ids: [1, 2, 3] }
     */
    public function destroyItems(Request $request, $listId)
    {
        $list = DashboardList::where('user_id', Auth::id())->findOrFail($listId);

        $validator = Validator::make($request->all(), [
            'item_ids'   => 'required|array|min:1',
            'item_ids.*' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $deleted = DashboardListItem::where('list_id', $list->id)
            ->whereIn('id', $request->item_ids)
            ->delete();

        $list->syncItemCount();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'removed' => $deleted]);
        }

        return redirect()->back()->with('success', "{$deleted} item(s) removed.");
    }
}
