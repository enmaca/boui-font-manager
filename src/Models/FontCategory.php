<?php

namespace Enmaca\Backoffice\FontManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FontCategory extends Model
{
    protected $table = 'font_categories';

    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the fonts associated with this category.
     */
    public function fonts(): BelongsToMany
    {
        return $this->belongsToMany(Font::class, 'font_category_details', 'category_id', 'font_id');
    }

    /**
     * Get the category details for this category.
     */
    public function categoryDetails(): HasMany
    {
        return $this->hasMany(FontCategoryDetail::class, 'category_id');
    }

    /**
     * Get the count of fonts in this category.
     */
    public function getFontsCountAttribute(): int
    {
        return $this->fonts()->count();
    }
}