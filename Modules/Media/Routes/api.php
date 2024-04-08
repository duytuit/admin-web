<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['jwt.auth']], function () {
    Route::group(['prefix' => 'admin/v1'], function () {
        Route::group(['prefix' => 'document'], function () {
            Route::get('/', "DocumentController@index")->name('document.index');
            Route::post('add', "DocumentController@add")->name('document.add');
            Route::get('show', "DocumentController@show")->name('document.show');
            Route::put('update', "DocumentController@update")->name('document.update');
            Route::delete('delete', "DocumentController@delete")->name('document.delete');
        });

        Route::group(['prefix'=>'document-app'], function () {
            Route::get('/', "DocumentAppController@index")->name('document.index');
            Route::post('add', "DocumentAppController@add")->name('document.add');
            Route::delete('delete', "DocumentAppController@delete")->name('document.delete');
        });
    });
});