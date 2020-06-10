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

});



Route::group(['middleware' => [],'namespace' => 'Wechat', 'prefix' => 'wechat', 'as' => 'wechat.'], function () {



});