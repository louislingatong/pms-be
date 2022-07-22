<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['cors', 'auth:api', 'permission:jobs_access']], function () {
    Route::get('/', 'WorkController@index')
        ->middleware('permission:jobs_show');
    Route::post('/', 'WorkController@create')
        ->middleware('permission:jobs_create');

    Route::post('/import', 'WorkController@import')
        ->middleware('permission:jobs_import');
    Route::get('/export', 'WorkController@export')
        ->middleware('permission:jobs_export');
    Route::get('/file-download', 'WorkController@downloadFile')
        ->middleware('permission:jobs_download_file');
    Route::get('/count', 'WorkController@workCount');

    Route::get('{vesselMachinerySubCategory}/export', 'WorkController@exportWorkHistory')
        ->middleware('permission:jobs_history_export');
});
