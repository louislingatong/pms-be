<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/', 'RunningHourController@index');
    Route::post('/', 'RunningHourController@create');
    Route::post('/import', 'RunningHourController@import');
    Route::get('/export', 'RunningHourController@export');
    Route::get('{vesselMachinery}/export', 'RunningHourController@exportRunningHoursHistory');
});
