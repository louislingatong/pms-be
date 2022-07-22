<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['cors', 'auth:api']], function () {
    Route::get('/', 'UserController@index');
    Route::post('/', 'UserController@create');
    Route::get('{user}', 'UserController@read');
    Route::put('{user}', 'UserController@update');
    Route::delete('{user}', 'UserController@delete');
});
