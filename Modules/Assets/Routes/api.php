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

//Route::middleware('auth:api')->get('/assets', function (Request $request) {
//    return $request->user();
//});

Route::prefix('admin/v1')->name('api.v1.')->group(function () {
    Route::group(['middleware' => ['jwt.auth']], function () {
        Route::group(['prefix'=>'maintenance-asset'], function () {
            Route::get('/', "MaintenanceAssetsController@index")->name('maintenance-asset.index');
            Route::post('add', "MaintenanceAssetsController@add")->name('maintenance-asset.add');
            Route::get('show', "MaintenanceAssetsController@show")->name('maintenance-asset.show');
            Route::put('update', "MaintenanceAssetsController@update")->name('maintenance-asset.update');
            Route::put('update-status', "MaintenanceAssetsController@updateStatus")->name('maintenance-asset.updateStatus');
            Route::delete('delete', "MaintenanceAssetsController@delete")->name('maintenance-asset.delete');
        });

        Route::group(['prefix'=>'area'], function () {
            Route::get('/', "AreasController@index")->name('area.index');
            Route::post('add', "AreasController@add")->name('area.add');
            Route::get('show', "AreasController@show")->name('area.show');
            Route::put('update', "AreasController@update")->name('area.update');
            Route::delete('delete', "AreasController@delete")->name('area.delete');
        });

        Route::group(['prefix'=>'asset'], function () {
            Route::get('/', "AssetsController@index")->name('asset.index');
            Route::post('add', "AssetsController@add")->name('asset.add');
            Route::get('show', "AssetsController@show")->name('asset.show');
            Route::put('update', "AssetsController@update")->name('asset.update');
            Route::delete('delete', "AssetsController@delete")->name('asset.delete');

            Route::post('testUploadImage', "AssetsController@testUploadImage")->name('asset.testUploadImage');
        });

        Route::group(['prefix'=>'asset-category'], function () {
            Route::get('/', "AssetCategoriesController@index")->name('asset-category.index');
            Route::post('add', "AssetCategoriesController@add")->name('asset-category.add');
            Route::get('show', "AssetCategoriesController@show")->name('asset-category.show');
            Route::put('update', "AssetCategoriesController@update")->name('asset-category.update');
            Route::delete('delete', "AssetCategoriesController@delete")->name('asset-category.delete');
        });
        Route::group(['prefix'=>'admin'], function () {
            Route::get('/list', "AdminsController@getList")->name('admin.getList');
            Route::get('/filter-phone', "AdminsController@filterPhone")->name('admin.filterPhone');
            Route::get('/department', "AdminsController@department")->name('admin.department');
        });
    });
});