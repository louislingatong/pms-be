<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'permission:vessel_machinery_access']], function () {
//    Route::get('/', 'VesselMachinerySubCategoryController@index');
//    Route::post('/', 'VesselMachinerySubCategoryController@create');
//    Route::get('{vesselMachinerySubCategory}', 'VesselMachinerySubCategoryController@read');
//    Route::put('{vesselMachinerySubCategory}', 'VesselMachinerySubCategoryController@update');
//    Route::delete('{vesselMachinerySubCategory}', 'VesselMachinerySubCategoryController@delete');
    Route::post('/import', 'VesselMachinerySubCategoryController@import')
        ->middleware('permission:vessel_sub_category_import');
});
