<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardListItem extends Model
{
    protected $table = 'dashboard_list_items';

    protected $fillable = [
        'list_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function list()
    {
        return $this->belongsTo(DashboardList::class, 'list_id');
    }
}
