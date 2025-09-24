<?php

namespace Enmaca\Backoffice\FontManager\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class FontCollectionDetail
 *
 * Model representing the pivot table linking fonts to font collections (categories).
 * Enables management of font-category relationships, supporting additional pivot attributes if needed.
 *
 * @package Enmaca\Backoffice\FontManager\Models
 *
 * @property int $id Primary key
 * @property int $font_id Foreign key referencing fonts table
 * @property int $collection_id Foreign key referencing font_collections table
 * @property Carbon $created_at Timestamp when the record was created
 * @property Carbon $updated_at Timestamp when the record was last updated
 *
 * @property-read Font $font The related font model
 * @property-read FontCollection $category The related font collection (category) model
 *
 * @method static Builder where(string $column, mixed $operator = null, mixed $value = null) Filter query by column
 * @method static Builder whereFont(int $fontId) Scope: filter by font ID
 * @method static Builder whereCategory(int $categoryId) Scope: filter by category ID
 */
class FontCollectionDetail extends Model
{
    /**
     * @var string $table
     * The database table associated with the model.
     */
    protected $table = 'font_collection_details';

    /**
     * @var array $fillable
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'font_id',
        'collection_id',
    ];

    /**
     * @var array $casts
     * Attribute casting definitions for date/time fields.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the font associated with this detail record.
     *
     * Defines a many-to-one relationship to the Font model.
     * Each detail record links a font to a collection.
     *
     * @return BelongsTo<Font>
     */
    public function font(): BelongsTo
    {
        return $this->belongsTo(Font::class);
    }

    /**
     * Get the font collection (category) associated with this detail record.
     *
     * Defines a many-to-one relationship to the FontCollection model.
     * Each detail record links a font to a specific collection.
     *
     * @return BelongsTo<FontCollection>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FontCollection::class, 'collection_id');
    }

    /**
     * Scope: Filter query to include only details for a specific font.
     *
     * @param Builder $query The Eloquent query builder
     * @param int $fontId The font ID to filter by
     * @return Builder
     */
    public function scopeWhereFont(Builder $query, int $fontId): Builder
    {
        return $query->where('font_id', $fontId);
    }

    /**
     * Scope: Filter query to include only details for a specific collection.
     *
     * @param Builder $query The Eloquent query builder
     * @param int $categoryId The collection ID to filter by
     * @return Builder
     */
    public function scopeWhereCollection($query, int $categoryId): Builder
    {
        return $query->where('collection_id', $categoryId);
    }

    /**
     * Accessor: Get a human-readable summary of the font-category relationship.
     *
     * @return string Summary string in the format "Font Name → Category Name"
     */
    public function getSummaryAttribute(): string
    {
        $fontName = $this->font?->name ?? 'Unknown Font';
        $categoryName = $this->category?->name ?? 'Unknown Category';

        return "{$fontName} → {$categoryName}";
    }
}
