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

    Route::delete('/', 'MachineryController@delete')
        ->middleware('permission:machinery_delete');

    Route::post('/import', 'MachineryController@import')
        ->middleware('permission:machinery_import');

    Route::put('{machinery}/create-sub-category', 'MachineryController@createSubCategory')
        ->middleware('permission:sub_category_create');

    Route::put('{machinery}/delete-sub-category', 'MachineryController@deleteSubCategory')
        ->middleware('permission:sub_category_delete');
});
