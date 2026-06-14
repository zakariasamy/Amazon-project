<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardList extends Model
{
    protected $table = 'dashboard_lists';

    protected $fillable = [
        'user_id',
        'folder_id',
        'name',
        'type',
        'description',
        'item_count',
    ];

    /** Valid list types */
    const TYPES = [
        'products',
        'keyword_magnet',
        'competitor_keyword_analyzer',
        'reverse_asin',
        'market_analysis',
    ];

    /** Human-readable labels for each type */
    const TYPE_LABELS = [
        'products'                    => 'Products',
        'keyword_magnet'              => 'Keyword Magnet',
        'competitor_keyword_analyzer' => 'Competitor Keyword Analyzer',
        'reverse_asin'                => 'Reverse ASIN',
        'market_analysis'             => 'Market Analysis',
    ];

    /** Emoji icons for each type */
    const TYPE_ICONS = [
        'products'                    => '📦',
        'keyword_magnet'              => '🧲',
        'competitor_keyword_analyzer' => '🔍',
        'reverse_asin'                => '🔄',
        'market_analysis'             => '📊',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function folder()
    {
        return $this->belongsTo(DashboardFolder::class, 'folder_id');
    }

    public function items()
    {
        return $this->hasMany(DashboardListItem::class, 'list_id')->orderBy('created_at', 'desc');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** Recalculate and persist the item_count from actual DB count */
    public function syncItemCount(): void
    {
        $this->update(['item_count' => $this->items()->count()]);
    }

    /** Return the human-readable type label */
    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? ucfirst($this->type);
    }

    /** Return the emoji icon for this type */
    public function typeIcon(): string
    {
        return self::TYPE_ICONS[$this->type] ?? '📋';
    }
}
