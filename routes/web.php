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

Route::get('/', 'WelcomeController@index')->name('welcome');

Auth::routes();

//Route::redirect('/register', '/404', 301);

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/problem', function () {
    return view('help');
})->name('help');

Route::get('/password', 'HomeController@password')->name('password');
Route::post('/retrieve', 'HomeController@retrieve')->name('retrieve');

Route::group(['prefix' => 'patron'], function () {
    Route::patch('is_something', 'PatronController@is_something')->name('is_something');
    Route::post('follow', 'PatronController@follow')->name('follow');
});
Route::resource('patron', 'PatronController');