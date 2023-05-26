<?php

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
    Route::get('/', 'SkinApiController@index')->name('home');
    Route::post('/update', 'SkinApiController@update')->name('update');
    Route::post('/reset', 'SkinApiController@update')->name('reset');
});
