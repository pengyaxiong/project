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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api','as'=>'api.'], function () {

    //会员性别统计
    Route::get('sex_count', 'VisualizationController@sex_count');
    //签单情况统计
    Route::get('chartjs', 'VisualizationController@chartjs');
    Route::get('task_rate', 'VisualizationController@task_rate');
    //方案签约率统计
    Route::get('case_count', 'VisualizationController@case_count');
    //任务周期比列
    Route::get('task_days', 'VisualizationController@task_days');
    //任务量统计
    Route::get('task_count', 'VisualizationController@task_count');
    //项目情况统计
    Route::get('project_count', 'VisualizationController@project_count');
    //员工项目情况分析图
    Route::get('staff_project', 'VisualizationController@staff_project');
    //项目状态统计
    Route::get('project_status', 'VisualizationController@project_status');
    //删除客户资讯
    Route::post('delete_patron', 'VisualizationController@delete_patron');

    Route::post('follow_edit', 'VisualizationController@follow_edit');

    //上传图片
    Route::post('upload_image', 'VisualizationController@upload_image');
    Route::post('delete_image', 'VisualizationController@delete_image');

    //消息通知
    Route::get('notifications', 'VisualizationController@notifications');


    //select联动
    Route::get('customer_patron','VisualizationController@customer_patron');
});


Route::group(['middleware' => [], 'namespace' => 'Wechat', 'prefix' => 'wechat', 'as' => 'wechat.'], function () {


});