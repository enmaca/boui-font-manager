<?php

namespace Enmaca\Backoffice\FontManager\Models;

use Illuminate\Database\Eloquent\Model;
use Uxmal\Backend\Models\Traits\HashUtils;

class FontVariant extends Model
{
    use HashUtils;

    protected $table = 'font_variants';

    protected $fillable = [
        'font_id',
        'sub_family',
        'sub_family_id',
        'full_name',
        'version',
        'weight',
        'post_script_name',
        'copyright',
        'type',
    ];

    public function scopeFontId($query, $fontId)
    {
        return $query->where('font_id', $fontId);
    }

    public function font(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Font::class);
    }

    public function file(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FontFiles::class)->default();
    }
}
