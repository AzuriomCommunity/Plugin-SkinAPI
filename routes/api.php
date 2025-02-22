<?php

use Azuriom\Plugin\SkinApi\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

// Skins
Route::get('/skins/{user}.png', [ApiController::class, 'skin'])->name('show');
Route::get('/skins/{user}', [ApiController::class, 'skin']);
Route::get('/avatars/{type}/{user}.png', [ApiController::class, 'avatar'])->name('showAvatar');
Route::get('/avatars/{type}/{user}', [ApiController::class, 'avatar']);
Route::post('/skins', [ApiController::class, 'updateSkin'])->name('update');
Route::post('/skins/update', [ApiController::class, 'updateSkin']);
Route::delete('/skins', [ApiController::class, 'deleteSkin'])->name('delete');

// Capes
Route::get('/capes/{user}.png', [ApiController::class, 'cape'])->name('cape');
Route::get('/capes/{user}', [ApiController::class, 'cape']);
Route::post('/capes', [ApiController::class, 'updateCape'])->name('capes.update');
Route::delete('/capes', [ApiController::class, 'deleteCape'])->name('capes.delete');
