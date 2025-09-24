<?php

namespace Enmaca\Backoffice\FontManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FontCategoryDetail extends Model
{
    protected $table = 'font_category_details';

    protected $fillable = [
        'font_id',
        'category_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the font that belongs to this category detail.
     */
    public function font(): BelongsTo
    {
        return $this->belongsTo(Font::class);
    }

    /**
     * Get the category that belongs to this category detail.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FontCategory::class, 'category_id');
    }
}