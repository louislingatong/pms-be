<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'permission:vessel_access']], function () {
    Route::get('/', 'VesselController@index')
        ->middleware('permission:vessel_show');
    Route::post('/', 'VesselController@create')
        ->middleware('permission:vessel_create');
    Route::delete('/', 'VesselController@delete')
        ->middleware('permission:vessel_delete');

    Route::get('{vessel}', 'VesselController@read')
        ->middleware('permission:vessel_show');
    Route::put('{vessel}', 'VesselController@update')
        ->middleware('permission:vessel_edit');
});
