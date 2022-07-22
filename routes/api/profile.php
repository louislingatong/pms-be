<?php

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['cors', 'auth:api']], function () {
    Route::get('/', function (Request $request) {
        return response()->json(new UserResource($request->user()));
    });
});
