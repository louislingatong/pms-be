<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'permission:employee_access']], function () {
    Route::get('/', 'EmployeeController@index')
        ->middleware('permission:employee_show');
    Route::post('/', 'EmployeeController@create')
        ->middleware('permission:employee_create');
    Route::get('{employee}', 'EmployeeController@read')
        ->middleware('permission:employee_show');
    Route::put('{employee}', 'EmployeeController@update')
        ->middleware('permission:employee_edit');
    Route::delete('{employee}', 'EmployeeController@delete')
        ->middleware('permission:employee_delete');
    Route::put('{employee}/edit-permissions', 'EmployeeController@updatePermissions')
        ->middleware('permission:employee_edit');
});
