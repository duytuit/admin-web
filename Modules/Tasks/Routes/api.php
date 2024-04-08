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

//Route::middleware('auth:api')->get('/tasks', function (Request $request) {
////    return $request->user();
////});
Route::group(['middleware' => ['jwt.auth']], function () {
    Route::group(['prefix' => 'admin/v1'], function () {
        Route::group(['prefix'=>'task-category'], function () {
            Route::get('/', "TaskCategoriesController@index")->name('task-category.index');
            Route::post('add', "TaskCategoriesController@add")->name('task-category.add');
            Route::get('show', "TaskCategoriesController@show")->name('task-category.show');
            Route::put('update', "TaskCategoriesController@update")->name('task-category.update');
            Route::delete('delete', "TaskCategoriesController@delete")->name('task-category.delete');
        });
        Route::group(['prefix'=>'sub-task-template'], function () {
            Route::get('/', "SubTaskTemplatesController@index")->name('sub-task-template.index');
            Route::post('stt-infos/add', "SubTaskTemplatesController@addSubTaskTempInfo")->name('sub-task-template.stt-infos-add');
            Route::put('stt-infos/update', "SubTaskTemplatesController@updateSubTaskTempInfo")->name('sub-task-template.stt-infos-update');
            Route::post('add', "SubTaskTemplatesController@add")->name('sub-task-template.add');
            Route::get('show', "SubTaskTemplatesController@show")->name('sub-task-template.show');
            Route::put('update', "SubTaskTemplatesController@update")->name('sub-task-template.update');
            Route::delete('delete', "SubTaskTemplatesController@delete")->name('sub-task-template.delete');
        });
        Route::group(['prefix'=>'sub-task-template-info'], function () {
            Route::get('/', "SubTaskTemplateInfosController@index")->name('sub-task-template-info.index');
        });
        Route::group(['prefix'=>'sub-task'], function () {
            Route::post('/add', "SubTasksController@add")->name('sub-task.add');
            Route::put('/update-status', "SubTasksController@updateStatus")->name('sub-task.updateStatus');
            Route::put('feedback', "SubTasksController@feedback")->name('task.feedback');
            Route::delete('/delete', "SubTasksController@delete")->name('sub-task.delete');
            Route::get('show', "SubTasksController@show")->name('sub-task.show');
        });
        Route::group(['prefix'=>'task-user'], function () {
            Route::post('/add', "TaskUsersController@add")->name('task-user.add');
            Route::delete('/delete', "TaskUsersController@delete")->name('task-user.delete');
        });
        Route::group(['prefix'=>'task-comment'], function () {
            Route::post('add', "TaskCommentsController@add")->name('task-comment.add');
            Route::delete('delete', "TaskCommentsController@delete")->name('task-comment.delete');
        });
        Route::group(['prefix'=>'task'], function () {
            Route::get('/', "TasksController@index")->name('task.index');
            Route::post('add', "TasksController@add")->name('task.add');
            Route::get('show', "TasksController@show")->name('task.show');
            Route::put('update', "TasksController@update")->name('task.update');
            Route::put('update-status', "TasksController@updateStatus")->name('task.updateStatus');
            Route::put('feedback', "TasksController@feedback")->name('task.feedback');
            Route::delete('delete', "TasksController@delete")->name('task.delete');
        });
        Route::group(['prefix'=>'work-shift'], function () {
            Route::get('/', "WorkShiftsController@index")->name('work-shift.index');
            Route::get('tasks', "WorkShiftsController@tasks")->name('work-shift.tasks');
            Route::post('add', "WorkShiftsController@add")->name('work-shift.add');
            Route::get('show', "WorkShiftsController@show")->name('work-shift.show');
            Route::put('update', "WorkShiftsController@update")->name('work-shift.update');
            Route::delete('delete', "WorkShiftsController@delete")->name('work-shift.delete');
            Route::put('update-status', "WorkShiftsController@updateStatus")->name('work-shift.updateStatus');
        });
        Route::group(['prefix'=>'related'], function () {
            Route::get('/', "RelatedController@index")->name('related.index');
        });

        Route::group(['prefix'=>'department'], function () {
            Route::get('/', "DepartmentsController@index")->name('department.index');
            Route::get('/list-admin', "DepartmentsController@listAdmin")->name('department.listAdmin');
        });
    });
});
Route::prefix('v2')->name('api.v2.')->group(function () {
    Route::group(['middleware' => ['jwt.auth']], function () {
        Route::group(['prefix'=>'department'], function () {
            Route::get('/', "DepartmentsController@index")->name('department.index');
            Route::get('/list-admin', "DepartmentsController@listAdmin")->name('department.listAdmin');
        });
    });
});