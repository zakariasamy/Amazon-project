<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CerebroFolder extends Model
{
    use HasFactory;

    protected $table = 'cerebro_folders';

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'description',
        'keyword_count',
    ];

    protected $casts = [
        'keyword_count' => 'integer',
    ];

    /**
     * Get the user that owns the folder
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the keywords in this folder
     */
    public function keywords()
    {
        return $this->hasMany(CerebroFolderKeyword::class, 'folder_id');
    }

    /**
     * Update keyword count
     */
    public function updateKeywordCount()
    {
        $this->keyword_count = $this->keywords()->count();
        $this->save();
    }
}
