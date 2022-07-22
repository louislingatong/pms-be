<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['cors', 'auth:api', 'permission:interval_access']], function () {
    Route::get('/', 'IntervalController@index')
        ->middleware('permission:interval_show');
    Route::post('/', 'IntervalController@create')
        ->middleware('permission:interval_create');
    Route::delete('/', 'IntervalController@delete')
        ->middleware('permission:interval_delete');

    Route::post('/import', 'IntervalController@import')
        ->middleware('permission:interval_import');

    Route::get('{interval}', 'IntervalController@read')
        ->middleware('permission:interval_show');
    Route::put('{interval}', 'IntervalController@update')
        ->middleware('permission:interval_edit');
});
