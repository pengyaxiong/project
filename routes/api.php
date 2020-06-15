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

Route::group(['namespace' => 'Api'], function () {

    //会员性别统计
    Route::get('sex_count', 'VisualizationController@sex_count');
    //签单情况统计
    Route::get('chartjs', 'VisualizationController@chartjs');
    //方案签约率统计
    Route::get('case_count', 'VisualizationController@case_count');
    //任务周期比列
    Route::get('task_days', 'VisualizationController@task_days');
    //任务量统计
    Route::get('task_count', 'VisualizationController@task_count');
    //项目情况统计
    Route::get('project_count', 'VisualizationController@project_count');

});



Route::group(['middleware' => [],'namespace' => 'Wechat', 'prefix' => 'wechat', 'as' => 'wechat.'], function () {



});