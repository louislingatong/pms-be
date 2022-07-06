<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'permission:running_hours_access']], function () {
    Route::get('/', 'RunningHourController@index')
        ->middleware('permission:running_hours_show');
    Route::post('/', 'RunningHourController@create')
        ->middleware('permission:running_hours_create');
    Route::post('/import', 'RunningHourController@import')
        ->middleware('permission:running_hours_import');
    Route::get('/export', 'RunningHourController@export')
        ->middleware('permission:running_hours_export');
    Route::get('{vesselMachinery}/export', 'RunningHourController@exportRunningHoursHistory')
        ->middleware('permission:running_hours_history_export');
});
