<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'permission:vessel_machinery_access']], function () {
    Route::post('/import', 'VesselMachinerySubCategoryController@import')
        ->middleware('permission:vessel_sub_category_import');
});
