<?php

/**
* Font Manager
*/

use Enmaca\Backoffice\FontManager\Controllers\TypographyCategoryController;
use Enmaca\Backoffice\FontManager\Controllers\TypographyCatalogController;

Route::get('/font-manager/catalog', TypographyCatalogController::class)
->name('enmaca.font-manager.catalog');
Route::get('/font-manager/category', TypographyCategoryController::class)
->name('enmaca.font-manager.category');
