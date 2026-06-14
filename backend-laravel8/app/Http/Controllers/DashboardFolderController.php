<?php

namespace App\Http\Controllers;

use App\Models\DashboardFolder;
use App\Models\DashboardList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashboardFolderController extends Controller
{
    /**
     * Root folders page — shows all top-level folders and lists.
     */
    public function index()
    {
        $folders = DashboardFolder::where('user_id', Auth::id())
            ->whereNull('parent_id')
            ->withCount('children')
            ->with('lists')
            ->orderBy('name')
            ->get();

        return view('folders.index', compact('folders'));
    }

    /**
     * Show the contents of a single folder (sub-folders + lists inside it).
     */
    public function show($id)
    {
        $folder = DashboardFolder::where('user_id', Auth::id())->findOrFail($id);

        $children = DashboardFolder::where('user_id', Auth::id())
            ->where('parent_id', $id)
            ->withCount('children')
            ->with('lists')
            ->orderBy('name')
            ->get();

        $lists = DashboardList::where('folder_id', $id)
            ->orderBy('name')
            ->get();

        $breadcrumb = $folder->breadcrumb();

        return view('folders.show', compact('folder', 'children', 'lists', 'breadcrumb'));
    }

    /**
     * Create a new folder (root or nested).
     * Accepts AJAX (JSON) or regular form POST.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:100',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'parent_id'   => 'nullable|integer|exists:dashboard_folders,id',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Verify parent belongs to this user
        if ($request->parent_id) {
            $parent = DashboardFolder::where('user_id', Auth::id())->find($request->parent_id);
            if (!$parent) {
                abort(403, 'Parent folder not found.');
            }
        }

        $folder = DashboardFolder::create([
            'user_id'     => Auth::id(),
            'parent_id'   => $request->parent_id,
            'name'        => $request->name,
            'color'       => $request->color ?? '#6366f1',
            'description' => $request->description,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'folder' => $folder], 201);
        }

        return redirect()->back()->with('success', 'Folder created successfully.');
    }

    /**
     * Rename / recolor a folder.
     */
    public function update(Request $request, $id)
    {
        $folder = DashboardFolder::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|required|string|max:100',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $folder->update($request->only(['name', 'color', 'description']));

        return response()->json(['success' => true, 'folder' => $folder]);
    }

    /**
     * Delete a folder (cascade removes sub-folders, lists and items via FK).
     */
    public function destroy(Request $request, $id)
    {
        $folder = DashboardFolder::where('user_id', Auth::id())->findOrFail($id);
        $parentId = $folder->parent_id;
        $folder->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Folder deleted.']);
        }

        if ($parentId) {
            return redirect()->route('folders.show', $parentId)->with('success', 'Folder deleted.');
        }
        return redirect()->route('folders.index')->with('success', 'Folder deleted.');
    }
}
