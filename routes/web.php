<?php

use Azuriom\Plugin\SkinApi\Controllers\MySkinController;
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

Route::middleware('auth')->group(function () {
    Route::get('/', [MySkinController::class, 'index'])->name('home');

    Route::post('/', [MySkinController::class, 'updateSkinCape'])->name('update');
    Route::delete('/skin', [MySkinController::class, 'deleteSkin'])->name('skin.delete');
    Route::delete('/cape', [MySkinController::class, 'deleteCape'])->name('cape.delete');
});
