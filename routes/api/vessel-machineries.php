<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'permission:vessel_machinery_access']], function () {
    Route::get('/', 'VesselMachineryController@index')
        ->middleware('permission:vessel_machinery_show');
    Route::post('/', 'VesselMachineryController@create')
        ->middleware('permission:vessel_machinery_create');
    Route::delete('/', 'VesselMachineryController@delete')
        ->middleware('permission:vessel_machinery_delete');

    Route::post('/import', 'VesselMachineryController@import')
        ->middleware('permission:vessel_machinery_import');
    Route::get('/export', 'VesselMachineryController@export')
        ->middleware('permission:vessel_machinery_export');
    Route::post('/copy-all-machinery', 'VesselMachineryController@copyAllMachinery')
        ->middleware('permission:vessel_machinery_create');

    Route::get('{vesselMachinery}', 'VesselMachineryController@read')
        ->middleware('permission:vessel_machinery_show');
    Route::put('{vesselMachinery}', 'VesselMachineryController@update')
        ->middleware('permission:vessel_machinery_edit');
    Route::put('{vesselMachinery}/edit-machinery-sub-categories', 'VesselMachineryController@editMachinerySubCategories')
        ->middleware('permission:vessel_sub_category_edit');
    Route::get('{vesselMachinery}/export', 'VesselMachineryController@exportVesselMachinery')
        ->middleware('permission:vessel_machinery_export');
});
