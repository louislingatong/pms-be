<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['cors', 'auth:api']], function () {
    Route::post('/import', 'MachinerySubCategoryController@import')
        ->middleware('permission:sub_category_import');
});
