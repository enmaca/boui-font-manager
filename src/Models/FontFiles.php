<?php

namespace Enmaca\Backoffice\FontManager\Models;

use Illuminate\Database\Eloquent\Model;
use Uxmal\Backend\Models\Traits\HashUtils;

class FontFiles extends Model
{
    use HashUtils;

    protected $table = 'font_files';

    protected $fillable = [
        'font_variant_id',
        'version',
        'version_comments',
        'default',
        'original_name',
        'extension',
        'mime_type',
        'size',
        'uri',
        'format',
        'local',
    ];

    public function scopeFontId($query, $fontId)
    {
        $fontId = Font::normalizeId($fontId);
        return $query->whereHas('variant', function ($q) use ($fontId) {
            $q->where('font_id', $fontId);
        })->with('variant.font');
    }

    public function variant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FontVariant::class, 'font_variant_id');
    }

    public function scopeDefault($query)
    {
        return $query->where('default', true);
    }

    public function url(): string
    {
        return route('enmaca.font-manager.font.url', ['id' => $this->hash]).'?t='.time();
    }
}
