<?php

use Illuminate\Support\Facades\Route;

Route::middleware('can:skin-api.manage')->group(function () {
    Route::get('/', 'AdminController@index')->name('index');
    Route::post('/', 'AdminController@update')->name('update');
    
    Route::get('/skins', 'AdminController@skins')->name('skins');
    Route::post('/skins', 'AdminController@updateSkins')->name('skins.update');
    
    Route::get('/capes', 'AdminController@capes')->name('capes');
    Route::post('/capes', 'AdminController@updateCapes')->name('capes.update');
});
