<?php


namespace Enmaca\Backoffice\FontManager\Models;

use Illuminate\Database\Eloquent\Model;
use Uxmal\Backend\Models\Traits\HashUtils;

class GoogleFontFamilies extends Model
{
    use HashUtils;

    protected $table = 'google_font_families';

    protected $fillable = [
        'family',
        'subsets',
        'category',
        'variable',
        'last_modified'
    ];



    public function defaultFile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(GoogleFontFiles::class, 'google_font_family_id')->latestCreated();
    }

    public function files(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GoogleFontFiles::class, 'google_font_family_id');
    }

    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(GoogleFontTags::class, 'google_font_family_tags', 'google_font_family_id', 'google_font_tag_id');
    }


}
