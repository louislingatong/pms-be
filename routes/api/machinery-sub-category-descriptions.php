<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['cors', 'auth:api']], function () {
    Route::get('/', 'MachinerySubCategoryDescriptionController@index');
});
