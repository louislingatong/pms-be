<?php

use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/', function () {
        return response()->json(['data' => PermissionResource::collection(Permission::all())]);
    });
});
