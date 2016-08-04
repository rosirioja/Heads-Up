<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['prefix' => 'api'], function(){
    Route::group(['prefix' => 'v1', 'namespace' => 'Api\v1'], function(){

        // Rewrite the cron - for testing and debugging
        Route::get('writecron', 'AlertController@writeCron');
        // Get the latest date to be run in cron - for testing and debugging
        Route::get('latest', 'AlertController@latestDate');
        Route::resource('alerts', 'AlertController');

        // User Consent Flow
        Route::any('users/auth', 'UserController@auth');
        // Mobile - User validation
        Route::post('users/validate', 'UserController@postValidate');
        Route::resource('users', 'UserController');
    });
});

Route::get('/', function () {
    return view('welcome');
});
