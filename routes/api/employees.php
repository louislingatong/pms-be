<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['cors', 'auth:api', 'permission:employee_access']], function () {
    Route::get('/', 'EmployeeController@index')
        ->middleware('permission:employee_show');
    Route::post('/', 'EmployeeController@create')
        ->middleware('permission:employee_create');

    Route::patch('/activate', 'EmployeeController@activate')
        ->middleware('permission:employee_delete');
    Route::patch('/deactivate', 'EmployeeController@deactivate')
        ->middleware('permission:employee_delete');

    Route::get('{employee}', 'EmployeeController@read')
        ->middleware('permission:employee_show');
    Route::put('{employee}', 'EmployeeController@update')
        ->middleware('permission:employee_edit');
    Route::put('{employee}/edit-permissions', 'EmployeeController@updatePermissions')
        ->middleware('permission:employee_edit');
    Route::put('{employee}/assign-vessels', 'EmployeeController@updateVesselAssignment')
        ->middleware('permission:employee_edit');

});
