<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'permission:machinery_access']], function () {
    Route::get('/', 'MachineryController@index')
        ->middleware('permission:machinery_show');
    Route::post('/', 'MachineryController@create')
        ->middleware('permission:machinery_create');
    Route::get('{machinery}', 'MachineryController@read')
        ->middleware('permission:machinery_show');
    Route::put('{machinery}', 'MachineryController@update')
        ->middleware('permission:machinery_edit');
    Route::delete('{machinery}', 'MachineryController@delete')
        ->middleware('permission:machinery_delete');

    Route::post('/import', 'MachineryController@import')
        ->middleware('permission:machinery_import');
    Route::put('{machinery}/add-sub-category', 'MachineryController@addSubCategory');
});
