<?php

use Azuriom\Plugin\SkinApi\Controllers\SkinApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your plugin. These
| routes are loaded by the RouteServiceProvider of your plugin within
| a group which contains the "web" middleware group and your plugin name
| as prefix. Now create something great!
|
*/

Route::get('/', [SkinApiController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    // Cape Management Routes
    Route::get('/capes', [SkinApiController::class, 'showCape'])->name('capes');
    Route::post('/capes/upload', [SkinApiController::class, 'uploadCape'])->name('capes.upload');
    Route::delete('/capes/delete', [SkinApiController::class, 'deleteCape'])->name('capes.delete');

    // Skin Management Routes
    Route::post('/skin/update', [SkinApiController::class, 'updateSkin'])->name('skin.update');
});
