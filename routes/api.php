<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;

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

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Passport::routes();

    Route::namespace('Auth\\Api')->group(function () {

        Route::group(['permission' => 'view'], function () {
            Route::post('login', 'ApiController@login_v2')->name('login_v2');
            Route::post('info', 'ApiController@getUserInfoV2')->name('getUserInfoV2');
            Route::post('register', 'ApiController@register')->name('app.update');
            Route::post('/change-profile', 'ApiController@changeProfile')->name('changeProfile');
            Route::post('/send-otp', 'ResetPasswordController@sendOTPApi');
            Route::post('/check-otp', 'ResetPasswordController@CheckOTP');
            Route::post('/otp-login', 'ResetPasswordController@LoginWithOTPApi');
        });

        Route::group(['middleware' => 'auth:public_user_v2'], function () {
            Route::group(['permission' => 'view'], function () {
                Route::get('logout', 'ApiController@logout')->name('logout');
                Route::post('/newpass-otp', 'ResetPasswordController@newpass');
                Route::get('user', 'ApiController@getAuthUser');
            });
        });

        Route::middleware('auth:public_user_v2')->group(function () {
            Route::group(['permission' => 'view'], function () {
                Route::put('change-password', 'ResetPasswordController@reset_v2')->name('change.password.test');
            });
        });

    });
    Route::group(['permission' => 'view'], function () {
        Route::post('reset-password/new', 'Users\Api\ForgotPasswordController@forgotPassword');
    });

    Route::prefix('upload')->name('upload.')->namespace('Upload')->group(function () {
        Route::post('/', 'UploadController@upload')->name('upload');
        Route::post('/file', 'UploadController@upload_v2')->name('upload_v2');
    });

    // khách hàng đánh giá
    Route::prefix('customer_rated_service')->namespace('CustomerRatedServices\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'CustomerRatedServiceController@index')->name('index');
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('/', 'CustomerRatedServiceController@add')->name('add');
        });

    });
    // danh sách nhân viên theo bộ phận
    Route::prefix('building')->namespace('Home\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/user-department', 'HomeController@getListUserByDepartment');
        });
    });
    // danh bộ phận theo tòa nhà
    Route::prefix('department')->namespace('Department\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/filter-building', 'DepartmentController@getListByBuilding');
        });
    });
});
// api cư dân
Route::prefix('v1')->middleware('auth:public_user_v2')->group(function () {
    Route::prefix('posts')->name('api.v1.')->namespace('Posts\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('', 'PostsController@indexCustomer_v2')->name('index.indexCustomer_v2');
            Route::get('/{id?}', 'PostsController@detailCus')->name('detail');
            Route::get('/{id?}/comments', 'PostsController@commentsCustomer')->name('comments');
            Route::get('/comment/{id?}', 'PostsController@getComment')->name('comment');
            Route::get('/sent-notify', 'PostsController@notifyCustomer')->name('sent_notify');
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('/{id?}/comment', 'PostsController@replyCustomer')->name('reply');
            Route::post('/register', 'PostRegisterController@add')->name('addRegister');
            Route::post('/check-in', 'PostRegisterController@checkIn')->name('checkIn');
        });
        Route::group(['permission' => 'update'], function () {
            Route::put('/customer-confirm', 'PostsController@customerConfirm')->name('customer_confirm');
        });
        Route::group(['permission' => 'delete'], function () {
        });
        Route::group(['permission' => 'import'], function () {
        });
        Route::group(['permission' => 'export'], function () {
        });

    });

    Route::prefix('post-emotion')->name('api.v1.')->namespace('PostEmotion\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/get-emotion', 'PostEmotionController@getPostEmotion')->name('get');
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('/', 'PostEmotionController@store')->name('store');
        });
        Route::group(['permission' => 'update'], function () {
            Route::post('{id}/update', 'PostEmotionController@update')->name('update');
        });
        Route::group(['permission' => 'delete'], function () {
            Route::post('{id}/delete', 'PostEmotionController@delete')->name('delete');
        });
        Route::group(['permission' => 'import'], function () {
        });
        Route::group(['permission' => 'export'], function () {
        });
    });

    Route::prefix('feedback')->name('api.v1.')->namespace('Feedback\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'FeedbackController@indexCus')->name('index');
            Route::get('/{id?}/comments', 'FeedbackController@commentsCustomer')->name('comments');
            Route::get('/comment/{id?}', 'FeedbackController@getCommentCustomer')->name('comment');
            Route::get('/templates', 'FeedbackFormController@index')->name('templates');
            Route::get('/repair-apartment-list', 'FeedbackController@repairApartmentList')->name('repairApartmentList');
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('/create', 'FeedbackController@create_v2')->name('create_v2');
            Route::post('/{id?}/comment', 'FeedbackController@replyCustomer_v2')->name('replyCustomer_v2');
            Route::post('/create-form-repair-apartment', 'FeedbackController@createFormRepairApartment_v2')->name('createFormRepairApartment_v2');
        });
        Route::group(['permission' => 'update'], function () {
            Route::post('/{id?}/update-status', 'FeedbackController@updateStatus')->name('update.status');
        });
        Route::group(['permission' => 'delete'], function () {
        });
        Route::group(['permission' => 'import'], function () {
        });
        Route::group(['permission' => 'export'], function () {
        });


    });

    Route::prefix('bill')->name('api.v1')->namespace('Bill\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'BillController@index')->name('index');
            Route::get('{id}/show', 'BillController@show')->name('show');
            Route::get('{id}/payments', 'BillController@payments')->name('payments');
            Route::get('payments-test', 'BillController@listAccountBanksTest')->name('payments_test');
            Route::get('list-bank', 'BillController@listbank')->name('listbank');
            Route::get('{id}/receipts', 'BillController@receipts')->name('receipts');

        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('{id}/pay', 'BillController@pay')->name('pay');
        });


    });

    Route::prefix('building')->name('api.v1')->namespace('Bill\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/payments', 'BillController@listAccountBanks')->name('index');
        });
    });

    Route::prefix('service-partners')->name('api.v1')->namespace('ServicePartners\Api')->group(function () {
        Route::group(['permission' => 'insert'], function () {
            Route::post('', 'ServicePartnerController@create')->name('create');
        });
    });

    Route::prefix('handbooks')->name('api.v1')->namespace('BuildingHandbook\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'BuildingHandbookController@index')->name('index');
            Route::get('/categories', 'BuildingHandbookController@category')->name('category');
            Route::get('/phone-categories', 'BuildingHandbookController@getPhoneInCategory')->name('phonecategory');
            Route::get('{id}/detail', 'BuildingHandbookController@detail')->name('detail');
            Route::get('/typehandbook', 'BuildingHandbookController@getHandbookType')->name('typeHandbook');
        });
    });
    Route::prefix('departments')->name('api.v1')->namespace('Department\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'DepartmentAppController@index')->name('index');
        });
    });
    Route::prefix('apartment')->name('api.v1')->namespace('Apartments\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'ApartmentsController@detailCus')->name('detailcus');
        });
        Route::group(['permission' => 'update'], function () {
            Route::post('/status', 'ApartmentsController@status')->name('status');
        });
    });


    Route::prefix('notification')->name('api.v1.')->namespace('Fcm\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'FcmController@indexCus')->name('index');
            Route::get('/count', 'FcmController@seeAllNotiCus')->name('count');
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('/read', 'FcmController@readNotifyCus')->name('read');
            Route::post('/savetoken', 'FcmController@saveTokenCus')->name('savetoken');
        });
    });
    Route::prefix('social-posts')->name('social_posts.')->namespace('Network\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('', 'SocialPostController@index')->name('all');
            Route::get('{post_id}/comments', 'SocialCommentController@index')->name('comments.index');
            Route::get('/{id}', 'SocialPostController@show')->name('show');
            Route::get('{post_id}/comments/{id}', 'SocialCommentController@show')->name('comments.show');
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('', 'SocialPostController@save_v2')->name('save');
            Route::post('{post_id}/reaction', 'SocialPostReactionController@add')->name('reaction.add');
            Route::post('{post_id}/comments', 'SocialCommentController@save_admin')->name('comments.save');
        });
        Route::group(['permission' => 'update'], function () {
            Route::put('/{id}', 'SocialPostController@save_v2')->name('update_v2');
            Route::put('{post_id}/comments/{id}', 'SocialCommentController@save')->name('comments.edit');
        });
        Route::group(['permission' => 'delete'], function () {
            Route::delete('{post_id}/reaction', 'SocialPostReactionController@delete')->name('reaction.remove');
            Route::delete('/{id?}', 'SocialPostController@destroy')->name('delete');
            Route::delete('{post_id}/comments/{id}', 'SocialCommentController@delete')->name('comments.delete');

        });
        Route::group(['permission' => 'import'], function () {
        });
        Route::group(['permission' => 'export'], function () {
        });
    });

    Route::group(['permission' => 'insert'], function () {
        Route::post('change-password', 'Users\Api\UserController@resetPassword_v2')->name('change.resetPassword_v2.resident');
        Route::post('reset-password', 'Users\Api\ForgotPasswordController@forgotPassword_v2')->name('reset.forgotPassword_v2.resident');
        Route::post('logout', 'Users\Api\UserController@logoutApp')->name('logout');
    });
});


// end api cư dân
Route::prefix('admin/v1/posts')->middleware('auth:public_user')->name('api.v1.')->namespace('Posts\Api')->group(function () {
    Route::group(['permission' => 'view'], function () {
        Route::get('/', 'PostsController@index')->name('index');
        Route::get('/{id?}', 'PostsController@detail')->name('detail');
        Route::get('/{id?}/comments', 'PostsController@comments')->name('comments');
        Route::get('/comment/{id?}', 'PostsController@getComment')->name('comment');
    });
    Route::group(['permission' => 'insert'], function () {
        Route::post('/{id?}/comment', 'PostsController@reply')->name('reply');
    });
    Route::group(['permission' => 'delete'], function () {
        Route::post('delete/{id?}', 'PostsController@destroy')->name('delete');
        Route::post('delete/{post_id}/comments/{id}', 'PostsController@delete')->name('comments.delete');
    });
});

Route::prefix('admin/v1/feedback')->middleware('auth:public_user')->name('api.v1.')->namespace('Feedback\Api')->group(function () {
    Route::group(['permission' => 'view'], function () {
        Route::get('/', 'FeedbackController@index')->name('index');
        Route::get('/detail/{id}', 'FeedbackController@detail')->name('detail');
        Route::get('/{id?}/comments', 'FeedbackController@comments')->name('comments');
        Route::get('/comment/{id?}', 'FeedbackController@getComment')->name('comment');
    });
    Route::group(['permission' => 'insert'], function () {
        Route::post('/create', 'FeedbackController@create')->name('create');
        Route::post('/{id?}/comment ', 'FeedbackController@reply')->name('reply');
    });
    Route::group(['permission' => 'update'], function () {
        Route::post('/{id?}/update-status', 'FeedbackController@updateStatus')->name('update.status');
    });
    Route::group(['permission' => 'delete'], function () {
        Route::post('delete/{id?}', 'FeedbackController@destroy')->name('delete');
        Route::post('delete/{feedback_id}/comments/{id}', 'FeedbackController@delete_comment')->name('comments.delete');
    });
});

Route::prefix('admin/v1/notification')->name('api.v1.')->namespace('Fcm\Api')->group(function () {
    Route::group(['permission' => 'view'], function () {
        Route::get('/', 'FcmController@index')->name('index');
        Route::get('/count', 'FcmController@seeAllNoti')->name('count');
        Route::get('/check', 'FcmController@check')->name('check');
        Route::get('/check1', 'FcmController@check1')->name('check');
        Route::get('/config', 'FcmController@getConfigUsers')->name('config');
    });
    Route::group(['permission' => 'insert'], function () {
        Route::post('/read', 'FcmController@readNotify')->name('read');
        Route::post('/savetoken', 'FcmController@saveToken')->name('savetoken');
        Route::post('/logToken', 'FcmController@logToken')->name('logToken');
        Route::post('/config', 'FcmController@configUsers')->name('config');
    });

});

// api dashboard BQL
Route::prefix('admin/v1/dashboard')->middleware('auth:public_user')->name('api.v1.')->namespace('Home\Api')->group(function () {
    Route::group(['permission' => 'view'], function () {
        Route::get('/', 'HomeController@index')->name('index');
        Route::get('/building', 'HomeController@listBuilding')->name('list_building');
    });
});
// api dashboard BQL
Route::prefix('admin/v1')->middleware('auth:public_user')->name('api.v1.')->group(function () {
    Route::prefix('receipt')->name('receipt.')->namespace('Receipt\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'ReceiptAppController@index')->name('index');
            Route::get('/{id?}', 'ReceiptAppController@detail')->name('detail');
            Route::get('/config', 'ReceiptAppController@listConfig')->name('listConfig');
            Route::get('/listUserReceipt', 'ReceiptAppController@listUserReceipt')->name('listUserReceipt');
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('/create', 'ReceiptAppController@create')->name('create');
            Route::post('/viewer', 'ReceiptAppController@reviewReceipt')->name('viewer');
        });
    });
    Route::prefix('apartments')->name('apartments.')->namespace('Apartments\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'ApartmentsController@listApartment')->name('list_apartment');
            Route::get('/user', 'ApartmentsController@listApartmentUser')->name('list_apartment_user');
            Route::get('/filter', 'ApartmentsController@listApartmentv2')->name('listApartmentv2');
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('/upload', 'ApartmentsController@upload')->name('upload');
            Route::post('/uploaddata', 'ApartmentsController@uploadData')->name('uploadData');
        });
    });
    // upload file
    Route::prefix('upload')->name('upload.')->namespace('Assets\Api')->group(function () {
        Route::post('/attach-files', 'UploadController@upload')->name('uploadFile');
    });
});


Route::prefix('debug')->name('debug.')->group(function () {
    Route::get('/set-app-id-online', 'Debug\DebugController@setAppIdOnline')->name('setAppIdOnline');
    Route::get('/set-app-id-form-domain', 'Debug\DebugController@setAppIdForDomain')->name('setAppIdForDomain');
});

Route::prefix('admin/v1/auth')->name('auth.api.')->group(function () {
    Route::group(['permission' => 'view'], function () {
        Route::post('/register', 'Users\Api\RegisterController@create')->name('create');
        Route::post('/login', 'Users\Api\UserController@login')->name('login');
        Route::post('/info', 'Users\Api\UserController@getAuthUser')->name('getAuthUser');
        Route::post('/profile', 'Users\Api\UserController@createProfile')->name('createProfile');
        Route::post('/password-forgot', 'Users\Api\ForgotPasswordController@forgotPassword')->name('forgot');
        Route::post('/password-reset', 'Users\Api\UserController@resetPassword')->name('reset');
        Route::post('/change-profile', 'Users\Api\UserController@changeProfile')->name('changeProfile');
        Route::post('/logout', 'Users\Api\UserController@logoutApp')->name('logout');
    });
    Route::namespace('Auth\Admin')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::post('/send-otp', 'ResetPasswordController@sendOTPApi');
            Route::post('/check-otp', 'ResetPasswordController@CheckOTP');
            Route::post('/otp-login', 'ResetPasswordController@LoginWithOTPApi');
            Route::post('/newpass', 'ResetPasswordController@newpass');
        });
    });
});

Route::namespace('CRMTool')->group(function () {
    Route::get('list-module', 'Api\ListmoduleController@list_module')->name('list-module');
    Route::post('update-time', 'Api\ListmoduleController@update_time')->name('update-time');
});

Route::prefix('admin/v1')->name('admin.api.v1.')->group(function () {
    Route::prefix('diarywork')->name('diarywork.')->namespace('WorkDiary\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'WorkDiaryController@index')->name('index');
            Route::get('/{id}', 'WorkDiaryController@detail')->name('detail');
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('/store', 'WorkDiaryController@store')->name('store');
        });
        Route::group(['permission' => 'update'], function () {
            Route::post('/{id}/update-status', 'WorkDiaryController@updateStatus')->name('updateStatus');
            Route::post('/{id}/update', 'WorkDiaryController@update')->name('update');
        });
        Route::group(['permission' => 'delete'], function () {
            Route::post('/{id}/delete', 'WorkDiaryController@delete')->name('delete');
        });
    });
    Route::prefix('department')->name('department.')->namespace('Department\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'DepartmentAppController@index')->name('index');
            Route::get('/filter-building', 'DepartmentController@getListByBuilding')->name('getListByBuilding');
            Route::get('/{id}', 'DepartmentController@detail')->name('detail');
        });
    });
    Route::prefix('upload')->name('upload.')->namespace('Upload\Api')->group(function () {
        Route::post('/', 'UploadController@upload')->name('upload');
        Route::post('/file', 'UploadController@upload_v2')->name('upload_v2');
    });

    Route::prefix('building')->name('upload.')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'Home\Api\HomeController@listBuilding')->name('listBuilding');
            Route::get('/filter-user', 'Home\Api\HomeController@getUserBuilding')->name('getUserBuilding');
            Route::get('/user-department', 'Home\Api\HomeController@getListUserByDepartment')->name('getListUserByDepartment');
            Route::get('/show-user/{id}', 'Home\Api\HomeController@showUser')->name('showUser');
            Route::get('/info', 'Home\Api\HomeController@buildingInfo')->name('buildingInfo');
        });
    });

    Route::prefix('handbooks')->name('api.v1')->namespace('BuildingHandbook\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'BuildingHandbookController@index')->name('index');
            Route::get('/categories', 'BuildingHandbookController@category')->name('category');
            Route::get('/phone-categories', 'BuildingHandbookController@getPhoneInCategory')->name('phonecategory');
            Route::get('{id}/detail', 'BuildingHandbookController@detail')->name('detail');
            Route::get('/typehandbook', 'BuildingHandbookController@getHandbookType')->name('typeHandbook');
        });
    });

    Route::group(['permission' => 'view'], function () {
        Route::get('/regency', 'Users\Api\UserController@getRegency')->name('getRegency');
    });

    // lấy chỉ sô điện nước

    Route::prefix('apartment-serivce-price')->name('apartmentserviceprice.')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('', 'Service\Api\ServiceApartmentPriceController@filterByApartment')->name('index');
        });
    });
    Route::prefix('electric-meter')->name('electricMeter.')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('', 'BdcElectricMeter\Api\ElectricMeterController@index')->name('index');
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('', 'BdcElectricMeter\Api\ElectricMeterController@createOrUpdate')->name('createOrUpdate');
        });
    });

    Route::prefix('social-posts')->name('social_posts.')->namespace('Network\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('', 'SocialPostController@index')->name('all');
            Route::get('{post_id}/comments', 'SocialCommentController@index')->name('comments.index');
            Route::get('/{id}', 'SocialPostController@show_admin')->name('show');
            Route::get('{post_id}/comments/{id}', 'SocialCommentController@show')->name('comments.show');
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('', 'SocialPostController@save')->name('save');
            Route::post('{post_id}/reaction', 'SocialPostReactionController@add_admin')->name('reaction.add_admin');
            Route::post('{post_id}/comments', 'SocialCommentController@save_admin')->name('comments.save_admin');
        });
        Route::group(['permission' => 'update'], function () {
            Route::put('/{id}', 'SocialPostController@save')->name('update_v2');
            Route::put('{post_id}/comments/{id}', 'SocialCommentController@save_admin')->name('comments.edit');
        });
        Route::group(['permission' => 'delete'], function () {
            Route::post('delete/{post_id}/reaction', 'SocialPostReactionController@delete_admin')->name('reaction.remove');
            Route::post('delete/{id?}', 'SocialPostController@destroy')->name('delete');
            Route::post('delete/{post_id}/comments/{id}', 'SocialCommentController@delete')->name('comments.delete');

        });
        Route::group(['permission' => 'import'], function () {
        });
        Route::group(['permission' => 'export'], function () {
        });
    });

});

Route::prefix('admin/v2')->name('admin.api.v2.')->group(function () {
    Route::prefix('apartments')->name('apartments.')->namespace('Apartments\Api')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('/', 'ApartmentsController@listApartmentv2')->name('listApartmentv2');
        });
    });
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::post('/login', 'Users\Api\UserController@login')->name('login');
        });
    });
    Route::prefix('apartment-serivce-price')->name('apartmentserviceprice.')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('', 'Service\Api\ServiceApartmentPriceController@filterByApartment')->name('index');
        });
    });
    Route::prefix('electric-meter')->name('electricMeter.')->group(function () {
        Route::group(['permission' => 'view'], function () {
            Route::get('', 'BdcElectricMeter\Api\ElectricMeterController@index')->name('index');
        });
        Route::group(['permission' => 'insert'], function () {
            Route::post('', 'BdcElectricMeter\Api\ElectricMeterController@createOrUpdate')->name('createOrUpdate');
        });
    });
});

Route::prefix('receipts')->name('api.receipts.')->group(function () {
    Route::group(['permission' => 'view'], function () {
        Route::get('/', 'Receipt\Api\ReceiptController@index')->name('index');
        Route::get('filterByBill/{apartment_id}/{service_id}/{type}', 'Receipt\Api\ReceiptController@filterByBill')->name('filterByBill');
        Route::get('filterByBill-old/{apartment_id}/{service_id}/{type}', 'Receipt\Api\ReceiptController@filterByBill_old')->name('filterByBill_old');
        Route::get('filterByBillPhieuChi/{apartment_id}/{service_id}', 'Receipt\Api\ReceiptController@filterByBillPhieuChi')->name('filterByBillPhieuChi');
    });
    Route::group(['permission' => 'insert'], function () {
        Route::post('create', 'Receipt\Api\ReceiptController@create')->name('create');
        Route::post('/viewer', 'Receipt\Api\ReceiptController@reviewReceipt')->name('viewer');
        Route::post('/create-old', 'Receipt\Api\ReceiptController@save_old')->name('create_old');
        Route::post('/viewer-old', 'Receipt\Api\ReceiptController@reviewReceipt_old')->name('viewer_old');
    });
    Route::group(['permission' => 'update'], function () {
        Route::post('update/{id}', 'Receipt\Api\ReceiptController@update')->name('update');
    });
});

Route::prefix('debit')->name('api.debit.')->group(function () {
    Route::group(['permission' => 'view'], function () {
        Route::get('/', 'Debit\Api\DebitController@index')->name('index');
        Route::get('reloadProcessDebitDetail', 'Debit\Api\DebitController@reloadProcessDebitDetail')->name('reloadProcessDebitDetail');
        Route::get('loadFormReceiptPrevious/{apartment_id}', 'Debit\Api\DebitController@loadFormReceiptPrevious')->name('loadFormReceiptPrevious');
        Route::get('v2/loadFormReceiptPrevious/{apartment_id}', 'Debit\V2\Api\DebitController@loadFormReceiptPrevious')->name('v2.loadFormReceiptPrevious');
    });
    Route::group(['permission' => 'insert'], function () {
        Route::post('createDebitPrevious', 'Debit\Api\DebitController@createDebitPrevious')->name('createDebitPrevious');
        Route::post('v2/createDebitPrevious', 'Debit\V2\Api\DebitController@createDebitPrevious')->name('v2.createDebitPrevious');
    });
});

Route::prefix('provisional-receipt')->name('api.provisionalreceipt.')->group(function () {
    Route::group(['permission' => 'view'], function () {
        Route::get('/', 'BdcProvisionalReceipt\Api\ProvisionalReceiptController@index')->name('index');
        Route::get('filterApartment/{apartment_id}', 'BdcProvisionalReceipt\Api\ProvisionalReceiptController@filterApartmentId')->name('filterApartment');
    });
    Route::group(['permission' => 'insert'], function () {
        Route::post('create', 'BdcProvisionalReceipt\Api\ProvisionalReceiptController@create')->name('create');
    });
});

Route::prefix('admin/v1/company')->name('api.v1.')->namespace('Company\Api')->group(function () {
    Route::group(['permission' => 'insert'], function () {
        Route::post('/', 'CompanyController@create')->name('store');
    });
});
Route::prefix('admin/v1/internal-notices')->name('api.v1.')->namespace('BuildingHandbook\Api')->group(function () {
    Route::group(['permission' => 'view'], function () {
        Route::get('/', 'BuildingHandbookController@internal')->name('internal');
        Route::get('/{id?}', 'BuildingHandbookController@detail')->name('detail');
    });
});

Route::prefix('admin/v1/notify')->name('api.v1.')->namespace('Fcm\Api')->group(function () {
    Route::group(['permission' => 'insert'], function () {
        Route::post('/push', 'Fcm_v2Controller@pushNotify')->name('push');
    });
});



Route::prefix('apartment-serivce-price')->name('api.apartmentserviceprice.')->group(function () {
    Route::group(['permission' => 'view'], function () {
        Route::get('/', 'Service\Api\ServiceApartmentPriceController@index')->name('index')->middleware('jwt.auth');
        Route::get('filterById/{id}', 'Service\Api\ServiceApartmentPriceController@filterById')->name('filterById');
    });
});
Route::prefix('v1')->name('v1.')->group(function () {
    Route::prefix('virtual-acc-payment')->name('api.virtualaccpayment.')->namespace('VirtualAccPayment\Api')->group(function () {
        Route::group(['permission' => 'insert'], function () {
            Route::post('/store', 'VirtualAccPaymentController@store')->name('store');
            Route::post('/receipt', 'VirtualAccPaymentController@storeReceipt')->name('storeReceipt');
        });
        Route::group(['permission' => 'view'], function () {
            Route::get('/transaction-receipt', 'VirtualAccPaymentController@getTransactionReceipt')->name('getTransactionReceipt');
        });
    });
    Route::prefix('debit')->name('debit.')->namespace('Debit\V2\Api')->group(function () {
        Route::post('/allocation', 'DebitController@save_phanbo')->name('save_phanbo');
    });
});
Route::prefix('nicepay')->name('nicepay.')->namespace('Payment\Api')->group(function () {
    Route::group(['permission' => 'insert'], function () {
        Route::post('/virtual-account/transaction', 'NicePayController@saveTransactionVirtualAccount')->name('saveTransactionVirtualAccount');
        Route::post('/transaction', 'NicePayController@saveTransaction')->name('saveTransaction');
    });
});
Route::prefix('check-upload')->group(function () {
    Route::post('/file', 'Debug\DebugController@checkEmailExist')->name('check_upload');
});

Route::prefix('detected-object')->group(function () {
    Route::get('/detected', 'Dev\DevController@Detected_vehicles')->name('Detected_vehicles');
    Route::post('/detected', 'Dev\DevController@Detected_vehicles')->name('Detected_vehicles');
});

Route::prefix('dev')->group(function () {
    //Route::get('/dev/Tranfer_RegisterMonthlyTicket', 'Dev\DevController@Tranfer_RegisterMonthlyTicket');
    Route::post('/Tranfer_RegisterMonthlyTicket', 'Dev\DevController@Tranfer_RegisterMonthlyTicket')->name('Tranfer_RegisterMonthlyTicket');
});
