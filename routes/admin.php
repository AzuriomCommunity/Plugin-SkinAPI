<?php

use Azuriom\Plugin\SkinApi\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:skin-api.manage')->group(function () {
    Route::get('/skins', [AdminController::class, 'skins'])->name('skins');
    Route::post('/skins', [AdminController::class, 'updateSkins'])->name('skins.update');
    Route::get('/capes', [AdminController::class, 'capes'])->name('capes');
    Route::post('/capes', [AdminController::class, 'updateCapes'])->name('capes.update');
});
