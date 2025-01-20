<?php


namespace Enmaca\Backoffice\FontManager\Models;

use Illuminate\Database\Eloquent\Model;
use Uxmal\Backend\Models\Traits\HashUtils;

class GoogleFontFiles extends Model
{
    use HashUtils;

    protected $table = 'google_font_files';

    protected $fillable = [
        'google_font_family_id',
        'google_font_variant_id',
        'remote_uri',
        'local_uri',
        'downloaded'
    ];

    public function scopeLatestCreated($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function family(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GoogleFontFamilies::class, 'google_font_family_id');
    }

    public function variant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GoogleFontVariants::class, 'google_font_variant_id');
    }
}
