<?php

use Enmaca\Backoffice\FontManager\Controllers\FontController;
use Illuminate\Support\Facades\Route;

Route::get('/font-manager/font/{id}', FontController::class.'@get')->name('enmaca.font-manager.font.url');
Route::put('/font-manager/font/{id}', FontController::class.'@put')->name('enmaca.font-manager.font.url.save');
