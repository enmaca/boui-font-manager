<?php

namespace Enmaca\Backoffice\FontManager\Models;

use Illuminate\Database\Eloquent\Model;
use Uxmal\Backend\Models\Traits\HashUtils;

class Font extends Model
{
    use HashUtils;

    protected $table = 'fonts';

    protected $fillable = [
        'name',
        'active',
        'tags',
    ];

    public function variants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FontVariant::class);
    }

    /**
     * Get the categories associated with this font.
     */
    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(FontCategory::class, 'font_category_details', 'font_id', 'category_id');
    }
}
