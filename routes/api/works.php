<?php

use App\Http\Requests\CountWorksRequest;
use App\Models\VesselMachinerySubCategory;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'permission:jobs_access']], function () {
    Route::get('/', 'WorkController@index')
        ->middleware('permission:jobs_show');
    Route::post('/', 'WorkController@create')
        ->middleware('permission:jobs_create');
    Route::get('/count', function (CountWorksRequest $request) {
        $vessel = $request->getVessel();
        $work = [];
        $work['warning'] = VesselMachinerySubCategory::searchByStatus(config('work.statuses.warning'))
            ->whereHas('vesselMachinery.vessel', function ($q) use ($vessel) {
                $q->where('name', '=', $vessel);
            })
            ->count();
        $work['due'] = VesselMachinerySubCategory::searchByStatus(config('work.statuses.due'))
            ->whereHas('vesselMachinery.vessel', function ($q) use ($vessel) {
                $q->where('name', '=', $vessel);
            })
            ->count();
        $work['overdue'] = VesselMachinerySubCategory::searchByStatus(config('work.statuses.overdue'))
            ->whereHas('vesselMachinery.vessel', function ($q) use ($vessel) {
                $q->where('name', '=', $vessel);
            })
            ->count();
        $work['jobs_done'] = VesselMachinerySubCategory::searchByStatus(config('work.statuses.jobs_done'))
            ->whereHas('vesselMachinery.vessel', function ($q) use ($vessel) {
                $q->where('name', '=', $vessel);
            })
            ->count();
        $work['dry_dock'] = VesselMachinerySubCategory::searchByStatus(config('work.statuses.dry_dock'))
            ->whereHas('vesselMachinery.vessel', function ($q) use ($vessel) {
                $q->where('name', '=', $vessel);
            })
            ->count();
        return response()->json(['data' => $work]);
    });
    Route::post('/import', 'WorkController@import')
        ->middleware('permission:jobs_import');
    Route::get('/export', 'WorkController@export')
        ->middleware('permission:jobs_export');
    Route::get('/file-download', 'WorkController@downloadFile')
        ->middleware('permission:jobs_download_file');
    Route::get('{vesselMachinerySubCategory}/export', 'WorkController@exportWorkHistory')
        ->middleware('permission:jobs_history_export');
});
