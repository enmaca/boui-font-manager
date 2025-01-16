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
        'uri'
    ];

}
