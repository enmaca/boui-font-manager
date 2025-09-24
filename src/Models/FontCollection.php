<?php

namespace Enmaca\Backoffice\FontManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Uxmal\Backend\Models\Traits\HashUtils;

/**
 * Font Category model for managing font collections/categories.
 *
 * Represents a category or collection of fonts that can be used to organize
 * typography assets. Provides relationships to fonts and category details,
 * along with utility methods for font counting and management.
 *
 * @package Enmaca\Backoffice\FontManager\Models
 *
 * @property int $id Primary key
 * @property string $name Category name (unique)
 * @property string|null $description Category description
 * @property \Illuminate\Support\Carbon $created_at Creation timestamp
 * @property \Illuminate\Support\Carbon $updated_at Last update timestamp
 * @property string $hash Generated hash identifier (from HashUtils trait)
 * @property int $fonts_count Dynamic attribute for font count (when using withCount)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<\Enmaca\Backoffice\FontManager\Models\Font> $fonts
 * @property-read \Illuminate\Database\Eloquent\Collection<\Enmaca\Backoffice\FontManager\Models\FontCollectionDetail> $categoryDetails
 *
 * @method static \Illuminate\Database\Eloquent\Builder withCount(string ...$relations)
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $operator = null, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder find(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder findOrFail(int $id)
 */
class FontCollection extends Model
{
    use HashUtils;

    /** Database table name */
    protected $table = 'font_collections';

    /** Mass assignable attributes */
    protected $fillable = [
        'name',
        'description',
    ];

    /** Attribute casting definitions */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the fonts associated with this category.
     *
     * Many-to-many relationship through the font_category_details pivot table.
     * This allows fonts to be assigned to multiple categories and categories
     * to contain multiple fonts.
     *
     * @return BelongsToMany<Font> The fonts relationship
     */
    public function fonts(): BelongsToMany
    {
        return $this->belongsToMany(
            Font::class,
            'font_category_details',
            'category_id',
            'font_id'
        )->withTimestamps();
    }

    /**
     * Get the category details for this category.
     *
     * One-to-many relationship to the pivot table records. Useful for
     * accessing additional pivot data or managing relationships directly.
     *
     * @return HasMany<FontCollectionDetail> The category details relationship
     */
    public function categoryDetails(): HasMany
    {
        return $this->hasMany(FontCollectionDetail::class, 'collection_id');
    }

    /**
     * Get the count of fonts in this category.
     *
     * Dynamic attribute that counts the related fonts. More efficient to use
     * withCount('fonts') in queries to avoid N+1 problems.
     *
     * @return int Number of fonts assigned to this category
     */
    public function getFontsCountAttribute(): int
    {
        // Check if fonts_count was loaded via withCount()
        if (array_key_exists('fonts_count', $this->attributes)) {
            return (int) $this->attributes['fonts_count'];
        }

        // Fallback to counting the relationship (may cause N+1 queries)
        return $this->fonts()->count();
    }

    /**
     * Scope a query to include only categories with fonts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithFonts($query)
    {
        return $query->has('fonts');
    }

    /**
     * Scope a query to include only empty categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEmpty($query)
    {
        return $query->doesntHave('fonts');
    }

    /**
     * Check if this category has any fonts assigned.
     *
     * @return bool True if category has fonts, false otherwise
     */
    public function hasFonts(): bool
    {
        return $this->getFontsCountAttribute() > 0;
    }

    /**
     * Check if this category is empty (no fonts assigned).
     *
     * @return bool True if category is empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return !$this->hasFonts();
    }
}
