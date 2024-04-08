<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

use App\Models\Campain;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('maintenance')->name('admin.')->group(function () {
    // Unauthenticated
    Route::namespace('Auth\Admin')->name('auth.')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('login', 'LoginController@showLoginForm')->name('form');
            Route::post('login', 'LoginController@login')->name('login');
            Route::get('logout', 'LoginController@logout')->name('logout');
        });
    });

    // dev
    Route::get('/dev', 'Dev\DevController@index');
    Route::get('/dev/reset', 'Dev\DevController@reset');
    Route::get('/dev/update_log', 'Dev\DevController@update_log');
    Route::get('/dev/checkmaxID', 'Dev\DevController@checkmaxID');
    Route::get('/dev/checkstatus_program', 'Dev\DevController@checkstatus_program');
    Route::get('/dev/checkIDmax', 'Dev\DevController@checkIDmax');
    Route::get('/dev/request_query', 'Dev\DevController@request_query');
    Route::get('/dev/checkIDmax_monthly_ticket', 'Dev\DevController@checkIDmax_monthly_ticket');
    Route::get('/dev/updateDebit', 'Dev\DevController@updateDebit');
    Route::get('/dev/Transfer_Exportdetail', 'Dev\DevController@Transfer_Exportdetail');
    Route::get('/dev/Transfer_countvehicle', 'Dev\DevController@Transfer_countvehicle');
    Route::get('/dev/Tranfer_RegisterMonthlyTicket', 'Dev\DevController@Tranfer_RegisterMonthlyTicket');
    Route::get('/dev/Transfer_event', 'Dev\DevController@Transfer_event');
    Route::get('/dev/update_electricmeter', 'Dev\DevController@update_electricmeter');
    Route::get('/dev/updatePaidByCycleNameFromReceipt_DEV', 'Dev\DevController@updatePaidByCycleNameFromReceipt_DEV');
    Route::get('/dev/resetDebit', 'Dev\DevController@resetDebit');
    Route::get('/dev/updateBill', 'Dev\DevController@updateBill');
    Route::get('/dev/resetBill', 'Dev\DevController@resetBill');
    Route::get('/dev/testPush', 'Dev\DevController@testPush');
    Route::get('/dev/clearLog', 'Dev\DevController@clearLog');
    Route::get('/dev/duong_custom', 'Dev\DevController@duong_custom');
    Route::get('/dev/sqlquerySelect', 'Dev\DevController@sqlquerySelect');
    Route::get('/dev/addLog', 'Dev\DevController@addLog');
    Route::get('/dev/viewLog', 'Dev\DevController@viewLog');
    Route::get('/dev/test1', 'Dev\DevController@test1');
    Route::get('/dev/test2', 'Dev\DevController@test2');
    Route::get('/dev/cleanCache', 'Dev\DevController@cleanCache');
    Route::get('/dev/runStat', 'Dev\DevController@runStat');
    Route::get('/dev/updatePaidCoin', 'Dev\DevController@updatePaidCoin');
    Route::get('/dev/updateDebit2', 'Dev\DevController@updateDebit2');
    Route::get('/dev/viewQueue', 'Dev\DevController@viewQueue');
    Route::get('/dev/delKey', 'Dev\DevController@delKey');
    Route::get('/dev/delKey2', 'Dev\DevController@delKey2');
    Route::get('/dev/viewKeyRedis', 'Dev\DevController@viewKeyRedis');
    Route::get('/dev/viewKeyRedis2', 'Dev\DevController@viewKeyRedis2');
    Route::get('/dev/Confirm_id_Vehicle', 'Dev\DevController@Confirm_id_Vehicle');
//    Route::get('/dev/deletePayment', 'Dev\DevController@deletePayment');
//    Route::get('/dev/deletePayment2', 'Dev\DevController@deletePayment2');
    Route::get('/dev/userConvert', 'Dev\DevController@userConvert');
//    Route::get('/dev/convertDiscountPayment', 'Dev\DevController@convertDiscountPayment');
//    Route::get('/dev/delLogPay', 'Dev\DevController@delLogPay');
    Route::get('/dev/xoanophatsinh', 'Dev\DevController@xoanophatsinh');
//    Route::get('/dev/xoanophatsinhle', 'Dev\DevController@xoanophatsinhle');
    Route::get('/dev/convertPayment', 'Dev\DevController@convertPayment');
//    Route::get('/dev/convertDiscountPaymentForce', 'Dev\DevController@convertDiscountPaymentForce');
//    Route::get('/dev/convertDiscountPaymentForceNotPaySuccess', 'Dev\DevController@convertDiscountPaymentForceNotPaySuccess');
    Route::get('/dev/forceDiscount', 'Dev\DevController@forceDiscount');
    Route::get('/dev/convertDiscountNew', 'Dev\DevController@convertDiscountNew');
    Route::get('/dev/customPayment', 'Dev\DevController@customPayment');
    Route::get('/dev/viewAllPaymentDebit', 'Dev\DevController@viewAllPaymentDebit');
    Route::get('/dev/LogCoinCus', 'Dev\DevController@LogCoinCus');
    Route::get('/dev/pushStat', 'Dev\DevController@pushStat');
    Route::get('/dev/handleDupAutoPayment', 'Dev\DevController@handleDupAutoPayment');
    Route::get('/dev/checkConvertUser', 'Dev\DevController@checkConvertUser');
    Route::get('/dev/getMoreAccount', 'Dev\DevController@getMoreAccount');
    Route::get('/dev/exportUserApartment', 'Dev\DevController@exportUserApartment');
    Route::get('/dev/clearUserConvert', 'Dev\DevController@clearUserConvert');
    Route::get('/dev/customCoin', 'Dev\DevController@customCoin');
    Route::get('/dev/handleBackAutoPayment', 'Dev\DevController@handleBackAutoPayment');
    Route::get('/dev/handleFixPaymentDelete', 'Dev\DevController@handleFixPaymentDelete');
    Route::get('/dev/handleFixDebitDelete', 'Dev\DevController@handleFixDebitDelete');
    Route::get('/dev/showDelDupUser', 'Dev\DevController@showDelDupUser');
    Route::get('/dev/handleDelUser', 'Dev\DevController@handleDelUser');
    Route::get('/dev/pushCreateReceipt', 'Dev\DevController@pushCreateReceipt');
    Route::get('/dev/warningPaymentShow', 'Dev\DevController@warningPaymentShow');
    Route::get('/dev/paymentShow', 'Dev\DevController@paymentShow');
    Route::get('/dev/runStatWarning', 'Dev\DevController@runStatWarning');
    Route::get('/dev/pushStatByDay', 'Dev\DevController@pushStatByDay');
    Route::get('/dev/delRecieptConvertLogCoin', 'Dev\DevController@delRecieptConvertLogCoin');
//    Route::get('/dev/handleFixUser', 'Dev\DevController@handleFixUser');
//    Route::get('/dev/delPayError', 'Dev\DevController@delPayError');

    Route::middleware(['auth:backend_public', 'route_permision'])->group(function () {

        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'Backend\HomeController@index')->name('home');
        });
        Route::namespace('V3')->name('v3.')->prefix('v3')->group(function () {
            Route::prefix("document")->name("document.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', "DocumentController@index")->name("index");
                    Route::get('/get_list_apartment', "DocumentController@get_list_apartment")->name("get_list_apartment");
                    Route::get('/get_list_apartment_group', "DocumentController@get_list_apartment_group")->name("get_list_apartment_group");
                    Route::post('/show', 'DocumentController@show')->name('show');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/', "DocumentController@store")->name("store");
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('/update', "DocumentController@update")->name("update");
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::post('/delete', "DocumentController@delete")->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                });

            });

            Route::prefix("assets")->name("assets.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', "AssetController@index")->name("index");
                    Route::get('/create', "AssetController@create")->name("create");
                    Route::get('/{asset_id}/edit', "AssetController@edit")->name("edit");
                    Route::get('/{asset_id}/detail', 'AssetController@detail')->name('detail');
                    Route::get('/{asset_id}/show', "AssetController@show")->name("show");
                    Route::get('/download', "AssetController@download")->name('download');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/', "AssetController@store")->name("store");
                });
                Route::group(['permission' => 'update'], function () {
                    Route::put('/{asset_id}', "AssetController@update")->name("update");
                    Route::post('/update-maintain', "AssetController@updateMaintain")->name('updateMaintain');
                    Route::post('/addMaintainDate', "AssetController@addMaintainDate")->name('addMaintainDate');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::post('/delete/{asset_id}', "AssetController@destroy")->name('destroy');
                    Route::post('/delete', "AssetController@delete")->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                    Route::post('/importAssets', 'AssetController@importAssets')->name('importAssets');
                    Route::get('/importexcel', "AssetController@importexcel")->name('importexcel');
                    Route::get('/importexceldetail', "AssetController@importexcel_asset_detail")->name('importexceldetail');
                });
                Route::group(['permission' => 'export'], function () {
                });

            });

            Route::prefix("asset-area")->name("asset-area.")->group(function () {

                Route::group(['permission' => 'view'], function () {
                    Route::get('/', "AssetAreaController@index")->name("index");
                    Route::get('/create', "AssetAreaController@create")->name("create");
                    Route::get('/{asset_id}/edit', "AssetAreaController@edit")->name("edit");
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/', "AssetAreaController@store")->name("store");
                });
                Route::group(['permission' => 'update'], function () {
                    Route::put('/{asset_id}', "AssetAreaController@update")->name("update");
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::post('/delete/{asset_id}', "AssetAreaController@destroy")->name('destroy');
                    Route::post('/delete', "AssetAreaController@delete")->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                });

            });

            Route::prefix("asset-category")->name("asset-category.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', "AssetCategoryController@index")->name("index");
                    Route::get('/create', "AssetCategoryController@create")->name("create");
                    Route::get('/{asset_id}/edit', "AssetCategoryController@edit")->name("edit");
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/', "AssetCategoryController@store")->name("store");
                });
                Route::group(['permission' => 'update'], function () {
                    Route::put('/{asset_id}', "AssetCategoryController@update")->name("update");
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::post('/delete/{asset_id}', "AssetCategoryController@destroy")->name('destroy');
                    Route::post('/delete', "AssetCategoryController@delete")->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                });

            });
            //Smart Car Packing

            Route::prefix("vehicles")->name('vehicles.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/totalinout', "VehiclesController@totalinout")->name('totalinout');
                    Route::get('/realtimeinout', "VehiclesController@realtimeinout")->name("realtimeinout");
                    Route::get('/report', "VehiclesController@report")->name("report");
                });
            });
            //end

            Route::prefix("maintenance-asset")->name('maintenance-asset.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', "MaintenanceAssetController@index")->name('index');
                    Route::get('/detail/{asset_id}', "MaintenanceAssetController@detail")->name("detail");
                    Route::post('/action', 'MaintenanceAssetController@action')->name('action');
                });
                Route::group(['permission' => 'insert'], function () {
                });
                Route::group(['permission' => 'update'], function () {
                });
                Route::group(['permission' => 'delete'], function () {
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('/exportMonth', "MaintenanceAssetController@exportMonth")->name("exportMonth");
                    Route::get('/exportList', "MaintenanceAssetController@exportList")->name("exportList");
                });
            });

        });

        // Unauthenticated
        Route::namespace('Users')->name('users.')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('manage-user', 'UserController@index')->name('manageUser');
                Route::get('manage-user-app', 'UserController@index_user')->name('manageUserApp');
                Route::post('/action', 'UserController@action')->name('action');
                Route::get('manage-user/sendmail', 'UserController@sendMailBills')->name('sendmailbill');
                Route::get('manage-user/create', 'UserController@create')->name('create');
                Route::get('manage-user/create1', 'UserController@create1')->name('create1');
                Route::get('manage-user-app/edit/{id}', 'UserController@edit')->name('edit');
                Route::get('manage-user-app/update/{id}', 'UserController@update')->name('update');
                Route::get('manage-user/update/{id}', 'UserController@restoreUser')->name('restoreUser');
                Route::get('manage-user/permission/{id}', 'UserController@listPermission')->name('permission');
                Route::get('/getSelectGroup', 'UserController@ajaxGetSelectGroup')->name('ajaxGetSelectGroup');
                Route::get('/changeisadmin', 'UserController@ajaxChangeIsAdmin')->name('ajaxChangeIsAdmin');
                Route::get('/getChangeSelectBuilding', 'UserController@updateUserWithBuilding')->name('updateUserWithBuilding');
                Route::get('manage-user/delete/{id}', 'UserController@destroyUser')->name('destroy');
                Route::get('manage-user-app/delete/{id}', 'UserController@destroyUserApp')->name('destroyUserApp');
                Route::get('manage-user/deleteprofile/{id}', 'UserController@destroyProfile')->name('destroyprofile');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('manage-user/create', 'UserController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('manage-user/change-status', 'UserController@changeStatus')->name('change-status');
                Route::post('manage-user/permission/{id}', 'UserController@listPermission')->name('permission');
                Route::post('manage-user/permission/{id}/update', 'UserController@updatePermission')->name('permission.update');
                Route::post('manage-user/reset-pass', 'UserController@ResetPassUser')->name('reset-pass');
                Route::get('/getChangeSelectGroup', 'UserController@updateGroupPermission')->name('updateGroupPermission');
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });

        Route::group(['permission' => 'view'], function () {
            Route::get('/system/menu/index', ['as' => 'system.menu.index', 'uses' => 'Menu\MenuController@index']);
            Route::get('/system/menu/create', ['as' => 'system.menu.create', 'uses' => 'Menu\MenuController@create']);
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('/system/menu/store', ['as' => 'system.menu.store', 'uses' => 'Menu\MenuController@store']);
        });


        Route::prefix('assets')->name('assets.')->namespace('Assets')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'AssetController@index')->name('index');
                Route::get('/create', 'AssetController@create')->name('create');
                Route::get('/edit/{id}', 'AssetController@edit')->name('edit');
                Route::get('/{id}', 'AssetController@show')->name('show');
                Route::post('/action', 'AssetController@action')->name('action');
                Route::get('/maintain_check/{mainId}', 'AssetController@checkDoneMaintain')->name('maintain_check');
                Route::get('/cancel_check/{mainId}', 'AssetController@cancelCheck')->name('cancel_check');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/', 'AssetController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::put('/edit/{id}', 'AssetController@update')->name('update');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::delete('/destroy/{id}', 'AssetController@destroy')->name('destroy');
                Route::post('/delete-multi', 'AssetController@deleteMulti')->name('deleteMulti');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });
        Route::prefix('fcms')->name('fcms.')->namespace('Fcm')->group(function () {
            Route::get('/device', 'FcmController@ajaxAddDevice')->name('device');
        });

        /**
         *
         * Price Type
         *
         */
        Route::prefix('pricetype')->name('pricetype.')->namespace('BdcPriceType')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'PriceTypeController@index')->name('index');
                Route::get('/create', 'PriceTypeController@create')->name('create');
                Route::get('/edit/{id}', 'PriceTypeController@edit')->name('edit');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/store', 'PriceTypeController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::put('/update/{id}', 'PriceTypeController@update')->name('update');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::delete('/delete/{id}', 'PriceTypeController@delete')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });

        });
        /**
         *
         * End Price Type
         *
         */

        /**
         *
         * Progressive Price
         *
         */
        Route::prefix('progressive-price')->name('progressiveprice.')->namespace('BdcProgressivePrice')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'BdcProgressivePriceController@index')->name('index');
                Route::get('/create', 'BdcProgressivePriceController@create')->name('create');
                Route::get('/edit/{id}', 'BdcProgressivePriceController@edit')->name('edit');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/store', 'BdcProgressivePriceController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::put('/update/{id}', 'BdcProgressivePriceController@update')->name('update');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::delete('/delete/{id}', 'BdcProgressivePriceController@delete')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });

        });
        /**
         *
         * End Progressive Price
         *
         */

        /**
         *
         * Progressive
         *
         */
        Route::prefix('progressive')->name('progressive.')->namespace('BdcProgressives')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'BdcProgressiveController@index')->name('index');
                Route::get('/create', 'BdcProgressiveController@create')->name('create');
                Route::get('/edit/{id}', 'BdcProgressiveController@edit')->name('edit');
                Route::get('/import-excel', 'BdcProgressiveController@importExcel')->name('importexcel');
                Route::get('/import-phi-dau-ky', 'BdcProgressiveController@importExcelPhiDauKy')->name('importexcelphidauky');
                Route::get('/download', 'BdcProgressiveController@download')->name('download');
                Route::get('/download-phi-dau-ky', 'BdcProgressiveController@downloadphidauky')->name('downloadphidauky');
                Route::post('/action', 'BdcProgressiveController@action')->name('action');
                Route::get('/import-cong-no', 'BdcProgressiveController@importServiceApartment')->name('importServiceApartment');
                Route::get('/download_tool_template', 'BdcProgressiveController@download_tool_template')->name('downloadtoolcongno');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/store', 'BdcProgressiveController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::put('/update/{id}', 'BdcProgressiveController@update')->name('update');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::delete('/delete/{id}', 'BdcProgressiveController@delete')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
                Route::post('/import-phi-dau-ky-post', 'BdcProgressiveController@importFileExcelPhiDauKyPost')->name('importexcelphidaukypost');
                Route::post('/import-excel-post', 'BdcProgressiveController@importFileExcelPost')->name('importexcelpost');
                Route::post('/import-cong-no-post', 'BdcProgressiveController@import_excel_service_post')->name('import_excel_service_post');
            });
            Route::group(['permission' => 'export'], function () {
            });

        });
        /**
         *
         * End Progressive
         *
         */

        //service-company
        Route::prefix('service-company')->name('service.company.')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'Service\ServiceCompanyController@index')->name('index');
                Route::post('/', 'Service\ServiceCompanyController@index')->name('index');
                Route::get('/create', 'Service\ServiceCompanyController@create')->name('create');
                Route::get('/edit/{id}', 'Service\ServiceCompanyController@edit')->name('edit');
                Route::get('/choose', 'Service\ServiceCompanyController@choose')->name('choose');
                Route::get('/get-progressive', 'Service\ServiceCompanyController@getProgressive')->name('getProgressive');
                Route::post('/action', 'Service\ServiceCompanyController@action')->name('action');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/store', 'Service\ServiceCompanyController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::put('/update/{id}', 'Service\ServiceCompanyController@update')->name('update');
                Route::put('/change-status', 'Service\ServiceCompanyController@changeStatus')->name('status');
                Route::post('/post-choose', 'Service\ServiceCompanyController@postChoose')->name('postChoose');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::delete('/destroy/{id}', 'Service\ServiceCompanyController@destroy')->name('destroy');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });

        });

        //service apartment
        Route::prefix('service-apartment')->name('service.apartment.')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'Service\ServiceApartmentController@index')->name('index');
                Route::post('/', 'Service\ServiceApartmentController@index')->name('filter');
                Route::get('/create', 'Service\ServiceApartmentController@create')->name('create');
                Route::get('/edit/{id}', 'Service\ServiceApartmentController@edit')->name('edit');
                Route::get('/show/{id}', 'Service\ServiceApartmentController@show')->name('show');
                Route::post('/action', 'Service\ServiceApartmentController@action')->name('action');
                Route::post('get-vehicle/', 'Service\ServiceApartmentController@getVehicleApartment')->name('getVehicle');
                Route::post('get-progress/', 'Service\ServiceApartmentController@getProgressApartment')->name('getProgressApartment');
                Route::post('get-service/', 'Service\ServiceApartmentController@getServiceApartmentAjax')->name('getServiceApartmentAjax');
                Route::get('/ajax_get_service', 'Service\ServiceApartmentController@ajaxGetSelectService')->name('ajax_get_service');
                Route::get('/ajaxGetSelectBuildings', 'Service\ServiceApartmentController@ajaxGetSelectBuildings')->name('ajaxGetSelectBuildings');
                Route::get('/ajaxGetSelectInspecter', 'Service\ServiceApartmentController@ajaxGetSelectInspecter')->name('ajaxGetSelectInspecter');
                Route::get('/ajaxGetSelectBuildingsOff', 'Service\ServiceApartmentController@ajaxGetSelectBuildingsOff')->name('ajaxGetSelectBuildingsOff');
                Route::get('/ajaxGetSelectBuildingsOn', 'Service\ServiceApartmentController@ajaxGetSelectBuildingsOn')->name('ajaxGetSelectBuildingsOn');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/store', 'Service\ServiceApartmentController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::put('/update/{id}', 'Service\ServiceApartmentController@update')->name('update');
                Route::put('/change-status', 'Service\ServiceApartmentController@changeStatus')->name('status');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::get('/destroy/{id}', 'Service\ServiceApartmentController@destroy')->name('destroy');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('export', 'Service\ServiceApartmentController@export')->name('export');
            });

        });

        //service-building
        Route::prefix('service-building')->name('service.building.')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'Service\ServiceBuildingController@index')->name('index');
                Route::get('/ajaxSelectTypeService', 'Service\ServiceBuildingController@ajaxSelectTypeService')->name('ajaxSelectTypeService');
                Route::post('/', 'Service\ServiceBuildingController@index')->name('filter');
                Route::get('/create', 'Service\ServiceBuildingController@create')->name('create');
                Route::get('/edit/{id}', 'Service\ServiceBuildingController@edit')->name('edit');
                Route::get('/choose', 'Service\ServiceBuildingController@choose')->name('choose');
                Route::get('/import-excel', 'Service\ServiceBuildingController@importExcel')->name('importexcel');
                Route::get('/download', 'Service\ServiceBuildingController@download')->name('download');
                Route::get('/category', 'Service\ServiceBuildingController@indexCategory')->name('indexCategory');
                Route::get('/check_index_accounting', 'Service\ServiceBuildingController@check_index_accounting')->name('check_index_accounting');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/store', 'Service\ServiceBuildingController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::put('/update/{id}', 'Service\ServiceBuildingController@update')->name('update');
                Route::put('/change-status', 'Service\ServiceBuildingController@changeStatus')->name('status');
                Route::post('/post-choose', 'Service\ServiceBuildingController@postChoose')->name('postChoose');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::get('/destroy/{id}', 'Service\ServiceBuildingController@destroy')->name('destroy');
                Route::post('/update_index_accounting', 'Service\ServiceBuildingController@update_index_accounting')->name('update_index_accounting');
                Route::post('/set-type-tinh-cong-no', 'Service\ServiceBuildingController@set_type_tinh_cong_no')->name('set_type_tinh_cong_no');
            });
            Route::group(['permission' => 'import'], function () {
                Route::post('/importApartmentService', 'Service\ServiceBuildingController@importApartmentService')->name('importApartmentService');
            });
            Route::group(['permission' => 'export'], function () {
            });

        });
        Route::middleware('route_accountant')->group(function () {
            //debit
            Route::prefix('debit')->name('debit.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'Debit\DebitController@index')->name('index');
                    Route::post('/', 'Debit\DebitController@index')->name('index');
                    Route::get('detail/', 'Debit\DebitController@detail')->name('detail');
                    Route::get('/detail-v2', 'Debit\DebitController@detailVersion2')->name('detailVersion2');
                    Route::post('detail/action', 'Debit\DebitController@action')->name('detail.action');
                    Route::post('get-apartment', 'Debit\DebitController@getApartment')->name('getApartment');
                    Route::get('show/{id}', 'Debit\DebitController@show')->name('show');
                    Route::get('exportShow/{id}', 'Debit\DebitController@exportDetailShowApartment')->name('exportShow');
                    Route::get('processDebitDetail', 'Debit\DebitController@processDebitDetail')->name('processDebitDetail');
                    Route::get('reloadProcessDebitDetail', 'Debit\DebitController@reloadProcessDebitDetail')->name('reloadProcessDebitDetail');
                    Route::get('detail-service', 'Debit\DebitController@detailDebit')->name('detailDebit');
                    Route::get('detail-service-action', 'Debit\DebitController@detailDebitActionRecord')->name('detail_service_action');
                    Route::post('detail-service/edit', 'Debit\DebitController@detailDebitEdit')->name('detailDebit.edit');
                    Route::get('{id?}/delete', 'Debit\DebitController@destroydebitDetail')->name('detailDebit.delete');
                    Route::get('{id?}/delete-version', 'Debit\DebitController@destroydebitDetailV2')->name('detailDebit.delete.version');
                    Route::get('detail-service/edit/version', 'Debit\DebitController@detailDebitEditVersion')->name('detailDebit.edit.version');
                    Route::post('detail-service/action', 'Debit\DebitController@detailDebitAction')->name('detail-service.action');
                    Route::post('detail-service/action-record', 'Debit\DebitController@ActionRecordDebit')->name('detail-service.action_record');
                    Route::get('/debit-logs', 'Debit\DebitController@debitLogs')->name('debitLogs');
                    Route::get('/total', 'Debit\DebitController@total')->name('total');
                    Route::get('/general-detail', 'Debit\DebitController@generalDetail')->name('generalDetail');
                    Route::post('/send-message', 'Debit\DebitController@sendMessage')->name('sendMessage');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('detail-handling', 'Debit\DebitController@detailHandling')->name('detail-handling');
                    Route::post('detail-handling-year', 'Debit\DebitController@detailHandlingYear')->name('detail-handling-year');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::put('detail-service/update/{id}', 'Debit\DebitController@detailDebitUpdate')->name('detail-service.update');
                });
                Route::group(['permission' => 'delete'], function () {
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('export', 'Debit\DebitController@export')->name('export');
                    Route::get('exportFilter', 'Debit\DebitController@exportFilter')->name('exportFilter');
                    Route::get('export-excel', 'Debit\DebitController@exportExcel')->name('exportExcel');
                    Route::get('export-meter-water', 'Debit\DebitController@export_meter_water')->name('export_meter_water');
                    Route::get('/export-excel-total', 'Debit\DebitController@exportExcelTotal')->name('exportExcelTotal');
                    Route::get('/export-excel-general-detail', 'Debit\DebitController@exportExcelGeneralDetail')->name('exportExcelGeneralDetail');
                });

            });
            //bill
            Route::prefix('bill')->name('bill.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'Bill\BillController@index')->name('index');
                    Route::get('/wait-for-confirm', 'Bill\BillController@waitForConfirm')->name('waitForConfirm');
                    Route::get('/wait-for-confirm-edit-dateline', 'Bill\BillController@waitForConfirmEditDateline')->name('waitForConfirmEditDateline');
                    Route::get('/wait-to-send', 'Bill\BillController@waitToSend')->name('waitToSend');
                    Route::get('/list-pay', 'Bill\BillController@listPay')->name('listPay');
                    Route::get('show/{id}', 'Bill\BillController@show')->name('show');
                    Route::get('export', 'Bill\BillController@export')->name('export');
                    Route::get('/reload-pdf', 'Bill\BillController@reloadPdf')->name('reloadPdf');
                    Route::post('/action', 'Bill\BillController@reloadpdfv2')->name('reloadpdfv2');
                    Route::get('delete', 'Bill\BillController@delete')->name('delete');
                });
                Route::group(['permission' => 'insert'], function () {
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('change-status', 'Bill\BillController@changeStatus')->name('changeStatus');
                    Route::post('post-change-status', 'Bill\BillController@postChangeStatus')->name('postChangeStatus');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::get('destroy/{id}', 'Bill\BillController@destroyBill')->name('destroyBill');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('exportFilter', 'Bill\BillController@exportFilter')->name('exportFilter');
                    Route::get('exportFilterBangKeKhachHang', 'Bill\BillController@exportFilterBangKeKhachHang')->name('exportFilterBangKeKhachHang');
                });

            });
            //receipt
            Route::prefix('receipt')->name('receipt.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'Receipt\ReceiptController@index')->name('index');
                    Route::get('/kyquy', 'Receipt\ReceiptController@kyquy')->name('kyquy');
                    Route::get('create', 'Receipt\ReceiptController@create')->name('create');
                    Route::get('create-old', 'Receipt\ReceiptController@create_old')->name('create_old');
                    Route::get('create-phieu-chi', 'Receipt\ReceiptController@createPhieuChi')->name('createPhieuChi');
                    Route::get('/{id}/show', 'Receipt\ReceiptController@show')->name('show');
                    Route::get('pdf', 'Receipt\ReceiptController@exportPDF')->name('exportPDF');
                    Route::get('create-receipt-previous', 'Receipt\ReceiptController@createReceiptPrevious')->name('create_receipt_previous');
                    Route::get('edit/{id}', 'Receipt\ReceiptController@edit')->name('edit');
                    Route::get('demo', 'Receipt\ReceiptController@demo')->name('demo');
                    Route::get('reload-pdf/{id}', 'Receipt\ReceiptController@reload_pdf')->name('reload_pdf');
                    Route::get('getReceipt/{code}', 'Receipt\ReceiptController@view_receipt')->name('receiptCode');
                    Route::get('edit-bill/{id}', 'Receipt\ReceiptController@editBill')->name('editBill');
                    Route::get('edit_create_date', 'Receipt\ReceiptController@edit_create_date')->name('edit_create_date');
                });
                Route::group(['permission' => 'insert'], function () {
                });
                Route::group(['permission' => 'update'], function () {
                    Route::put('update/{id}', 'Receipt\ReceiptController@update')->name('update');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::delete('destroy/{id}', 'Receipt\ReceiptController@destroy')->name('destroy');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('export', 'Receipt\ReceiptController@export')->name('export');
                    Route::get('exportFilterSoQuyTienMat', 'Receipt\ReceiptController@exportFilterSoQuyTienMat')->name('exportFilterSoQuyTienMat');
                    Route::get('exportFilterSoQuyChuyenKhoan', 'Receipt\ReceiptController@exportFilterSoQuyChuyenKhoan')->name('exportFilterSoQuyChuyenKhoan');
                    Route::get('exportFilterThuChi', 'Receipt\ReceiptController@exportFilterThuChi')->name('exportFilterThuChi');
                    Route::get('exportFilterReceiptDeposit', 'Receipt\ReceiptController@exportFilterReceiptDeposit')->name('exportFilterReceiptDeposit');
                    Route::get('exportDetailFilter', 'Receipt\ReceiptController@exportDetailFilter')->name('exportDetailFilter');
                });
            });

        });

        Route::prefix('apartments')->name('apartments.')->namespace('Apartments')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'ApartmentsController@index')->name('index');
                Route::post('/', 'ApartmentsController@index')->name('index');
                Route::get('/create', 'ApartmentsController@create')->name('insert');
                Route::get('{id?}/edit', 'ApartmentsController@edit')->name('edit');
                Route::get('/ajax_get_apartment', 'ApartmentsController@ajaxGetSelectApartment')->name('ajax_get_apartment');
                Route::get('/ajax_get_apartment_with_place', 'ApartmentsController@ajaxGetSelectApartmentv2')->name('ajax_get_apartment_with_place');
                Route::get('/ajax_get_building_place', 'ApartmentsController@ajaxGetSelectBuildingPlace')->name('ajax_get_building_place');
                Route::get('/ajax_get_resident', 'ApartmentsController@ajaxGetSelectResident')->name('ajax_get_resident');
                Route::get('/import', 'ApartmentsController@indexImport')->name('index_import');
                Route::get('/download', 'ApartmentsController@download')->name('download');
                Route::get('/download_file_update', 'ApartmentsController@downloadFileUpdate')->name('download_file_update');
                Route::get('/ajax_get_customer', 'ApartmentsController@ajaxGetCustomer')->name('ajax_get_customer');
                Route::get('/ajax_get_apartment_in_group', 'ApartmentsController@ajaxGetApartmentInGroup')->name('ajax_get_apartment_in_group');
                Route::post('/action', 'ApartmentsController@action')->name('action');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/create', 'ApartmentsController@save')->name('create');
                Route::post('{id?}/add_file', 'ApartmentsController@createFile')->name('createfile');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('{id?}/edit', 'ApartmentsController@update')->name('update');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::get('{id?}/del', 'ApartmentsController@destroy')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
                Route::post('/import_apartment', 'ApartmentsController@importFileApartment')->name('import_apartment');
                Route::post('/import_update_apartment', 'ApartmentsController@importFileUpdateApartment')->name('import_update_apartment');
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('/export', 'ApartmentsController@export')->name('export');
            });

        });

        Route::prefix('apartment-group')->name('apartment-group.')->namespace('Apartments')->group(function () {
            Route::group(['permission' => 'view'], function () {
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/store', 'ApartmentGroupController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('/update', 'ApartmentGroupController@update')->name('update');
                Route::post('/status', 'ApartmentGroupController@status')->name('status');
                Route::post('/addApartment', 'ApartmentGroupController@addApartment')->name('addApartment');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::post('/delete', 'ApartmentGroupController@delete')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });

        // Report Chart

        Route::prefix('report-chart')->name('report-chart.')->namespace('ReportChart')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'ReportChartController@index')->name('index');
                Route::get('/report_cash', 'ReportChartController@report_total_cash')->name('report_cash');
                Route::get('/report_total_data_building', 'ReportChartController@report_total_data_building')->name('report_total_data_building');
                Route::get('/report_total_interactive', 'ReportChartController@report_total_interactive')->name('report_total_interactive');
            });
        });

        Route::prefix('apartment-handover')->name('apartment.handover.')->namespace('ApartmentHandOver')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'ApartmentHandOverController@index')->name('index');
                Route::post('/', 'ApartmentHandOverController@index')->name('index');
                Route::get('/create', 'ApartmentHandOverController@create')->name('insert');
                Route::get('{id?}/edit', 'ApartmentHandOverController@edit')->name('edit');
                Route::get('/ajax_get_apartment', 'ApartmentHandOverController@ajaxGetSelectApartment')->name('ajax_get_apartment');
                Route::get('/ajax_get_apartment_with_place', 'ApartmentHandOverController@ajaxGetSelectApartmentv2')->name('ajax_get_apartment_with_place');
                Route::get('/ajax_get_building_place', 'ApartmentHandOverController@ajaxGetSelectBuildingPlace')->name('ajax_get_building_place');
                Route::get('/ajax_get_resident', 'ApartmentHandOverController@ajaxGetSelectResident')->name('ajax_get_resident');
                Route::get('/import', 'ApartmentHandOverController@indexImport')->name('index_import');
                Route::get('/download', 'ApartmentHandOverController@download')->name('download');
                Route::post('/action', 'ApartmentHandOverController@action')->name('action');
                Route::get('/ajax_get_customer', 'ApartmentHandOverController@ajaxGetCustomer')->name('ajax_get_customer');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/create', 'ApartmentHandOverController@save')->name('save');
                Route::post('{id?}/add_file', 'ApartmentHandOverController@createFile')->name('createfile');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('{id?}/edit', 'ApartmentHandOverController@update')->name('update');
                Route::post('/change-status-confirm', 'ApartmentHandOverController@change_status_confirm')->name('change_status_confirm');
                Route::post('/change-note-confirm', 'ApartmentHandOverController@change_note_confirm')->name('change_note_confirm');
                Route::post('/change-success-handover', 'ApartmentHandOverController@change_success_handover')->name('change_success_handover');
                Route::post('/change-date-handover', 'ApartmentHandOverController@change_date_handover')->name('change_date_handover');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::get('{id?}/del', 'ApartmentHandOverController@destroy')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
                Route::post('/import_apartment', 'ApartmentHandOverController@importFile')->name('import_apartment');
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('/export', 'ApartmentHandOverController@export')->name('export');
            });
        });

        Route::prefix('customers')->name('customers.')->namespace('Customers')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'CustomersController@index')->name('index');
                Route::post('/', 'CustomersController@index')->name('index');
                Route::get('/create-new', 'CustomersController@create')->name('create');
                Route::get('{id?}/edit', 'CustomersController@edit')->name('edit');
                Route::get('/import', 'CustomersController@indexImport')->name('index_import');
                Route::post('/action', 'CustomersController@action')->name('action');
                Route::get('/download', 'CustomersController@download')->name('download');
                Route::get('/download-update', 'CustomersController@downloadUpdate')->name('downloadUpdate');
                Route::post('/viewexcel', 'CustomersController@ViewExcel')->name('viewexcel');
                Route::get('/ajax_check_type', 'CustomersController@ajaxCheckType')->name('ajax_check_type');
                Route::get('/ajax_get_cus', 'CustomersController@ajaxGetCus')->name('ajax_get_cus');
                Route::post('/send-mail-checked', 'CustomersController@sendMailChecked')->name('sendMailChecked');
                Route::post('/send-sms-checked', 'CustomersController@sendSmsChecked')->name('sendSmsChecked');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/create', 'CustomersController@store')->name('insert');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('{id?}/edit', 'CustomersController@update')->name('update');
                Route::post('/save_user', 'CustomersController@saveUserApartment')->name('save_user_apartment');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::get('{id?}/del_resident', 'CustomersController@destroyCustomerApartment')->name('del_customer');
                Route::get('{id?}/delete', 'CustomersController@destroyCus')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
                Route::post('/import_customer', 'CustomersController@importFileApartment')->name('import_customer');
                Route::post('/import_customer_new', 'CustomersController@indexImportNew')->name('import_customer_new');
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('/export', 'CustomersController@export')->name('export');
            });

        });

        Route::prefix('vehicles')->name('vehicles.')->namespace('Vehicles')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'VehiclesController@index')->name('index');
                Route::post('/', 'VehiclesController@index')->name('index');
                Route::post('/create', 'VehiclesController@create')->name('insert');
                Route::get('{id?}/edit', 'VehiclesController@edit')->name('edit');
                Route::get('/import', 'VehiclesController@indexImport')->name('index_import');
                Route::get('/download', 'VehiclesController@download')->name('download');
                Route::get('/ajax_check_number', 'VehiclesController@ajaxCheckNumber')->name('ajax_check_number');
                Route::post('/action', 'VehiclesController@action')->name('action');
                Route::post('/getPriceVehicle', 'VehiclesController@getPriceVehicle')->name('getPriceVehicle');
                Route::post('/checkNumberVehicle', 'VehiclesController@checkNumberVehicle')->name('checkNumberVehicle');
            });
            Route::group(['permission' => 'insert'], function () {
            });
            Route::group(['permission' => 'update'], function () {
                // Route::post('{id?}/edit', 'VehiclesController@update')->name('update');
                // Route::post('/save_vehicle', 'VehiclesController@saveVehicleApartment')->name('save_vehicle_apartment');
                // Route::put('/status','VehiclesController@status')->name('status');
            });
            Route::group(['permission' => 'delete'], function () {
                // Route::get('{id?}/del_vehicle', 'VehiclesController@destroyVehicleApartment')->name('del_vehicle');
                // Route::get('{id?}/delete', 'VehiclesController@destroy')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
                // Route::post('/import_vehicle', 'VehiclesController@importFileApartment')->name('import_vehicle');
                // Route::post('importExcel', 'VehiclesController@importExcel')->name("importExcel");
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('/export', 'VehiclesController@export')->name('export');
                Route::get('report_export', 'VehiclesController@report_export')->name("report_export");
            });
        });

        Route::prefix('vehiclecategory')->name('vehiclecategory.')->namespace('VehicleCategory')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'VehicleCategoryController@index')->name('index');
                Route::post('/', 'VehicleCategoryController@index')->name('index');
                Route::get('{id?}/edit', 'VehicleCategoryController@edit')->name('edit');
                Route::get('/ajax_get_vehicle_cate', 'VehicleCategoryController@ajaxGetSelectVehicleCate')->name('ajax_get_vehicle_cate');
                Route::post('/checkVehicleNameCategory', 'VehicleCategoryController@checkVehicleNameCategory')->name('checkVehicleNameCategory');
            });
            Route::group(['permission' => 'insert'], function () {
                // Route::post('/create', 'VehicleCategoryController@create')->name('insert');
            });
            Route::group(['permission' => 'update'], function () {
                // Route::post('{id?}/edit', 'VehicleCategoryController@update')->name('update');
                // Route::put('/status','VehicleCategoryController@status')->name('status');
            });
            Route::group(['permission' => 'delete'], function () {
                // Route::get('{id?}/delete', 'VehicleCategoryController@destroy')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });

        });

        Route::prefix('systemfiles')->name('systemfiles.')->namespace('SystemFiles')->group(function () {
            Route::get('/', 'SystemFilesController@index')->name('index');
            Route::post('/', 'SystemFilesController@index')->name('index');
            Route::post('/create', 'SystemFilesController@create')->name('insert');
            Route::get('{id?}/edit', 'SystemFilesController@edit')->name('edit');
            Route::post('{id?}/edit', 'SystemFilesController@update')->name('update');
            Route::get('/ajax_change_status', 'SystemFilesController@ajaxChangeStatus')->name('ajax_change_status');
            Route::get('{id?}/delete', 'SystemFilesController@destroy')->name('delete');
            Route::get('/download', 'SystemFilesController@download')->name('download');
        });

        Route::prefix('vehiclecards')->name('vehiclecards.')->namespace('VehicleCards')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'VehicleCardsController@index')->name('index');
                Route::post('/', 'VehicleCardsController@index')->name('index');
                Route::get('{id?}/edit', 'VehicleCardsController@edit')->name('edit');
                Route::get('/ajax_get_vehiclecard', 'VehicleCardsController@ajaxGetSelectVehicleNumber')->name('ajax_get_vehiclecard');
                Route::post('/action', 'VehicleCardsController@action')->name('action');
                Route::get('/ajax_change_status', 'VehicleCardsController@ajaxChangeStatus')->name('ajax_change_status');
                Route::get('/import', 'VehicleCardsController@indexImport')->name('index_import');
                Route::get('/download', 'VehicleCardsController@download')->name('download');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/create', 'VehicleCardsController@create')->name('insert');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('{id?}/edit', 'VehicleCardsController@update')->name('update');
                Route::put('/change-status', 'VehicleCardsController@changeStatus')->name('status');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::get('{id?}/delete', 'VehicleCardsController@destroy')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
                Route::post('/import_vehicle', 'VehicleCardsController@importFileVehicleCard')->name('import_vehiclecards');
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('/export', 'VehicleCardsController@export')->name('export');
            });

        });

        Route::prefix('comments')->name('comments.')->namespace('Comments')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/article', 'CommentsController@index')->name('index');
                Route::get('/event', 'CommentsController@indexEvent')->name('index_event');
                Route::get('/post-comment', 'CommentsController@indexPost')->name('index_comment_post');
                Route::post('action', 'CommentsController@action')->name('action');
                Route::get('{id}/comments', 'CommentsController@detail')->name('comments');
                Route::get('post/{id}/comments', 'CommentsController@detailPost')->name('comments_post');
                Route::get('/downloadfile', 'CommentsController@downloadfile')->name('download_file');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('save/{id?}', 'CommentsController@save')->name('save');
            });
            Route::group(['permission' => 'update'], function () {
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });

        });

        Route::prefix('feedback')->name('feedback.')->namespace('Feedback')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/fback', 'FeedbackController@index')->name('index');
                Route::post('/fback', 'FeedbackController@index')->name('index');
                Route::get('/request', 'FeedbackController@indexRequest')->name('index_request');
                Route::post('/request', 'FeedbackController@indexRequest')->name('index_request');
                Route::post('/action', 'FeedbackController@action')->name('action');
                Route::get('detail/{id}', 'FeedbackController@detail')->name('detail');
                Route::get('/ajax_get_user_profile', 'FeedbackController@ajaxGetSelectUserProfile')->name('ajax_get_profile');
                Route::get('/ajax_search_feedback', 'FeedbackController@ajaxSearch')->name('ajax_search_feedback');
                Route::get('/ajax_get_feedback', 'FeedbackController@ajaxGetSelectFeedback')->name('ajax_get_feedback');
                Route::get('/repair-apartment', 'FeedbackController@repairApartment')->name('repairApartment');
                Route::get('/repair-apartment-create', 'FeedbackController@repairApartmentCreate')->name('repairApartmentCreate');
                Route::get('/warranty-claim', 'FeedbackController@warranty_claim')->name('warrantyClaim');
                Route::get('/warranty-claim-create', 'FeedbackController@warrantyClaimCreate')->name('warrantyClaimCreate');
                Route::get('/{id}/warranty-claim-edit', 'FeedbackController@warrantyClaimEdit')->name('warrantyClaimEdit');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/repair-apartment-store', 'FeedbackController@repairApartmentStore')->name('repairApartmentStore');
                Route::post('/warranty-claim-store', 'FeedbackController@warrantyClaimStore')->name('warrantyClaimStore');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('/repair-apartment', 'FeedbackController@repairApartment')->name('repairApartment');
                Route::post('/repair-change-status', 'FeedbackController@repairChangeStatus')->name('repairChangeStatus');
                Route::post('/repair-change-status-v2', 'FeedbackController@repairChangeStatusV2')->name('repairChangeStatusV2');
                Route::post('/repairChangeStatusV3', 'FeedbackController@repairChangeStatusV3')->name('repairChangeStatusV3');
                Route::post('{id}/warranty-claim-update', 'FeedbackController@warrantyClaimUpdate')->name('warrantyClaimUpdate');
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });

        });

        Route::prefix('feedbackform')->name('feedbackform.')->namespace('Feedback')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'FeedbackFormController@index')->name('index');
                Route::get('/create', 'FeedbackFormController@create')->name('create');
                Route::get('/edit/{id}', 'FeedbackFormController@edit')->name('edit');
                Route::post('/edit/{id}', 'FeedbackFormController@save')->name('update');
                Route::get('/download', 'FeedbackFormController@download')->name('download');
                Route::post('/action', 'FeedbackFormController@action')->name('action');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/create', 'FeedbackFormController@save')->name('save');
            });
            Route::group(['permission' => 'update'], function () {
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });

        });

        Route::prefix('product-deposit')->name('product-deposit.')->namespace('ProductDeposit')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'ProductDepositController@index')->name('index');
                Route::post('/', 'ProductDepositController@index')->name('index');
            });
            Route::group(['permission' => 'insert'], function () {
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('/change-status', 'ProductDepositController@changeStatus')->name('changeStatus');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::post('/destroy', 'ProductDepositController@destroy')->name('destroy');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });

        });

        Route::prefix('banks')->name('banks.')->namespace('Banks')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'BanksController@index')->name('index');
                Route::get('/create', 'BanksController@create')->name('create');
                Route::get('/download', 'BanksController@download')->name('download');
                Route::post('/action', 'BanksController@action')->name('action');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/create', 'BanksController@save')->name('save');
            });
            Route::group(['permission' => 'update'], function () {
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });

        });
        // Building-handbook-type
        Route::prefix('building-handbook')->name('building-handbook.')->namespace('BuildingHandbookType')->group(function () {
            Route::prefix('type')->name('type.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'BuildingHandbookTypeController@index')->name('index');
                    Route::get('create', 'BuildingHandbookTypeController@edit')->name('create');
                    Route::get('/{id}/edit', 'BuildingHandbookTypeController@edit')->name('edit');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('store', 'BuildingHandbookTypeController@store')->name('store');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('{id}/update', 'BuildingHandbookTypeController@update')->name('update');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::post('delete', 'BuildingHandbookTypeController@delete')->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                });

            });
        });

        //building info
        Route::prefix('building')->name('building.')->namespace('BuildingInfo')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'BuildingInfoController@index')->name('index');
                Route::get('/edit', 'BuildingInfoController@edit')->name('edit');
                Route::post('payment/edit', 'BuildingInfoController@editPayment')->name('editPayment');
                Route::post('building-info/edit', 'BuildingInfoController@editInfoBuilding')->name('editInfo');
                Route::post('change_building', 'BuildingInfoController@changeBuilding')->name('changeBuilding');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('payment_vpbank/store', 'BuildingInfoController@storePaymentVpBank')->name('storePaymentVpBank');
                Route::post('building-info/store', 'BuildingInfoController@storeInfoBuilding')->name('building-info-store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('/edit', 'BuildingInfoController@update')->name('update');
                Route::post('payment/store', 'BuildingInfoController@storePayment')->name('store-payment');
                Route::post('payment/update', 'BuildingInfoController@updatePayment')->name('updatePayment');
                Route::post('building-info/update', 'BuildingInfoController@updateInfoBuilding')->name('updateInfo');
                Route::post('building-info/update-department-debit', 'BuildingInfoController@updateDepartmentIdAndDebitDate')->name('updateDepartmentIdAndDebitDate');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::delete('payment/delete/{id}', 'BuildingInfoController@destroyPayment')->name('destroyPayment');
                Route::delete('building-info/delete/{id}', 'BuildingInfoController@destroyInfo')->name('destroyInfo');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });
        // per_page

        Route::prefix('perpage')->name('perpage.')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::post('/action', 'BuildingController@per_page')->name('action');
            });
        });

        Route::prefix('system')->name('system.')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/permission/index', 'Permissions\PermissionController@index')->name('permission.index');
                Route::get('/permission/create', 'Permissions\PermissionController@create')->name('permission.create');
                Route::get('permission/{id}/edit', 'Permissions\PermissionController@edit')->name('permission.edit');
                Route::get('/permission-groups/index', 'Permissions\GroupPermissionController@index')->name('group_permission.index');
                Route::get('/permission-groups/create', 'Permissions\GroupPermissionController@create')->name('group_permission.create');
                Route::get('permission-groups/{id}/edit', 'Permissions\GroupPermissionController@edit')->name('group_permission.edit');
                Route::get('/menu/index', 'Permissions\MenuController@index')->name('menu.index');
                Route::get('/menu/create', 'Permissions\MenuController@create')->name('menu.create');
                Route::get('menu/{id}/edit', 'Permissions\MenuController@edit')->name('menu.edit');
                Route::prefix('template-send-notification')->group(function () {
                    Route::get('/create', 'System\TemplateSendNotificationController@create')->name('template_send_notification.create');
                    Route::get('/send', 'System\TemplateSendNotificationController@send')->name('template_send_notification.send');
                });
                Route::get('config', 'System\ConfigController@index')->name('config.index');

            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/permission/store', 'Permissions\PermissionController@store')->name('permission.store');
                Route::post('/permission-groups/store', 'Permissions\GroupPermissionController@store')->name('group_permission.store');
                Route::post('/menu/store', 'Permissions\MenuController@store')->name('menu.store');
                Route::post('config', 'System\ConfigController@store')->name('config.store');

            });
            Route::group(['permission' => 'update'], function () {
                Route::post('/permission/{id}', 'Permissions\PermissionController@update')->name('permission.update');
                Route::post('/check_index_position', 'Permissions\PermissionController@check_index_position')->name('permission.check_index_position');
                Route::post('/permission-groups/{id}', 'Permissions\GroupPermissionController@updatePermission')->name('group_permission.update');
                Route::post('/menu/{id}', 'Permissions\MenuController@update')->name('menu.update');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::delete('/permission/delete/{id}', 'Permissions\PermissionController@destroy')->name('permission.destroy');
                Route::delete('/menu/delete/{id}', 'Permissions\MenuController@destroy')->name('menu.destroy');
                Route::get('/permission/delete/{id}', 'Permissions\GroupPermissionController@destroy')->name('group_permission.destroy');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });

        //department
        Route::prefix('department')->name('department.')->namespace('Department')->group(function () {

            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'DepartmentController@index')->name('index');
                Route::get('/detail/{id}', 'DepartmentController@show')->name('show');
                Route::get('/detail1/{id}', 'DepartmentController@show1')->name('show1');
                Route::get('permission/{staffId}', 'DepartmentController@updatePermissionUser')->name('updatePermissionUser');
                Route::get('/getSelectGroup', 'DepartmentController@ajaxGetSelectGroup')->name('ajaxGetSelectGroup');
                Route::get('/getChangeSelectGroup', 'DepartmentController@updateGroupPermission')->name('updateGroupPermission');
                Route::post('/action', 'DepartmentController@action')->name('action');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/', 'DepartmentController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('/showModalEdit', 'DepartmentController@edit')->name('edit');
                Route::post('/update', 'DepartmentController@update')->name('update');
                Route::post('/change-status', 'DepartmentController@changeStatus')->name('change-status');
                Route::post('add_staff/{id}', 'DepartmentController@addStaff')->name('addStaff');
                Route::post('staff/head-department', 'DepartmentController@headStaff')->name('headStaff');
                Route::post('staff/head-building', 'DepartmentController@headBuilding')->name('headBuilding');
                Route::post('staff/change-staff', 'DepartmentController@changeStaff')->name('changeStaff');
                Route::post('group-permission/createOrUpdate', 'DepartmentController@createOrUpdatePermission')->name('group-permission');
                Route::post('permission/{staffId}', 'DepartmentController@updatePermissionDeny')->name('updatePermissionDeny');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::delete('destroy/{id}', 'DepartmentController@destroy')->name('destroy');
                Route::delete('staff/delete/{id}', 'DepartmentController@destroyStaff')->name('destroyStaff');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });

        });

        // Building-handbook-category
        Route::prefix('building-handbook')->name('building-handbook.')->namespace('BuildingHandbookCategory')->group(function () {
            Route::prefix('category')->name('category.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/{id}/edit', 'BuildingHandbookCategoryController@edit')->name('edit');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('store', 'BuildingHandbookCategoryController@store')->name('store');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('{id}/update', 'BuildingHandbookCategoryController@update')->name('update');
                    Route::post('{id}/ajax_change_status', 'BuildingHandbookCategoryController@ajaxChangeStatus')->name('ajax_change_status');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::post('ajax_delete_multi', 'BuildingHandbookCategoryController@ajaxDeleteMulti')->name('del_multi');
                    Route::post('delete', 'BuildingHandbookCategoryController@delete')->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                });
            });
        });

        // Upload
        Route::prefix('upload')->name('upload.')->namespace('Upload')->group(function () {
            Route::group(['permission' => 'insert'], function () {
                Route::post('/file', 'UploadController@upload_v2')->name('store');
                Route::post('/ckeditor', 'UploadController@upload_ckeditor')->name('upload_ckeditor');
            });
        });
        //Promotion
        Route::prefix('promotion')->name('promotion.')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', function () {
                    $data['meta_title'] = 'Quản lý khuyến mãi';
                    return view('promotion.index', $data);
                })->name('index');
            });
            Route::prefix("promotion_manager")->name("promotion_manager.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'PromotionManager\PromotionManagerController@index')->name('index');
                    Route::post('/edit', 'PromotionManager\PromotionManagerController@edit')->name('edit');
                });

                Route::group(['permission' => 'insert'], function () {
                    Route::post('/create', 'PromotionManager\PromotionManagerController@store')->name('store');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('/update', 'PromotionManager\PromotionManagerController@update')->name('update');
                    Route::put('/change-status', 'PromotionManager\PromotionManagerController@change_status')->name('status');
                });
            });

            Route::prefix("apartment_promotion_manager")->name("apartment_promotion_manager.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'ApartmentPromotionManager\ApartmentPromotionManagerController@index')->name('index');
                    Route::post('/edit', 'ApartmentPromotionManager\ApartmentPromotionManagerController@edit')->name('edit');
                });

                Route::group(['permission' => 'insert'], function () {
                    Route::post('/create', 'ApartmentPromotionManager\ApartmentPromotionManagerController@store')->name('store');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('/update', 'ApartmentPromotionManager\ApartmentPromotionManagerController@update')->name('update');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::delete('/delete', 'ApartmentPromotionManager\ApartmentPromotionManagerController@delete')->name('delete');
                });
            });
        });
        // Building-handbook
        Route::prefix('building-handbook')->name('building-handbook.')->namespace('BuildingHandbook')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/{id?}', 'BuildingHandbookController@index')->name('index');
                Route::get('create', 'BuildingHandbookController@edit')->name('create');
                Route::get('ajax_get_category', 'BuildingHandbookController@ajaxGetCategory')->name('ajax_get_category');
                Route::get('{id}/edit', 'BuildingHandbookController@edit')->name('edit');
                Route::post('action', 'BuildingHandbookController@action')->name('action');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('store', 'BuildingHandbookController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('{id}/update', 'BuildingHandbookController@update')->name('update');
                Route::post('{id}/ajax_change_status', 'BuildingHandbookController@ajaxChangeStatus')->name('ajax_change_status');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::post('delete', 'BuildingHandbookController@delete')->name('delete');
                Route::post('ajax_delete_multi_handbook', 'BuildingHandbookController@ajaxDeleteMultiHandbook')->name('del_multi');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });

        });

        // Work-diary-v2
        Route::prefix('work-diary-v2')->name('work-diary-v2.')->namespace('WorkDiary_v2')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'WorkDiary_v2Controller@index')->name('index');
                Route::get('search_workdiary', 'WorkDiary_v2Controller@search_workdiary')->name('search_workdiary');
                Route::get('search-tempsample', 'WorkDiary_v2Controller@search_tempsample')->name('search_tempsample');
                Route::get('search_workdiary_apartment', 'WorkDiary_v2Controller@search_workdiary_apartment')->name('search_workdiary_apartment');
                Route::get('create', 'WorkDiary_v2Controller@create')->name('create');
                Route::get('task/{id}/show', 'WorkDiary_v2Controller@showtask')->name('showtask');
                Route::get('edit/{id}', 'WorkDiary_v2Controller@edit')->name('edit');
                Route::get('ajaxGetSelectmaintenance', 'WorkDiary_v2Controller@ajaxGetSelectmaintenance_asset')->name('ajax_get_maintenance_asset');
                Route::get('ajaxGetSelectUserByDepartment', 'WorkDiary_v2Controller@ajaxGetSelectUserByDepartment')->name('ajaxGetSelectUserByDepartment');
                Route::get('detailCheckList', 'WorkDiary_v2Controller@detai_check_list')->name('detailCheckList');
                Route::post('show', 'WorkDiary_v2Controller@show')->name('show');
                Route::get('/detail', "WorkDiary_v2Controller@detail")->name("detail");
                Route::get('/changestatus', "WorkDiary_v2Controller@changestatus")->name("changestatus");
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('store', 'WorkDiary_v2Controller@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('{id}/update', 'WorkDiary_v2Controller@update')->name('update');
                Route::post('change-status', 'WorkDiary_v2Controller@change_status')->name('change_status');
                Route::post('sub-task/feedback', 'WorkDiary_v2Controller@feedback_subtask')->name('sub_task_feedback');
                Route::post('task/feedback', 'WorkDiary_v2Controller@feedback_task')->name('task_feedback');
                Route::post('change-status-subtask', 'WorkDiary_v2Controller@change_status_subtask')->name('change_status_subtask');
                Route::post('change-status-task', 'WorkDiary_v2Controller@change_status_task')->name('change_status_task');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::post('delete', 'WorkDiary_v2Controller@delete')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('export', 'WorkDiary_v2Controller@exportExcel')->name('exportExcel');
            });
        });

        // Shift-work
        Route::prefix('shift')->name('shift.')->namespace('WorkDiary_v2\Shift')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('', 'ShiftController@index')->name('index');
                Route::get('/{id}/edit', 'ShiftController@edit')->name('edit');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('create', 'ShiftController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('{id}/update', 'ShiftController@update')->name('update');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::post('delete', 'ShiftController@delete')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });

        // TempSample-work
        Route::prefix('tempsample')->name('tempsample.')->namespace('WorkDiary_v2\TempSample')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('', 'TempSampleController@index')->name('index');
                Route::get('/{id}/edit', 'TempSampleController@edit')->name('edit');
                Route::get('ajaxGetSelecttasktemplate', 'TempSampleController@ajaxGetSelecttasktemplate')->name('ajaxGetSelecttasktemplate');
                Route::get('sub', 'TempSampleController@subtempindex')->name('subtempindex');
                Route::get('sub/{id}/edit', 'TempSampleController@subtempedit')->name('subtempedit');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('create', 'TempSampleController@store')->name('store');
                Route::post('sub/create', 'TempSampleController@subtempstore')->name('subtempstore');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('{id}/update', 'TempSampleController@update')->name('update');
                Route::post('sub/{id}/update', 'TempSampleController@subtempupdate')->name('subtempupdate');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::post('delete', 'TempSampleController@delete')->name('delete');
                Route::post('sub/delete', 'TempSampleController@subtempdelete')->name('subtempdelete');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
            //Sub Temp
        });

        // Category-work
        Route::prefix('category-work')->name('categorywork.')->namespace('WorkDiary_v2\Category')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('', 'CategoryController@index')->name('index');
                Route::get('/{id}/edit', 'CategoryController@edit')->name('edit');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('create', 'CategoryController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('{id}/update', 'CategoryController@update')->name('update');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::post('delete', 'CategoryController@delete')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });

        Route::prefix('posts')->name('posts.')->namespace('Backend')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/article', 'PostController@index')->name('index');
                Route::get('/event', 'PostController@indexEvent')->name('index_event');
                Route::get('create', 'PostController@edit')->name('create');
                Route::get('{id}/edit', 'PostController@edit')->name('edit');
                Route::get('delete-poll-option', 'PostController@deletePollOption')->name('delete.option');
                Route::get('{id}/comments', 'CommentController@detail')->name('comments');
                Route::get('{id}/registers', 'RegisterController@index')->name('registers');
                Route::get('/comments', 'PostController@listComments')->name('listcomments');
                Route::post('action', 'PostController@action')->name('action');
                Route::any('ajax/posts', 'PostController@ajaxPosts')->name('ajax.posts');

                Route::any('validator/register', 'RegisterController@validationCode')->name('validator.register');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('{id}/save', 'PostController@save')->name('save');
                Route::post('add-poll-option', 'PostController@addPollOption')->name('add.option');
                Route::post('save-poll-option', 'PostController@savePollOption')->name('save.option');
                Route::post('{id}/registers/action', 'RegisterController@action')->name('registers.action');
                Route::post('register', 'RegisterController@checkInRegister')->name('add.register');
            });
            Route::group(['permission' => 'update'], function () {
                Route::put('/change-status', 'PostController@changeStatus')->name('status');
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('{id}/registers/export', 'RegisterController@export')->name('registers.export');
            });

        });

        // Posts-Customers

        Route::prefix('posts-customers')->name('posts_customers.')->namespace('Backend')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/article', 'PostCustomersController@index')->name('index');
                Route::get('/asset-handover', 'PostCustomersController@indexAssetHandover')->name('indexAssetHandover');
                Route::get('/event', 'PostCustomersController@indexEvent')->name('index_event');
                Route::get('create', 'PostCustomersController@edit')->name('create');
                Route::get('{id}/edit', 'PostCustomersController@edit')->name('edit');
                Route::get('delete-poll-option', 'PostCustomersController@deletePollOption')->name('delete.option');
                Route::any('ajax/customers', 'PostCustomersController@ajaxCustomers')->name('ajax.customers');
                Route::any('ajax/apartment', 'PostCustomersController@ajaxApartment')->name('ajax.apartment');
                Route::any('ajax/posts', 'PostCustomersController@ajaxPosts')->name('ajax.posts');
                Route::any('ajax/buildingplace', 'PostCustomersController@ajaxBuildingPlace')->name('ajax.buildingplace');
                Route::get('/comments', 'PostCustomersController@listComments')->name('listcomments');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('{id}/save', 'PostCustomersController@save')->name('save');
                Route::post('/save-apartment', 'PostCustomersController@save_apartment')->name('save_apartment');
                Route::post('/save-asset-handover', 'PostCustomersController@save_asset_hand_over')->name('save_asset_hand_over');
                Route::post('action', 'PostCustomersController@action')->name('action');
                Route::post('add-poll-option', 'PostCustomersController@addPollOption')->name('add.option');
                Route::post('save-poll-option', 'PostCustomersController@savePollOption')->name('save.option');
            });
            Route::group(['permission' => 'update'], function () {
                Route::put('/change-status', 'PostCustomersController@changeStatus')->name('status');
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });

        Route::prefix('polloptions')->name('polloptions.')->namespace('Backend')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'PollOptionController@index')->name('index');
                Route::get('{id}/edit', 'PollOptionController@edit')->name('edit');
                Route::post('action', 'PollOptionController@action')->name('action');
                Route::get('create', 'PollOptionController@edit')->name('create');
                Route::get('{id}/post-poll', 'PollOptionController@postPoll')->name('postPoll');
                Route::any('ajax/get-all', 'PollOptionController@getAll')->name('getAll');
                Route::any('ajax/get-all-posts', 'PollOptionController@getAllPosts')->name('getAllPosts');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('{id}/save', 'PollOptionController@save')->name('save');
            });
            Route::group(['permission' => 'update'], function () {
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('export/{id}', 'PollOptionController@export')->name('export');
            });

        });

        // khách hàng đánh giá
        Route::prefix('customer_rated_service')->name('rated_service.')->namespace('CustomerRatedServices')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/total', 'RatedServiceController@total')->name('total');
                Route::get('/export-total', 'RatedServiceController@export_total')->name('export_total');
                Route::get('/detail', 'RatedServiceController@detail')->name('detail');
                Route::post('/action', 'RatedServiceController@action')->name('action');
                Route::get('/export-detail', 'RatedServiceController@export_detail')->name('export_detail');
                Route::get('/audit-limit', 'RatedServiceController@update_limit_audit')->name('update_limit_audit');
                Route::get('/audit-app', 'RatedServiceController@auditApp')->name('auditApp');
            });
            Route::group(['permission' => 'insert'], function () {
            });
            Route::group(['permission' => 'update'], function () {
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });

        Route::prefix('categories')->name('categories.')->namespace('Backend')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::any('all', 'CategoryController@all')->name('all');
                Route::get('/article', 'CategoryController@index')->name('index');
                Route::get('/event', 'CategoryController@indexEvent')->name('index_event');
                Route::get('create', 'CategoryController@edit')->name('create');
                Route::get('{id}/edit', 'CategoryController@edit')->name('edit');
                Route::post('action', 'CategoryController@action')->name('action');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('{id}/save', 'CategoryController@save')->name('save');
            });
            Route::group(['permission' => 'update'], function () {
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('export', 'CategoryController@export')->name('export');
            });

        });

        //compamy
        Route::prefix('company')->name('company.')->namespace('Company')->group(function () {
            //listcompany
            Route::prefix("listcompany")->name("listcompany.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'CompanyController@listcompany')->name('listcompany');
                });
            });
            Route::prefix("listemp")->name("listemp.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'CompanyController@listemp')->name('listemp');
                });
            });
            Route::prefix("listdepartment")->name("listdepartment.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'CompanyController@listdepartment')->name('listdepartment');
                });
            });
            Route::prefix("listurban")->name("listurban.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'CompanyController@listurban')->name('listurban');
                });
            });
            // urban - buildings
            Route::prefix("urban-building")->name("urban-building.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'CompanyController@index')->name('index');
                    Route::get('/create', 'CompanyController@create')->name('create');
                    Route::get('/edit/{id}', 'CompanyController@edit')->name('edit');
                    Route::get('/create_staff', 'CompanyController@createStaffEmail')->name('create_staff_form');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/create_staff', 'CompanyController@createStaff')->name('create_staff');
                    Route::post('/', 'CompanyController@store')->name('store');
                    Route::post('/create_staff/store', 'CompanyController@storeStaff')->name('storeStaff');
                    Route::post('/saveUrban', 'CompanyController@saveUrban')->name('saveUrban');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('/change-status', 'CompanyController@changeStatus')->name('change-status');
                    Route::post('/change-status-building', 'CompanyController@changeStatusBuilding')->name('change-status-building');
                    Route::post('/update/{id}', 'CompanyController@update')->name('update');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::get('/destroy/{id}', 'CompanyController@delUrban')->name('destroy');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                });
            });
            // company
            Route::prefix("list")->name("list.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'CompanyController@indexCompany')->name('index');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/saveCompany', 'CompanyController@saveCompany')->name('saveCompany');
                });
                Route::group(['permission' => 'update'], function () {
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::get('/destroy/{id}', 'CompanyController@delCompany')->name('destroy');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                });


            });
        });
        //business partners
        Route::prefix('partners')->name('business-partners.')->namespace('BusinessPartner')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'BusinessPartnerController@index')->name('index');
                Route::get('/{id}/edit', 'BusinessPartnerController@edit')->name('edit');
                Route::post('action', 'BusinessPartnerController@action')->name('action');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('store', 'BusinessPartnerController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('{id}/update', 'BusinessPartnerController@update')->name('update');
                Route::post('change-status', 'BusinessPartnerController@changeStatus')->name('change-status');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::post('delete', 'BusinessPartnerController@delete')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('/export', 'BusinessPartnerController@exportExcel')->name('export');
            });
        });

        //Service partners
        Route::prefix('service-partners')->name('service-partners.')->namespace('ServicePartners')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'ServicePartnerController@index')->name('index');
                Route::get('/{id}/edit', 'ServicePartnerController@edit')->name('edit');
                Route::post('action', 'ServicePartnerController@action')->name('action');
                Route::get('/export', 'ServicePartnerController@exportExcel')->name('export');
                Route::get('/ajax_get_partners', 'ServicePartnerController@ajaxGetSelectPartners')->name('ajax_get_partners');
                Route::get('/ajax_get_building_handbooks', 'ServicePartnerController@ajaxGetSelectBuildingHandbooks')->name('ajax_get_building_handbooks');
                Route::get('/ajax_get_customers', 'ServicePartnerController@ajaxGetSelectCustomers')->name('ajax_get_customers');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('store', 'ServicePartnerController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('{id}/update', 'ServicePartnerController@update')->name('update');
                Route::post('change-status', 'ServicePartnerController@changeStatus')->name('change-status');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::post('delete', 'ServicePartnerController@delete')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });

        //Asset Apartment
        Route::prefix('asset-apartment')->name('asset-apartment.')->namespace('Assets')->group(function () {

            Route::prefix("asset-handover")->name("asset-handover.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'AssetHandOverController@index')->name('index');
                    Route::get('/create', 'AssetHandOverController@create')->name('create');
                    Route::get('/{id}/edit', 'AssetHandOverController@edit')->name('edit');
                    Route::post('/action', 'AssetHandOverController@action')->name('action');
                    Route::get('/import', "AssetHandOverController@indexImport")->name('import');
                    Route::get('/download', "AssetHandOverController@dowload_file_import")->name('download');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('store', 'AssetHandOverController@store')->name('store');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('{id}/update', 'AssetHandOverController@update')->name('update');
                    Route::post('/change_date_of_delivery', 'AssetHandOverController@change_date_of_delivery')->name('change_date_of_delivery');
                    Route::post('/change_status', 'AssetHandOverController@change_status')->name('change_status');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::post('delete', 'AssetHandOverController@delete')->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                    Route::post('/import_store', 'AssetHandOverController@import_store')->name('import_store');
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('/export', 'AssetHandOverController@export')->name('export');
                });


            });

            Route::prefix("asset")->name("asset.")->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'AssetApartmentController@index')->name('index');
                    Route::get('/create', 'AssetApartmentController@create')->name('create');
                    Route::get('/{id}/edit', 'AssetApartmentController@edit')->name('edit');
                    Route::get('/download', "AssetApartmentController@dowload_file_import")->name('download');
                    Route::get('/download_file_update', "AssetApartmentController@dowload_file_update_import")->name('download_file_update');
                    Route::get('/import', "AssetApartmentController@indexImport")->name('import');
                    Route::post('/action', 'AssetApartmentController@action')->name('action');
                    Route::get('/ajaxGetSelect', 'AssetApartmentController@ajaxGetSelect')->name('ajaxGetSelect');
                    Route::get('/category', 'AssetApartmentController@indexCategory')->name('indexCategory');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('store', 'AssetApartmentController@store')->name('store');
                    Route::post('/import_store', 'AssetApartmentController@import_store')->name('import_store');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('{id}/update', 'AssetApartmentController@update')->name('update');
                    Route::post('/import_update', 'AssetApartmentController@import_update')->name('import_update');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::post('delete', 'AssetApartmentController@delete')->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('/export', 'AssetApartmentController@export')->name('export');
                });

            });
        });

        // Payment Detail

        Route::prefix('payment-detail')->name('paymen-detail.')->namespace('PaymentDetail')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/tien-thua', 'PaymentDetailController@tien_thua')->name('tien_thua');
                Route::get('/tien-thua/show', 'PaymentDetailController@tien_thua_show')->name('tien_thua.show');
                Route::get('/tien-thua/export', 'PaymentDetailController@tien_thua_export')->name('tien_thua.export');
                Route::post('/tien-thua/action', 'PaymentDetailController@per_page')->name('tien_thua.action');
                Route::get('/hach-toan', 'PaymentDetailController@hach_toan')->name('hach_toan');
                Route::post('/hach-toan/action', 'PaymentDetailController@per_page')->name('hach_toan.action');
                Route::get('/hach-toan/export', 'PaymentDetailController@hach_toan_export')->name('hach_toan.export');
                Route::get('/hach-toan/show', 'PaymentDetailController@hach_toan_show')->name('hach_toan.show');
                Route::post('/action', 'PaymentDetailController@action')->name('action');
                Route::get('/export', 'PaymentDetailController@export')->name('export');
                Route::get('', 'PaymentDetailController@index')->name('index');
            });
        });


        //History Notify

        Route::prefix('history-notify')->name('history-notify.')->namespace('HistoryNotify')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'HistoryNotifyController@index')->name('index');
                Route::get('/entire', 'HistoryNotifyController@entire')->name('entire');
                Route::get('/email', 'HistoryNotifyController@email')->name('email');
                Route::get('/notify-app', 'HistoryNotifyController@notify_app')->name('notify-app');
                Route::get('/sms', 'HistoryNotifyController@sms')->name('sms');
                Route::get('/vnpay', 'HistoryNotifyController@vnpay')->name('vnpay');
                Route::post('action', 'HistoryNotifyController@action')->name('action');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::post('/delete', 'HistoryNotifyController@delete')->name('delete');
            });
        });

        // Accounting Account

        Route::prefix('accounting-account')->name('accounting.account.')->namespace('AccountingAccounts')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'AccountingAccountController@index')->name('index');
                Route::get('create', 'AccountingAccountController@edit')->name('create');
                Route::get('{id}/edit', 'AccountingAccountController@edit')->name('edit');
                Route::post('action', 'AccountingAccountController@action')->name('action');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('{id}/save', 'AccountingAccountController@save')->name('save');
            });
            Route::group(['permission' => 'update'], function () {
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('export', 'AccountingAccountController@export')->name('export');
            });
        });

        // History Tracsaction Accounting

        Route::prefix('history-transaction-accounting')->name('history-transaction-accounting.')->namespace('HistoryTransactionAccounting')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'HistoryTransactionAccountingController@index')->name('index');
                Route::get('create', 'HistoryTransactionAccountingController@edit')->name('create');
                Route::get('{id}/edit', 'HistoryTransactionAccountingController@edit')->name('edit');
                Route::post('action', 'HistoryTransactionAccountingController@action')->name('action');
                Route::get('import', 'HistoryTransactionAccountingController@import_view')->name('import');
                Route::get('download', 'HistoryTransactionAccountingController@download')->name('download');
                Route::get('download_vietqr', 'HistoryTransactionAccountingController@download_vietqr')->name('download_vietqr');
                Route::get('import_vietqr', 'HistoryTransactionAccountingController@import_vietqr')->name('import_vietqr');
                Route::get('debt-brick', 'HistoryTransactionAccountingController@indexDebtBrick')->name('indexDebtBrick');
                Route::post('confirm-debt-brick', 'HistoryTransactionAccountingController@create_debt_brick')->name('action.debt_brick');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('import-save', 'HistoryTransactionAccountingController@import_save')->name('import_save');
                Route::post('{id}/save', 'HistoryTransactionAccountingController@save')->name('save');
            });
            Route::group(['permission' => 'update'], function () {
                Route::put('confirm_transaction', 'HistoryTransactionAccountingController@confirm_transaction')->name('confirm_transaction');
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('export', 'HistoryTransactionAccountingController@export')->name('export');
                Route::get('export_debt_brick', 'HistoryTransactionAccountingController@export_debt_brick')->name('export_debt_brick');
            });
        });

        // Accounting Vouchers

        Route::prefix('accounting-voucher')->name('accounting.voucher.')->namespace('AccountingVouches')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/tien-thua', 'AccountingVoucheController@tien_thua')->name('tien_thua');
                Route::get('/tien-thua/show', 'AccountingVoucheController@tien_thua_show')->name('tien_thua.show');
                Route::get('/tien-thua/export', 'AccountingVoucheController@tien_thua_export')->name('tien_thua.export');
                Route::post('/tien-thua/action', 'AccountingVoucheController@per_page')->name('tien_thua.action');
                Route::get('/hach-toan', 'AccountingVoucheController@hach_toan')->name('hach_toan');
                Route::post('/hach-toan/action', 'AccountingVoucheController@per_page')->name('hach_toan.action');
                Route::get('/hach-toan/show', 'AccountingVoucheController@hach_toan_show')->name('hach_toan.show');
            });
            Route::group(['permission' => 'insert'], function () {
            });
            Route::group(['permission' => 'update'], function () {
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('/hach-toan/export', 'AccountingVoucheController@hach_toan_export')->name('hach_toan.export');
            });
        });

        // Building Payment Info

        Route::prefix('building-info')->name('building.info.')->namespace('BuildingPaymentInfo')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'BuildingPaymentInfoController@index')->name('index');
                Route::get('create', 'BuildingPaymentInfoController@edit')->name('create');
                Route::get('{id}/edit', 'BuildingPaymentInfoController@edit')->name('edit');
                Route::post('action', 'BuildingPaymentInfoController@action')->name('action');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('{id}/save', 'BuildingPaymentInfoController@save')->name('save');
            });
            Route::group(['permission' => 'update'], function () {
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('export', 'BuildingPaymentInfoController@export')->name('export');
            });
        });

        // Log import excel

        Route::prefix('log-import')->name('log.import.')->namespace('LogImport')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'LogImportController@index')->name('index');
            });
        });



        //configs
        Route::prefix('configs')->name('configs.')->namespace('Config')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'ConfigController@index')->name('index');
                Route::get('/create', 'ConfigController@create')->name('create');
                Route::get('/{id}/edit', 'ConfigController@edit')->name('edit');
                Route::get('/bill-pdf', 'ConfigController@billPdf')->name('billPdf');
                Route::get('/receipt-style', 'ConfigController@receipt')->name('receipt_style');
                Route::post('/bill-pdf-post', 'ConfigController@billPdfPost')->name('billPdfPost');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/', 'ConfigController@store')->name('store');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('/{id}/update', 'ConfigController@update')->name('update');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::post('/delete', 'ConfigController@delete')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });

        Route::prefix('building-place')->name('buildingplace.')->namespace('BuildingPlace')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'BuildingPlaceController@index')->name('index');
                Route::post('/', 'BuildingPlaceController@index')->name('index');
                Route::get('/create', 'BuildingPlaceController@create')->name('create');
                Route::post('/create', 'BuildingPlaceController@save')->name('create');
                Route::get('/{id}/edit', 'BuildingPlaceController@edit')->name('edit');
                Route::post('/action', 'BuildingPlaceController@action')->name('action');
                Route::get('/get-email', 'BuildingPlaceController@ajaxGetSelectEmail')->name('ajaxGetSelectEmail');
            });
            Route::group(['permission' => 'insert'], function () {
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('/{id}/edit', 'BuildingPlaceController@update')->name('update');
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });

        //provisional receipt
        Route::prefix('provisional-receipt')->name('provisionalreceipt.')->namespace('BdcProvisionalReceipt')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'ProvisionalReceiptController@index')->name('index');
                Route::get('/create', 'ProvisionalReceiptController@create')->name('create');
                Route::get('/create-payment-slip', 'ProvisionalReceiptController@createPaymentSlip')->name('createPaymentSlip');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('/store', 'ProvisionalReceiptController@store')->name('store');
                Route::post('/storePaymentSlip', 'ProvisionalReceiptController@storePaymentSlip')->name('storePaymentSlip');
            });
            Route::group(['permission' => 'update'], function () {
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
            });
        });

        //receipt total
        Route::prefix('receipt-total')->name('receipttotal.')->namespace('ReceiptTotal')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'ReceiptTotalController@index')->name('index');
                Route::get('?version=2', 'ReceiptTotalController@index')->name('indexv2');
                Route::get('/reportReceiptDeposit', 'ReceiptTotalController@reportReceiptDeposit')->name('reportReceiptDeposit');
                Route::get('/report', 'ReceiptTotalController@report')->name('report');
                Route::get('/exportReceiptDeposit', 'ReceiptTotalController@exportReceiptDeposit')->name('exportReceiptDeposit');
            });
        });

        //vnpay
        Route::prefix('vnpay')->name('vnpay.')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'Vnpay\VnpayController@index')->name('index');
            });
        });
        Route::prefix('electric-meter')->name('electricMeter.')->group(function () {

            Route::group(['permission' => 'view'], function () {
                Route::get('', 'BdcElectricMeter\ElectricMeterController@index')->name('index');
                Route::post('action', 'BdcElectricMeter\ElectricMeterController@action')->name('action');
                Route::get('import', 'BdcElectricMeter\ElectricMeterController@indexImport')->name('import');
                Route::get('download', 'BdcElectricMeter\ElectricMeterController@download')->name('download');
                Route::post('count', 'BdcElectricMeter\ElectricMeterController@countApartmentByCycleNameAndType')->name('count');
                Route::post('view_detail', 'BdcElectricMeter\ElectricMeterController@getDetail')->name('view_detail');
                Route::post('next', 'BdcElectricMeter\ElectricMeterController@next')->name('next');
                Route::post('previous', 'BdcElectricMeter\ElectricMeterController@previous')->name('previous');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('import_save', 'BdcElectricMeter\ElectricMeterController@import_save')->name('import_save');
                Route::post('handle_electric_water', 'BdcElectricMeter\ElectricMeterController@handle_electric_water')->name('handle_electric_water');
                Route::post('save', 'BdcElectricMeter\ElectricMeterController@save')->name('save');
            });
            Route::group(['permission' => 'update'], function () {
                  Route::post('removeImage', 'BdcElectricMeter\ElectricMeterController@removeImage')->name('removeImage');
            });
            Route::group(['permission' => 'delete'], function () {
                Route::post('delete', 'BdcElectricMeter\ElectricMeterController@delete')->name('delete');
            });
            Route::group(['permission' => 'import'], function () {
            });
            Route::group(['permission' => 'export'], function () {
                Route::get('export', 'BdcElectricMeter\ElectricMeterController@export')->name('export');
            });
        });
        Route::prefix('cycle_name')->name('cycle_name.')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('', 'LockCycleName\LockCycleNameController@index')->name('index');
                Route::post('action', 'LockCycleName\LockCycleNameController@action')->name('action');
                Route::get('generate_cycle_curent', 'LockCycleName\LockCycleNameController@generate_cycle_curent')->name('generate_cycle_curent');
            });
            Route::group(['permission' => 'insert'], function () {
                Route::post('save', 'LockCycleName\LockCycleNameController@save')->name('save');
            });
            Route::group(['permission' => 'update'], function () {
                Route::post('update', 'LockCycleName\LockCycleNameController@update')->name('update');
                Route::post('change_status', 'LockCycleName\LockCycleNameController@change_status')->name('change_status');
            });
            Route::group(['permission' => 'delete'], function () {
            });
            Route::group(['permission' => 'import'], function () {
            });
        });
        // building care kế toán v2
        Route::prefix('v2')->group(function () {

            Route::prefix('apartments')->name('v2.apartments.')->group(function () {

                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'Apartments\V2\ApartmentsController@index')->name('index');
                    Route::post('/', 'Apartments\V2\ApartmentsController@index')->name('index');
                    Route::get('/create', 'Apartments\V2\ApartmentsController@create')->name('insert');
                    Route::get('{id?}/edit', 'Apartments\V2\ApartmentsController@edit')->name('edit');
                    Route::get('/ajax_get_apartment', 'Apartments\V2\ApartmentsController@ajaxGetSelectApartment')->name('ajax_get_apartment');
                    Route::get('/ajax_get_apartment_with_place', 'Apartments\V2\ApartmentsController@ajaxGetSelectApartmentv2')->name('ajax_get_apartment_with_place');
                    Route::get('/ajax_get_building_place', 'Apartments\V2\ApartmentsController@ajaxGetSelectBuildingPlace')->name('ajax_get_building_place');
                    Route::get('/ajax_get_resident', 'Apartments\V2\ApartmentsController@ajaxGetSelectResident')->name('ajax_get_resident');
                    Route::get('/import', 'Apartments\V2\ApartmentsController@indexImport')->name('index_import');
                    Route::get('/download', 'Apartments\V2\ApartmentsController@download')->name('download');
                    Route::get('/download_file_update', 'Apartments\V2\ApartmentsController@downloadFileUpdate')->name('download_file_update');
                    Route::post('/action', 'Apartments\V2\ApartmentsController@action')->name('action');
                    Route::get('/ajax_get_customer', 'Apartments\V2\ApartmentsController@ajaxGetCustomer')->name('ajax_get_customer');
                    Route::get('/ajax_get_apartment_in_group', 'Apartments\V2\ApartmentsController@ajaxGetApartmentInGroup')->name('ajax_get_apartment_in_group');
                    Route::get('/report-apartment-company', 'Apartments\V2\ApartmentsController@reportApartmentbyCompany')->name('reportApartmentbyCompany');
                    Route::get('/get-apartment-company', 'Apartments\V2\ApartmentsController@getReportApartmentbyCompany')->name('getReportApartmentbyCompany');
                    Route::get('/get-apartment-company1', 'Apartments\V2\ApartmentsController@getReportApartmentbyCompanyUpgrade')->name('getReportApartmentbyCompanyUpgrade');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/create', 'Apartments\V2\ApartmentsController@save')->name('create');
                    Route::post('{id?}/add_file', 'Apartments\V2\ApartmentsController@createFile')->name('createfile');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('{id?}/edit', 'Apartments\V2\ApartmentsController@update')->name('update');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::get('{id?}/del', 'Apartments\V2\ApartmentsController@destroy')->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                    Route::post('/import_apartment', 'Apartments\V2\ApartmentsController@importFileApartment')->name('import_apartment');
                    Route::post('/import_update_apartment', 'Apartments\V2\ApartmentsController@importFileUpdateApartment')->name('import_update_apartment');
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('/export', 'Apartments\V2\ApartmentsController@export')->name('export');
                    Route::get('/export_apartment_group', 'Apartments\V2\ApartmentsController@export_apartment_group')->name('export_apartment_group');
                });
            });

            Route::prefix('apartment-group')->name('apartment-group.')->namespace('Apartments')->group(function () {
                Route::group(['permission' => 'view'], function () {
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/store', 'ApartmentGroupController@store')->name('store');
                    Route::post('/addApartment', 'ApartmentGroupController@addApartment')->name('addApartment');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('/update', 'ApartmentGroupController@update')->name('update');
                    Route::post('/status', 'ApartmentGroupController@status')->name('status');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::post('/delete', 'ApartmentGroupController@delete')->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                });
            });

            Route::prefix('apartment-handover')->name('apartment.handover.')->namespace('ApartmentHandOver')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'ApartmentHandOverController@index')->name('index');
                    Route::post('/', 'ApartmentHandOverController@index')->name('index');
                    Route::get('/create', 'ApartmentHandOverController@create')->name('insert');
                    Route::get('{id?}/edit', 'ApartmentHandOverController@edit')->name('edit');
                    Route::get('/ajax_get_apartment', 'ApartmentHandOverController@ajaxGetSelectApartment')->name('ajax_get_apartment');
                    Route::get('/ajax_get_apartment_with_place', 'ApartmentHandOverController@ajaxGetSelectApartmentv2')->name('ajax_get_apartment_with_place');
                    Route::get('/ajax_get_building_place', 'ApartmentHandOverController@ajaxGetSelectBuildingPlace')->name('ajax_get_building_place');
                    Route::get('/ajax_get_resident', 'ApartmentHandOverController@ajaxGetSelectResident')->name('ajax_get_resident');
                    Route::get('/import', 'ApartmentHandOverController@indexImport')->name('index_import');
                    Route::get('/download', 'ApartmentHandOverController@download')->name('download');
                    Route::post('/action', 'ApartmentHandOverController@action')->name('action');
                    Route::get('/ajax_get_customer', 'ApartmentHandOverController@ajaxGetCustomer')->name('ajax_get_customer');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/create', 'ApartmentHandOverController@save')->name('save');
                    Route::post('{id?}/add_file', 'ApartmentHandOverController@createFile')->name('createfile');
                    Route::post('/import_apartment', 'ApartmentHandOverController@importFile')->name('import_apartment');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('{id?}/edit', 'ApartmentHandOverController@update')->name('update');
                    Route::post('/change-status-confirm', 'ApartmentHandOverController@change_status_confirm')->name('change_status_confirm');
                    Route::post('/change-note-confirm', 'ApartmentHandOverController@change_note_confirm')->name('change_note_confirm');
                    Route::post('/change-success-handover', 'ApartmentHandOverController@change_success_handover')->name('change_success_handover');
                    Route::post('/change-date-handover', 'ApartmentHandOverController@change_date_handover')->name('change_date_handover');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::get('{id?}/del', 'ApartmentHandOverController@destroy')->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('/export', 'ApartmentHandOverController@export')->name('export');
                });
            });

            Route::prefix('customers')->name('v2.customers.')->namespace('Customers')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'Customers_v2Controller@index')->name('index');
                    Route::post('/', 'Customers_v2Controller@index')->name('index');
                    Route::get('/create-new', 'Customers_v2Controller@create')->name('create');
                    Route::get('{id?}/edit', 'Customers_v2Controller@edit')->name('edit');
                    Route::get('/import', 'Customers_v2Controller@indexImport')->name('index_import');
                    Route::post('/action', 'Customers_v2Controller@action')->name('action');
                    Route::get('/download', 'Customers_v2Controller@download')->name('download');
                    Route::get('/download-update', 'Customers_v2Controller@downloadUpdate')->name('downloadUpdate');
                    Route::post('/viewexcel', 'Customers_v2Controller@ViewExcel')->name('viewexcel');
                    Route::get('/ajax_check_type', 'Customers_v2Controller@ajaxCheckType')->name('ajax_check_type');
                    Route::get('/ajax_get_cus', 'Customers_v2Controller@ajaxGetCus')->name('ajax_get_cus');
                    Route::post('/resetPass', 'Customers_v2Controller@resetPass')->name('resetPass');
                    Route::post('/searchResident', 'Customers_v2Controller@searchResident')->name('searchResident');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/create', 'Customers_v2Controller@store')->name('insert');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('{id?}/edit', 'Customers_v2Controller@update')->name('update');
                    Route::post('/save_user', 'Customers_v2Controller@saveUserApartment')->name('save_user_apartment');
                    Route::post('/add_user_apartment', 'Customers_v2Controller@addInfoApartment')->name('add_user_apartment');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::get('/del_resident', 'Customers_v2Controller@destroyCustomerApartment')->name('del_customer');
                    Route::post('/del_user_apartment', 'Customers_v2Controller@deleteInfoApartment')->name('del_user_apartment');
                    Route::get('/delete', 'Customers_v2Controller@destroyCus')->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                    Route::post('/import_customer', 'Customers_v2Controller@importFileApartment')->name('import_customer');
                    Route::post('/indexImportUpdate', 'Customers_v2Controller@indexImportUpdate')->name('indexImportUpdate');
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('/export', 'Customers_v2Controller@export')->name('export');
                });
            });

            // User Register Form
            Route::prefix('user-request')->name('v2.user_request.')->namespace('UserRequest')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/registerVehicle', 'UserRequestController@registerVehicle')->name('registerVehicle');
                    Route::get('/detail_comments/{id}', 'UserRequestController@detail_comments')->name('detail_comments');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('change_status', 'UserRequestController@change_status')->name('change_status');
                });
            });
        });
        Route::prefix('v2')->middleware('route_accountant_v2')->group(function () {

            Route::prefix('bill')->name('v2.bill.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'Bill\V2\BillController@index')->name('index');
                    Route::get('/wait-for-confirm', 'Bill\V2\BillController@waitForConfirm')->name('waitForConfirm');
                    Route::get('/wait-for-confirm-edit-dateline', 'Bill\V2\BillController@waitForConfirmEditDateline')->name('waitForConfirmEditDateline');
                    Route::get('/wait-to-send', 'Bill\V2\BillController@waitToSend')->name('waitToSend');
                    Route::get('/list-pay', 'Bill\V2\BillController@listPay')->name('listPay');
                    Route::get('show/{id}', 'Bill\V2\BillController@show')->name('show');
                    Route::get('/reload-pdf', 'Bill\V2\BillController@reloadPdf')->name('reloadPdf');
                    Route::post('/reloadpdfv2', 'Bill\V2\BillController@reloadpdfv2')->name('reloadpdfv2');
                    Route::post('/action', 'Bill\V2\BillController@action')->name('action');
                });
                Route::group(['permission' => 'insert'], function () {
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('change-status', 'Bill\V2\BillController@changeStatus')->name('changeStatus');
                    Route::post('post-change-status', 'Bill\V2\BillController@postChangeStatus')->name('postChangeStatus');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::get('destroy/{id}', 'Bill\V2\BillController@destroyBill')->name('destroyBill');
                    Route::get('delete/{id}', 'Bill\V2\BillController@destroyBill')->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('exportFilter', 'Bill\V2\BillController@exportFilter')->name('exportFilter');
                    Route::get('exportFilterBangKeKhachHang', 'Bill\V2\BillController@exportFilterBangKeKhachHang')->name('exportFilterBangKeKhachHang');
                    Route::get('export', 'Bill\V2\BillController@export')->name('export');
                });
            });

            //receipt - v2
            Route::prefix('receipt')->name('v2.receipt.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'Receipt\V2\ReceiptController@index')->name('index');
                    Route::get('/index_v1', 'Receipt\V2\ReceiptController@index_v1')->name('index_v1');
                    Route::get('/show_huyphieuthu', 'Receipt\V2\ReceiptController@show_huyphieuthu')->name('show_huyphieuthu');
                    Route::get('create', 'Receipt\V2\ReceiptController@create')->name('create');
                    Route::get('phieu_dieu_chinh', 'Receipt\V2\ReceiptController@phieu_dieu_chinh')->name('phieu_dieu_chinh');
                    Route::get('create_payment_slip', 'Receipt\V2\ReceiptController@create_payment_slip')->name('create_payment_slip');
                    Route::post('save_payment_slip', 'Receipt\V2\ReceiptController@save_payment_slip')->name('save_payment_slip');
                    Route::get('filterByBill/{apartment_id}/{type}', 'Receipt\V2\ReceiptController@filterByBill')->name('filterByBill');
                    Route::post('viewer', 'Receipt\V2\ReceiptController@reviewReceipt')->name('viewer');
                    Route::get('/kyquy', 'Receipt\V2\ReceiptController@kyquy')->name('kyquy');
                    Route::get('create', 'Receipt\V2\ReceiptController@create')->name('create');
                    Route::get('create-old', 'Receipt\V2\ReceiptController@create_old')->name('create_old');
                    Route::get('create-phieu-chi', 'Receipt\V2\ReceiptController@createPhieuChi')->name('createPhieuChi');
                    Route::get('/{id}/show', 'Receipt\V2\ReceiptController@show')->name('show');
                    Route::get('pdf', 'Receipt\V2\ReceiptController@exportPDF')->name('exportPDF');
                    Route::get('create-receipt-previous', 'Receipt\V2\ReceiptController@createReceiptPrevious')->name('create_receipt_previous');
                    Route::get('edit/{id}', 'Receipt\V2\ReceiptController@edit')->name('edit');
                    Route::get('reload-pdf/{id}', 'Receipt\V2\ReceiptController@reload_pdf')->name('reload_pdf');
                    Route::get('getReceipt/{code}', 'Receipt\V2\ReceiptController@view_receipt')->name('receiptCode');
                    Route::get('edit-bill/{id}', 'Receipt\V2\ReceiptController@editBill')->name('editBill');
                    Route::get('/category', 'Receipt\V2\ReceiptController@indexCategory')->name('indexCategory');
                    Route::get('ajaxGetSelectTypeReceipt', 'Receipt\V2\ReceiptController@ajaxGetSelectTypeReceipt')->name('ajaxGetSelectTypeReceipt');
                    Route::post('action', 'Receipt\V2\ReceiptController@action')->name('action');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('save_adjustment_slip', 'Receipt\V2\ReceiptController@save_adjustment_slip')->name('save_adjustment_slip');
                    Route::post('save', 'Receipt\V2\ReceiptController@save')->name('save');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::put('update/{id}', 'Receipt\V2\ReceiptController@update')->name('update');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::post('/save_huyphieuthu', 'Receipt\V2\ReceiptController@save_huyphieuthu')->name('save_huyphieuthu');
                    Route::delete('destroy/{id}', 'Receipt\V2\ReceiptController@destroy')->name('destroy');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('export', 'Receipt\V2\ReceiptController@export')->name('export');
                    Route::get('exportFilterSoQuyTienMat', 'Receipt\V2\ReceiptController@exportFilterSoQuyTienMat')->name('exportFilterSoQuyTienMat');
                    Route::get('exportFilterSoQuyChuyenKhoan', 'Receipt\V2\ReceiptController@exportFilterSoQuyChuyenKhoan')->name('exportFilterSoQuyChuyenKhoan');
                    Route::get('exportFilterThuChi', 'Receipt\V2\ReceiptController@exportFilterThuChi')->name('exportFilterThuChi');
                    Route::get('export-thu-tien-tavico', 'Receipt\V2\ReceiptController@export_thu_tien_tavico')->name('export_thu_tien_tavico');
                    Route::get('exportFilterReceiptDeposit', 'Receipt\V2\ReceiptController@exportFilterReceiptDeposit')->name('exportFilterReceiptDeposit');
                    Route::get('exportDetailFilter', 'Receipt\V2\ReceiptController@exportDetailFilter')->name('exportDetailFilter');
                    Route::get('export-bang-ke-thu-tien', 'Receipt\V2\ReceiptController@exportDetailFilter_v2')->name('exportDetailFilter_v2');
                });
            });

            //receipt total
            Route::prefix('receipt-total')->name('v2.receipttotal.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'ReceiptTotal\V2\ReceiptTotalController@index')->name('index');
                    Route::get('/reportReceiptDeposit', 'ReceiptTotal\V2\ReceiptTotalController@reportReceiptDeposit')->name('reportReceiptDeposit');
                    Route::get('/report', 'ReceiptTotal\V2\ReceiptTotalController@report')->name('report');
                    Route::get('/exportReceiptDeposit', 'ReceiptTotal\V2\ReceiptTotalController@exportReceiptDeposit')->name('exportReceiptDeposit');
                });
            });

            Route::prefix('receipt-bankbook')->name('v2.receiptbankbook.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'ReceiptTotal\V2\ReceiptTotalController@indexbankbook')->name('index');
                    Route::get('/bankbook', 'ReceiptTotal\V2\ReceiptTotalController@indexbankbook')->name('index');
                });
            });

            Route::prefix('building-lock')->name('v2.buildinglock.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'Service\V2\ServiceBuildingController@buildinglock')->name('index');
                });
            });
            //debit
            Route::prefix('debit')->name('v2.debit.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'Debit\V2\DebitController@index')->name('index');
                    Route::post('/', 'Debit\V2\DebitController@index')->name('index');
                    Route::get('detail/', 'Debit\V2\DebitController@detail')->name('detail');
                    Route::get('/detail-v2', 'Debit\V2\DebitController@detailVersion2')->name('detailVersion2');
                    Route::post('detail/action', 'Debit\V2\DebitController@action')->name('detail.action');
                    Route::post('get-apartment', 'Debit\V2\DebitController@getApartment')->name('getApartment');
                    Route::get('show/{id}', 'Debit\V2\DebitController@show')->name('show');
                    Route::get('processDebitDetail', 'Debit\V2\DebitController@processDebitDetail')->name('processDebitDetail');
                    Route::get('reloadProcessDebitDetail', 'Debit\V2\DebitController@reloadProcessDebitDetail')->name('reloadProcessDebitDetail');
                    Route::get('detail-service', 'Debit\V2\DebitController@detailDebit')->name('detailDebit');
                    Route::get('detail-service-action', 'Debit\V2\DebitController@detailDebitActionRecord')->name('detail_service_action');
                    Route::get('detail-service/edit/version', 'Debit\V2\DebitController@detailDebitEditVersion')->name('detailDebit.edit.version');
                    Route::post('detail-service/edit', 'Debit\V2\DebitController@detailDebitEdit')->name('detailDebit.edit');
                    Route::post('detail-service/action', 'Debit\V2\DebitController@detailDebitAction')->name('detail-service.action');
                    Route::post('detail-service/action-record', 'Debit\V2\DebitController@ActionRecordDebit')->name('detail-service.action_record');
                    Route::get('/debit-logs', 'Debit\V2\DebitController@debitLogs')->name('debitLogs');
                    Route::get('/total', 'Debit\V2\DebitController@total')->name('total');
                    Route::get('/total/action', 'Debit\V2\DebitController@totalDebitAction')->name('total.action');
                    Route::get('/export-excel-total', 'Debit\V2\DebitController@exportExcelGeneralDetailTotal')->name('exportExcelTotal');
                    Route::get('/general-detail', 'Debit\V2\DebitController@generalDetail')->name('generalDetail');
                    Route::get('/general-detail/action', 'Debit\V2\DebitController@generalDetailDebitAction')->name('generalDetail.action');
                    Route::post('/send-message', 'Debit\V2\DebitController@sendMessage')->name('sendMessage');
                    Route::get('/total-tien-thua', 'Debit\V2\DebitController@total_tienthua')->name('total_tienthua');
                    Route::get('/detail-tien-thua', 'Debit\V2\DebitController@detail_tienthua')->name('detail_tienthua');
                    Route::get('/export-excess-cash', 'Debit\V2\DebitController@export_excess_cash')->name('export_excess_cash');
                    Route::get('/export-total-excess-cash', 'Debit\V2\DebitController@export_total_excess_cash')->name('export_total_excess_cash');
                    Route::get('/show-tien-thua', 'Debit\V2\DebitController@show_tienthua')->name('show_tienthua');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('detail-handling', 'Debit\V2\DebitController@detailHandling')->name('detail-handling');
                    Route::post('detail-handling-year', 'Debit\V2\DebitController@detailHandlingYear')->name('detail-handling-year');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::put('detail-service/update/{id}', 'Debit\V2\DebitController@detailDebitUpdate')->name('detail-service.update');
                    Route::post('/save-tien-thua', 'Debit\V2\DebitController@save_phanbo')->name('save_phanbo');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::get('{id?}/delete', 'Debit\V2\DebitController@destroydebitDetail')->name('detailDebit.delete');
                    Route::get('{id?}/delete-version', 'Debit\V2\DebitController@destroydebitDetailV2')->name('detailDebit.delete.version');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('export', 'Debit\V2\DebitController@export')->name('export');
                    Route::get('exportShow/{id}', 'Debit\V2\DebitController@exportDetailShowApartment')->name('exportShow');
                    Route::get('export-excel', 'Debit\V2\DebitController@exportExcel')->name('exportExcel');
                    Route::get('export-excel-total-service', 'Debit\V2\DebitController@exportExcel_v2')->name('exportExcel_v2');
                    Route::get('exportFilter', 'Debit\V2\DebitController@exportFilter')->name('exportFilter');
                    Route::get('/export-excel-general-detail', 'Debit\V2\DebitController@exportExcelGeneralDetail')->name('exportExcelGeneralDetail');
                    Route::get('/export-excel-total-group-service', 'Debit\V2\DebitController@exportExcelGeneralDetailTotalByTypeService')->name('exportExcelGeneralDetailTotalByTypeService');
                    Route::get('/export-excel-total-group-apartment', 'Debit\V2\DebitController@exportExcelGeneralDetailTotalByTypeApartment')->name('exportExcelGeneralDetailTotalByTypeApartment');
                });
            });

            // progressive

            Route::prefix('progressive')->name('v2.progressive.')->group(function () {

                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'BdcProgressives\V2\BdcProgressiveController@index')->name('index');
                    Route::get('/create', 'BdcProgressives\V2\BdcProgressiveController@create')->name('create');
                    Route::get('/edit/{id}', 'BdcProgressives\V2\BdcProgressiveController@edit')->name('edit');
                    Route::get('/import-excel', 'BdcProgressives\V2\BdcProgressiveController@importExcel')->name('importexcel');
                    Route::get('/import-phi-dau-ky', 'BdcProgressives\V2\BdcProgressiveController@importExcelPhiDauKy')->name('importexcelphidauky');
                    Route::get('/download', 'BdcProgressives\V2\BdcProgressiveController@download')->name('download');
                    Route::get('/download-phi-dau-ky', 'BdcProgressives\V2\BdcProgressiveController@downloadphidauky')->name('downloadphidauky');
                    Route::post('/action', 'BdcProgressives\V2\BdcProgressiveController@action')->name('action');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/store', 'BdcProgressives\V2\BdcProgressiveController@store')->name('store');
                    Route::post('/import-phi-dau-ky-post', 'BdcProgressives\V2\BdcProgressiveController@importFileExcelPhiDauKyPost')->name('importexcelphidaukypost');
                    Route::post('/import-excel-post', 'BdcProgressives\V2\BdcProgressiveController@importFileExcelPost')->name('importexcelpost');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::put('/update/{id}', 'BdcProgressives\V2\BdcProgressiveController@update')->name('update');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::delete('/delete/{id}', 'BdcProgressives\V2\BdcProgressiveController@delete')->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                });
            });


            Route::prefix('vehicles')->name('v2.vehicles.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'Vehicles\V2\VehiclesController@index')->name('index');
                    Route::post('/', 'Vehicles\V2\VehiclesController@index')->name('index');
                    Route::get('{id?}/edit', 'Vehicles\V2\VehiclesController@edit')->name('edit');
                    Route::get('/import', 'Vehicles\V2\VehiclesController@indexImport')->name('index_import');
                    Route::get('/download', 'Vehicles\V2\VehiclesController@download')->name('download');
                    Route::get('/ajax_check_number', 'Vehicles\V2\VehiclesController@ajaxCheckNumber')->name('ajax_check_number');
                    Route::post('/action', 'Vehicles\V2\VehiclesController@action')->name('action');
                    Route::post('/getPriceVehicle', 'Vehicles\V2\VehiclesController@getPriceVehicle')->name('getPriceVehicle');
                    Route::post('/checkNumberVehicle', 'Vehicles\V2\VehiclesController@checkNumberVehicle')->name('checkNumberVehicle');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/create', 'Vehicles\V2\VehiclesController@create')->name('insert');
                    Route::post('/save_vehicle', 'Vehicles\V2\VehiclesController@saveVehicleApartment')->name('save_vehicle_apartment');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::put('/status', 'Vehicles\V2\VehiclesController@status')->name('status');
                    Route::post('{id?}/edit', 'Vehicles\V2\VehiclesController@update')->name('update');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::get('{id?}/del_vehicle', 'Vehicles\V2\VehiclesController@destroyVehicleApartment')->name('del_vehicle');
                    Route::get('{id?}/delete', 'Vehicles\V2\VehiclesController@destroy')->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                    Route::post('/import_vehicle', 'Vehicles\V2\VehiclesController@importFileApartment')->name('import_vehicle');
                    Route::post('importExcel', 'Vehicles\V2\VehiclesController@importExcel')->name("importExcel");
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('/export', 'Vehicles\V2\VehiclesController@export')->name('export');
                    Route::get('report_export', 'Vehicles\V2\VehiclesController@report_export')->name("report_export");
                    Route::get('exportChoppyByTypeVehicle', 'Vehicles\V2\VehiclesController@exportChoppyByTypeVehicle')->name("exportChoppyByTypeVehicle");
                });
            });

            Route::prefix('vehiclecategory')->name('v2.vehiclecategory.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'VehicleCategory\V2\VehicleCategoryController@index')->name('index');
                    Route::post('/', 'VehicleCategory\V2\VehicleCategoryController@index')->name('index');
                    Route::get('{id?}/edit', 'VehicleCategory\V2\VehicleCategoryController@edit')->name('edit');
                    Route::get('/ajax_get_vehicle_cate', 'VehicleCategory\V2\VehicleCategoryController@ajaxGetSelectVehicleCate')->name('ajax_get_vehicle_cate');
                    Route::post('/checkVehicleNameCategory', 'VehicleCategory\V2\VehicleCategoryController@checkVehicleNameCategory')->name('checkVehicleNameCategory');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/create', 'VehicleCategory\V2\VehicleCategoryController@create')->name('insert');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::post('{id?}/edit', 'VehicleCategory\V2\VehicleCategoryController@update')->name('update');
                    Route::put('/status', 'VehicleCategory\V2\VehicleCategoryController@status')->name('status');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::get('{id?}/delete', 'VehicleCategory\V2\VehicleCategoryController@destroy')->name('delete');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                });
            });
            //service apartment
            Route::prefix('service-apartment')->name('v2.service.apartment.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'Service\V2\ServiceApartmentController@index')->name('index');
                    Route::post('/', 'Service\V2\ServiceApartmentController@index')->name('filter');
                    Route::get('/create', 'Service\V2\ServiceApartmentController@create')->name('create');
                    Route::get('/edit/{id}', 'Service\V2\ServiceApartmentController@edit')->name('edit');
                    Route::get('/show/{id}', 'Service\V2\ServiceApartmentController@show')->name('show');
                    Route::post('/action', 'Service\V2\ServiceApartmentController@action')->name('action');
                    Route::post('get-vehicle/', 'Service\V2\ServiceApartmentController@getVehicleApartment')->name('getVehicle');
                    Route::post('get-progress/', 'Service\V2\ServiceApartmentController@getProgressApartment')->name('getProgressApartment');
                    Route::post('get-service/', 'Service\V2\ServiceApartmentController@getServiceApartmentAjax')->name('getServiceApartmentAjax');
                    Route::post('/check_type_electric_water', 'Service\V2\ServiceApartmentController@check_type_electric_water')->name('check_type_electric_water');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/store', 'Service\V2\ServiceApartmentController@store')->name('store');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::put('/update/{id}', 'Service\V2\ServiceApartmentController@update')->name('update');
                    Route::put('/change-status', 'Service\V2\ServiceApartmentController@changeStatus')->name('status');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::get('/destroy/{id}', 'Service\V2\ServiceApartmentController@destroy')->name('destroy');
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                    Route::get('export', 'Service\V2\ServiceApartmentController@export')->name('export');
                });
            });

            //service-building
            Route::prefix('service-building')->name('v2.service.building.')->group(function () {

                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'Service\V2\ServiceBuildingController@index')->name('index');
                    Route::get('/ajaxSelectTypeService', 'Service\V2\ServiceBuildingController@ajaxSelectTypeService')->name('ajaxSelectTypeService');
                    Route::post('/', 'Service\V2\ServiceBuildingController@index')->name('filter');
                    Route::get('/create', 'Service\V2\ServiceBuildingController@create')->name('create');
                    Route::get('/edit/{id}', 'Service\V2\ServiceBuildingController@edit')->name('edit');
                    Route::get('/choose', 'Service\V2\ServiceBuildingController@choose')->name('choose');
                    Route::get('/action', 'Service\V2\ServiceBuildingController@action')->name('action');
                    Route::get('/import-excel', 'Service\V2\ServiceBuildingController@importExcel')->name('importexcel');
                    Route::get('/download', 'Service\V2\ServiceBuildingController@download')->name('download');
                    Route::get('/category', 'Service\V2\ServiceBuildingController@indexCategory')->name('indexCategory');
                    Route::get('/check_index_accounting', 'Service\V2\ServiceBuildingController@check_index_accounting')->name('check_index_accounting');
                    Route::post('/set-type-tinh-cong-no', 'Service\V2\ServiceBuildingController@set_type_tinh_cong_no')->name('set_type_tinh_cong_no');
                    Route::post('/update_index_accounting', 'Service\V2\ServiceBuildingController@update_index_accounting')->name('update_index_accounting');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/store', 'Service\V2\ServiceBuildingController@store')->name('store');
                    Route::post('/post-choose', 'Service\V2\ServiceBuildingController@postChoose')->name('postChoose');
                });
                Route::group(['permission' => 'update'], function () {
                    Route::put('/update/{id}', 'Service\V2\ServiceBuildingController@update')->name('update');
                    Route::put('/change-status', 'Service\V2\ServiceBuildingController@changeStatus')->name('status');
                });
                Route::group(['permission' => 'delete'], function () {
                    Route::get('/destroy/{id}', 'Service\V2\ServiceBuildingController@destroy')->name('destroy');
                });
                Route::group(['permission' => 'import'], function () {
                    Route::post('/importApartmentService', 'Service\V2\ServiceBuildingController@importApartmentService')->name('importApartmentService');
                });
                Route::group(['permission' => 'export'], function () {
                });
            });
            //provisional receipt
            Route::prefix('provisional-receipt')->name('v2.provisionalreceipt.')->group(function () {
                Route::group(['permission' => 'view'], function () {
                    Route::get('/', 'BdcProvisionalReceipt\V2\ProvisionalReceiptController@index')->name('index');
                    Route::get('/create', 'BdcProvisionalReceipt\V2\ProvisionalReceiptController@create')->name('create');
                    Route::get('/create-payment-slip', 'BdcProvisionalReceipt\V2\ProvisionalReceiptController@createPaymentSlip')->name('createPaymentSlip');
                });
                Route::group(['permission' => 'insert'], function () {
                    Route::post('/store', 'BdcProvisionalReceipt\V2\ProvisionalReceiptController@store')->name('store');
                    Route::post('/storePaymentSlip', 'BdcProvisionalReceipt\V2\ProvisionalReceiptController@storePaymentSlip')->name('storePaymentSlip');
                });
                Route::group(['permission' => 'update'], function () {
                });
                Route::group(['permission' => 'delete'], function () {
                });
                Route::group(['permission' => 'import'], function () {
                });
                Route::group(['permission' => 'export'], function () {
                });
            });
        });
        Route::prefix('notification')->namespace('Backend')->name('notification.')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('/', 'CampainController@index')->name('index');
                Route::get('/detail/{id}', 'CampainController@getCampainDetail')->name('campain-detail');
                Route::post('/action', 'CampainController@action')->name('action');
            });
        });
    });
    // Debit log
    Route::prefix('debitlog')->name('debitlog.')->group(function () {
        Route::group(['permission' => 'import'], function () {
            Route::get('/importDienNuoc', 'BdcDebitLogs\DebitLogsController@importDienNuoc')->name('importDienNuoc');
            Route::post('/action', 'BdcDebitLogs\DebitLogsController@action')->name('action');
        });
    });
    Route::prefix('users')->namespace('Backend')->name('users.')->group(function () {
        Route::get('profile', 'UserController@profile')->name('profile');
        Route::post('profile', 'UserController@processProfile')->name('processProfile');
        Route::post('upload_avatar', 'UserController@upload_avatar')->name('avatar');
        Route::post('validator-pass', 'UserController@validator_pass')->name('validator');
        Route::post('update-pass', 'UserController@resetPass')->name('update-pass');
    });
    Route::prefix('configs')->name('configs.')->namespace('Config')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/create-view', 'ConfigController@create_view')->name('create_view');
            Route::get('/view', 'ConfigController@view')->name('view');
        });
    });
    Route::prefix('transaction-payment')->name('transactionpayment.')->namespace('TransactionPayment')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/list', 'TransactionPaymentController@index')->name('index')->middleware('route_permision');
            Route::get('/service-detail-payment', 'TransactionPaymentController@service_detail_payment')->name('service_detail_payment')->middleware('route_permision');
            Route::get('/export-transaction-payment', 'TransactionPaymentController@exportTransactionPayment')->name('export_transaction_payment')->middleware('route_permision');
            Route::get('/export-service-detail-payment', 'TransactionPaymentController@exportTransactionPaymentByServiceReceipt')->name('export_service_detail_payment')->middleware('route_permision');
            Route::post('/action-transaction-payment', 'TransactionPaymentController@action_transaction_payment')->name('action_transaction_payment')->middleware('route_permision');
            Route::post('/action-service-detail-payment', 'TransactionPaymentController@action_service_detail_payment')->name('action_service_detail_payment')->middleware('route_permision');
        });
        Route::group(['permission' => 'insert'], function () {
        });
        Route::group(['permission' => 'update'], function () {
            Route::get('/status-confirm-success', 'TransactionPaymentController@status_confirm_success')->name('status_confirm_success')->middleware('route_permision');
            Route::put('/status-confirm-reject', 'TransactionPaymentController@status_confirm_reject')->name('status_confirm_reject')->middleware('route_permision');
        });
        Route::group(['permission' => 'delete'], function () {
        });
        Route::group(['permission' => 'import'], function () {
        });
        Route::group(['permission' => 'export'], function () {
        });
    });
    Route::prefix('demo')->name('demo.')->group(function () {
        Route::get('/index', 'Demo\DemoPostController@index')->name('index');
        Route::post('/save_debit', 'Demo\DemoPostController@save_debit')->name('save_debit');
    });
    Route::prefix('ajax')->name('ajax.')->group(function () {
        Route::get('/getServiceApartmentAjaxV2', 'Service\V2\ServiceApartmentController@getServiceApartmentAjaxV2')->name('getServiceApartmentAjaxV2');
        Route::get('/download_qrcode', 'BuildingInfo\BuildingInfoController@download_qrcode')->name('download_qrcode');
        Route::post('/change-status-app', 'Department\DepartmentController@changeStatusApp')->name('change-status-app');
        Route::post('/change-status-notify', 'Department\DepartmentController@changeStatusNotify')->name('change-status-notify');
        Route::get('/ajaxGetStaffByCompany', 'Company\CompanyController@ajaxGetStaffByCompany')->name('ajaxGetStaffByCompany');
        Route::get('/ajaxGetUrbanByCompany', 'Company\CompanyController@ajaxGetUrbanByCompany')->name('ajaxGetUrbanByCompany');
        Route::any('/ajaxbuildingplace', 'Backend\PostController@ajaxBuildingPlace')->name('buildingplace');
        Route::any('/ajaxcustomers', 'Backend\PostController@ajaxCustomers')->name('ajaxcustomers');
        Route::any('/ajaxapartment', 'Backend\PostController@ajaxApartment')->name('ajaxapartment');
        Route::get('/ajax_get_apartment', 'Apartments\ApartmentsController@ajaxGetSelectApartment')->name('ajax_get_apartment');
        Route::get('/ajax_get_apartment_with_place', 'Apartments\ApartmentsController@ajaxGetSelectApartmentv2')->name('ajax_get_apartment_with_place');
        Route::get('/ajax_get_building_place', 'Apartments\ApartmentsController@ajaxGetSelectBuildingPlace')->name('ajax_get_building_place');
        Route::get('/ajax_get_vehicle_cate', 'VehicleCategory\V2\VehicleCategoryController@ajaxGetSelectVehicleCate')->name('ajax_get_vehicle_cate');
        Route::get('/ajaxGetAsset', 'BuildingPlace\BuildingPlaceController@ajaxGetAsset')->name('ajaxGetAsset');
        Route::get('/ajaxGetAssetDetail', 'BuildingPlace\BuildingPlaceController@ajaxGetAssetDetail')->name('ajaxGetAssetDetail');
        Route::get('/ajaxGetAssetDetailByName', 'BuildingPlace\BuildingPlaceController@ajaxGetAssetDetailByName')->name('ajaxGetAssetDetailByName');
        Route::get('/ajaxGetAssetCategory', 'BuildingPlace\BuildingPlaceController@ajaxGetAssetCategory')->name('ajaxGetAssetCategory');
        Route::get('/ajaxGetAssetArea', 'BuildingPlace\BuildingPlaceController@ajaxGetAssetArea')->name('ajaxGetAssetArea');
        Route::get('/ajaxGetFloor', 'BuildingPlace\BuildingPlaceController@ajaxGetFloor')->name('ajaxGetFloor');
        Route::get('/ajaxGetDepartment', 'BuildingPlace\BuildingPlaceController@ajaxGetDepartment')->name('ajaxGetDepartment');
        Route::get('/ajaxGetCheckList', 'BuildingPlace\BuildingPlaceController@ajaxGetCheckList')->name('ajaxGetCheckList');
        Route::get('/ajax_get_service', 'Service\ServiceApartmentController@ajaxGetSelectService')->name('ajax_get_service');
        Route::get('/ajaxGetServiceApartment', 'Service\V2\ServiceApartmentController@ajaxGetServiceApartment')->name('ajaxGetServiceApartment');
        Route::get('/ajaxGetPromotion', 'BuildingPlace\BuildingPlaceController@ajaxGetPromotion')->name('ajaxGetPromotion');
        Route::get('/ajaxGetUrban', 'BuildingPlace\BuildingPlaceController@ajaxGetUrban')->name('ajaxGetUrban');
        Route::post('/ajax_check_type_electric_water', 'Service\V2\ServiceApartmentController@check_type_electric_water')->name('check_type_electric_water');
        Route::get('/ajaxGetCateTask', 'BuildingPlace\BuildingPlaceController@ajaxGetCateTask')->name('ajaxGetCateTask');
        Route::get('/get_user_building', 'WorkDiary_v2\WorkDiary_v2Controller@get_user_building')->name('get_user_building');
        Route::get('/ajax_get_feedback', 'Feedback\FeedbackController@ajaxGetSelectFeedback')->name('ajax_get_feedback');
        Route::post('/ajax_create_task', 'WorkDiary_v2\WorkDiary_v2Controller@saveTask')->name('saveTask');
    });
    // Activity-log
    Route::prefix('activity-log')->name('activitylog.')->namespace('ActivityLog')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('', 'ActivityLogController@index')->name('index');
            Route::get('log-tool', 'ActivityLogController@LogActiveTool')->name('LogActiveTool');
            Route::get('log-action', 'ActivityLogController@LogActionDB')->name('LogActionDB');
            Route::get('/ajaxGetSelectTable', 'ActivityLogController@ajaxGetSelectTable')->name('ajaxGetSelectTable');
        });
    });
});
Route::prefix('admin/vnpay')->name('vnpay.')->group(function () {
//    Route::get('/', 'VnpayController@index')->name('index');
    Route::get('/ipn-vnpay-bdc', 'Vnpay\VnpayController@returnIpnPayment')->name('ipnvnpay');
});
Route::prefix('admin/vnpay-app')->name('vnpay.')->group(function () {
    Route::get('/', 'Vnpay\VnpayController@indexCustomer')->name('index_customer');
//    Route::get('/ipn-vnpay-bdc', 'Vnpay\VnpayController@returnIpnPayment')->name('ipnvnpay');
});
Route::prefix('admin/bill/detail/')->name('billcode.')->group(function () {
    Route::get('/{billcode}', 'Bill\BillDetailController@index')->name('index');
});
Route::prefix('admin/receipt/detail/')->name('receiptcode.')->group(function () {
    Route::get('/{code}', 'Receipt\ReceiptDetailController@view_receipt')->name('receiptCode');
});

Route::post('admin/building/setBuildingId', 'BuildingInfo\BuildingInfoController@setBuildingId')->name('setBuildingId');

Route::prefix('admin/transaction-payment')->name('transactionpayment.')->namespace('TransactionPayment')->group(function () {
    Route::get('/store', 'TransactionPaymentCallBackController@store')->name('store');
});
// khách hàng đánh giá
Route::prefix('audit-service')->namespace('CustomerRatedServices')->group(function () {
    Route::get('/', 'CustomerRatedServiceController@index')->name('index');
    Route::post('/store', 'CustomerRatedServiceController@store')->name('store');
    Route::get('/del', 'CustomerRatedServiceController@del')->name('del');
});
// khách hàng cài ứng dụng
Route::prefix('app')->namespace('CustomerRatedServices')->group(function () {
    Route::get('/install', 'CustomerRatedServiceController@installApp')->name('installApp');
});
// Frontend
Route::namespace('Frontend')
    ->middleware('minify')
    ->group(base_path('routes/web-frontend.php'));

Route::prefix('debug')->group(function () {
    Route::get('/clearDebit', 'Debug\DebugController@clearDebit')->name('index');
    Route::get('/clearkeyredis', 'Debug\DebugController@clearKeyRedis')->name('clearKeyRedis');
    Route::get('/sendSMS', 'Debug\DebugController@sendSOAP')->name('sendSOAP');
    Route::get('/kiem-tra-email', 'Debug\DebugController@checkEmailExist')->name('checkEmailExist');
    Route::get('/test', 'Debug\DebugController@test')->name('test');
    Route::get('/test-time-out', 'Debug\DebugController@test_time_out')->name('test_time_out');
    Route::get('/test-fcm', 'Debug\DebugController@testPush')->name('testPush');
    Route::get('/maintenance', 'Debug\DebugController@Maintain')->name('Mantain');
    Route::get('/get_cookie', 'Debug\DebugController@get_cookie')->name('get_cookie');
    Route::get('/set_cookie', 'Debug\DebugController@set_cookie')->name('set_cookie');
    Route::get('/install_command', 'Debug\DebugController@install_command')->name('install_command');
    Route::get('/install_command_migrate', 'Debug\DebugController@install_command_migrate')->name('install_command_migrate');
    Route::get('/delete_log_debit', 'Debug\DebugController@delete_log_debit')->name('delete_log_debit');
    Route::get('/check_receipt/{code}', 'Debug\DebugController@check_view_receipt')->name('check_view_receipt');
    Route::get('/clearKeysRedis', 'Debug\DebugController@clearKeysRedis')->name('clearKeysRedis');
    Route::get('/insertIntoBdcDebitV2', 'Debug\DebugController@insertIntoBdcDebitV2')->name('insertIntoBdcDebitV2');
    Route::get('/insertIntoBdcDebitV2ByPriceOne', 'Debug\DebugController@insertIntoBdcDebitV2ByPriceOne')->name('insertIntoBdcDebitV2ByPriceOne');
    Route::get('/addExcessCash', 'Debug\DebugController@addExcessCash')->name('addExcessCash');
    Route::get('/addExcessCashDetail', 'Debug\DebugController@addExcessCashDetail')->name('addExcessCashDetail');
    Route::get('/tool_update_cycle_name', 'Debug\DebugController@updateCycleNamePaymentDetailByCreateDateReceipt')->name('updateCycleNamePaymentDetailByCreateDateReceipt');
    Route::get('/updatePaidDebitCycleNameApartment', 'Debug\DebugController@updatePaidDebitCycleNameApartment')->name('updatePaidDebitCycleNameApartment');
    Route::get('/restore', 'Debug\DebugController@restore')->name('restore');
    Route::get('/convertVehicleCategory', 'Debug\DebugController@convertVehicleCategory')->name('convertVehicleCategory');
    Route::get('/checkConvertVehicleCategory', 'Debug\DebugController@checkConvertVehicleCategory')->name('checkConvertVehicleCategory');
    Route::get('/changeCycleNameDebitDetail', 'Debug\DebugController@changeCycleNameDebitDetail')->name('changeCycleNameDebitDetail');
    Route::get('/insertIntoBdcDebitV3', 'Debug\DebugController@insertIntoBdcDebitV3')->name('insertIntoBdcDebitV3');
    Route::get('/insertIntoBdcDebitFirstCycleName', 'Debug\DebugController@insertIntoBdcDebitFirstCycleName')->name('insertIntoBdcDebitFirstCycleName');
    Route::get('/BackInsertIntoBdcDebitFirstCycleName', 'Debug\DebugController@BackInsertIntoBdcDebitFirstCycleName')->name('BackInsertIntoBdcDebitFirstCycleName');
    Route::get('/checkdebit', 'Debug\DebugController@checkdebit')->name('checkdebit');
    Route::get('/updateApartmentId', 'Debug\DebugController@updateApartmentId')->name('updateApartmentId');
    Route::get('/sendNotifyV2', 'Debug\DebugController@sendNotifyV2')->name('sendNotifyV2');
    Route::get('/createDebitV2ByApartmentId', 'Debug\DebugController@createDebitV2ByApartmentId')->name('createDebitV2ByApartmentId');
    Route::get('/insertIntoBdcDebitFirstCycleNameByApartmentId', 'Debug\DebugController@insertIntoBdcDebitFirstCycleNameByApartmentId')->name('insertIntoBdcDebitFirstCycleNameByApartmentId');
    Route::get('/run_command', 'Debug\DebugController@run_command')->name('run_command');
    Route::get('/removeBillWithDebitNotExit', 'Debug\DebugController@removeBillWithDebitNotExit')->name('removeBillWithDebitNotExit');
    Route::get('/getKeysRedis', 'Debug\DebugController@getKeysRedis')->name('getKeysRedis');
    Route::get('/getEntireKeysRedis', 'Debug\DebugController@getEntireKeysRedis')->name('getEntireKeysRedis');
    Route::get('/addcoin', 'Debug\DebugController@addcoin')->name('addcoin');
    Route::get('/generate', 'Debug\DebugController@generate')->name('generate');
    Route::get('/getPassUserV2', 'Debug\DebugController@getPassUserV2')->name('getPassUserV2');
    Route::get('/reportApartmentByBuilding', 'Debug\DebugController@reportApartmentByBuilding')->name('reportApartmentByBuilding');
    Route::get('/reportApartmentByBuildingV2', 'Debug\DebugController@reportApartmentByBuildingV2')->name('reportApartmentByBuildingV2');
    Route::get('/setConfigBank', 'Debug\DebugController@setConfigBank')->name('setConfigBank');
    Route::get('/getAllKeysRedis', 'Debug\DebugController@getAllKeysRedis')->name('getAllKeysRedis');
    Route::get('/SendMailCustom', 'Debug\DebugController@SendMailCustom')->name('SendMailCustom');
    Route::get('/SendMailCustomV2', 'Debug\DebugController@SendMailCustomV2')->name('SendMailCustomV2');
    Route::get('/SendNotifyCustomV2', 'Debug\DebugController@SendNotifyCustomV2')->name('SendNotifyCustomV2');
    Route::get('/SendNotifyCustomV3', 'Debug\DebugController@SendNotifyCustomV3')->name('SendNotifyCustomV3');
    Route::get('/SendNotifyCustomV4', 'Debug\DebugController@SendNotifyCustomV4')->name('SendNotifyCustomV4');
    Route::get('/clearKeyApartment', 'Debug\DebugController@clearKeyApartment')->name('clearKeyApartment');
    Route::get('/changeMeter', 'Debug\DebugController@changeMeter')->name('changeMeter');
    Route::get('debt-brick', 'HistoryTransactionAccounting\HistoryTransactionAccountingController@indexDebtBrick')->name('indexDebtBrick');
    Route::get('export_debt_brick', 'HistoryTransactionAccounting\HistoryTransactionAccountingController@export_debt_brick')->name('admin.export_debt_brick');
    Route::post('confirm-debt-brick', 'HistoryTransactionAccountingController@create_debt_brick')->name('admin.action.debt_brick');

    Route::group(['permission' => 'view'], function () {
    });
    Route::group(['permission' => 'insert'], function () {
    });
    Route::group(['permission' => 'update'], function () {
    });
    Route::group(['permission' => 'delete'], function () {
    });
    Route::group(['permission' => 'import'], function () {
    });
    Route::group(['permission' => 'export'], function () {
    });

    Route::get('/delcoin', 'Debug\DebugController@delCoin')->name('delcoin');
    Route::get('/test_send_mail', 'Debug\DebugController@testSendMail')->name('testSendMail');
    Route::get('/checkInfoCampainDetail', 'Debug\DebugController@checkInfoCampainDetail')->name('checkInfoCampainDetail');
    Route::get('/checkCampain', 'Debug\DebugController@checkCampain')->name('checkCampain');
    Route::get('/sub_coin', 'Debug\DebugController@sub_coin')->name('sub_coin');
});
Route::prefix('admin')->group(function () {
    Route::get('/maintenance', 'Debug\DebugController@getMaintain')->name('admin.maintenance');
});
