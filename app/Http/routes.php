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

        Route::get('writecron', 'AlertController@writeCron');
        Route::get('latest', 'AlertController@latestDate');
        Route::resource('alerts', 'AlertController');

    });
});

Route::get('/', function () {
    return view('welcome');
});
