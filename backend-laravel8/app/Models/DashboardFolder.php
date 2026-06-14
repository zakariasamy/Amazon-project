<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardFolder extends Model
{
    protected $table = 'dashboard_folders';

    protected $fillable = [
        'user_id',
        'parent_id',
        'name',
        'color',
        'description',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    /** Parent folder (null for root folders) */
    public function parent()
    {
        return $this->belongsTo(DashboardFolder::class, 'parent_id');
    }

    /** Direct child folders */
    public function children()
    {
        return $this->hasMany(DashboardFolder::class, 'parent_id')->orderBy('name');
    }

    /** Lists contained directly in this folder */
    public function lists()
    {
        return $this->hasMany(DashboardList::class, 'folder_id')->orderBy('name');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Build the full ancestor breadcrumb path for this folder.
     * Returns an array of ['id' => ..., 'name' => ...] from root → current.
     */
    public function breadcrumb(): array
    {
        $path = [];
        $folder = $this;

        while ($folder) {
            array_unshift($path, ['id' => $folder->id, 'name' => $folder->name]);
            $folder = $folder->parent_id ? DashboardFolder::find($folder->parent_id) : null;
        }

        return $path;
    }
}
