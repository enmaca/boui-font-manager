<?php


namespace Enmaca\Backoffice\FontManager\Models;

use Illuminate\Database\Eloquent\Model;
use Uxmal\Backend\Models\Traits\HashUtils;

class GoogleFontTags extends Model
{
    use HashUtils;

    protected $table = 'google_font_tags';

    protected $fillable = [
        'name'
    ];

}
