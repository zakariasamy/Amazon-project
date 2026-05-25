<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CerebroFolderKeyword extends Model
{
    use HasFactory;

    protected $table = 'cerebro_folder_keywords';

    protected $fillable = [
        'folder_id',
        'keyword',
        'search_volume',
        'cerebro_iq_score',
        'cpr_8day',
        'word_count',
        'competing_products',
        'title_density',
        'organic_ranks',
        'sponsored_ranks',
        'source',
        'notes',
    ];

    protected $casts = [
        'search_volume' => 'integer',
        'cerebro_iq_score' => 'decimal:2',
        'cpr_8day' => 'integer',
        'word_count' => 'integer',
        'competing_products' => 'integer',
        'title_density' => 'decimal:2',
        'organic_ranks' => 'array',
        'sponsored_ranks' => 'array',
    ];

    /**
     * Get the folder that contains this keyword
     */
    public function folder()
    {
        return $this->belongsTo(CerebroFolder::class, 'folder_id');
    }
}
