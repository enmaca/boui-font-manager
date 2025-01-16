<?php


namespace Enmaca\Backoffice\FontManager\Models;

use Illuminate\Database\Eloquent\Model;
use Uxmal\Backend\Models\Traits\HashUtils;

class GoogleFontVariants extends Model
{
    use HashUtils;

    protected $table = 'google_font_variants';

    protected $fillable = [
        'name'
    ];

}
