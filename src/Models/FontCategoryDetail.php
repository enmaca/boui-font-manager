<?php

namespace Enmaca\Backoffice\FontManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Font Category Detail model for managing font-category relationships.
 *
 * Represents the pivot table records that link fonts to categories.
 * This model provides explicit access to the many-to-many relationship
 * data and allows for additional pivot table attributes if needed.
 *
 * @package Enmaca\Backoffice\FontManager\Models
 * 
 * @property int $id Primary key
 * @property int $font_id Foreign key to fonts table
 * @property int $category_id Foreign key to font_categories table
 * @property \Illuminate\Support\Carbon $created_at Creation timestamp
 * @property \Illuminate\Support\Carbon $updated_at Last update timestamp
 * 
 * @property-read \Enmaca\Backoffice\FontManager\Models\Font $font
 * @property-read \Enmaca\Backoffice\FontManager\Models\FontCategory $category
 * 
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $operator = null, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder whereFont(int $fontId)
 * @method static \Illuminate\Database\Eloquent\Builder whereCategory(int $categoryId)
 */
class FontCategoryDetail extends Model
{
    /** Database table name */
    protected $table = 'font_category_details';

    /** Mass assignable attributes */
    protected $fillable = [
        'font_id',
        'category_id',
    ];

    /** Attribute casting definitions */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the font that belongs to this category detail.
     *
     * Many-to-one relationship to the Font model. Each detail record
     * represents one font assigned to one category.
     *
     * @return BelongsTo<Font> The font relationship
     */
    public function font(): BelongsTo
    {
        return $this->belongsTo(Font::class);
    }

    /**
     * Get the category that belongs to this category detail.
     *
     * Many-to-one relationship to the FontCategory model. Each detail record
     * represents one category assignment for one font.
     *
     * @return BelongsTo<FontCategory> The category relationship
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FontCategory::class, 'category_id');
    }

    /**
     * Scope a query to only include details for a specific font.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $fontId The font ID to filter by
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereFont($query, int $fontId)
    {
        return $query->where('font_id', $fontId);
    }

    /**
     * Scope a query to only include details for a specific category.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $categoryId The category ID to filter by
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Get a summary string of this relationship.
     *
     * @return string A human-readable summary
     */
    public function getSummaryAttribute(): string
    {
        $fontName = $this->font?->name ?? 'Unknown Font';
        $categoryName = $this->category?->name ?? 'Unknown Category';
        
        return "{$fontName} → {$categoryName}";
    }
}