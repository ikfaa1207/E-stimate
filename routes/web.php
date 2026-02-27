<?php

use App\Http\Controllers\AssemblyController;
use App\Http\Controllers\AssemblyMappingController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectRequirementController;
use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('projects.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('projects.index');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('projects', ProjectController::class);
    Route::get('projects/{project}/requirements', [ProjectRequirementController::class, 'edit'])
        ->name('projects.requirements.edit');
    Route::put('projects/{project}/requirements', [ProjectRequirementController::class, 'update'])
        ->name('projects.requirements.update');
    Route::post('projects/{project}/estimates', [EstimateController::class, 'store'])
        ->name('projects.estimates.store');
    Route::get('projects/{project}/estimates/{estimate}', [EstimateController::class, 'show'])
        ->name('projects.estimates.show');
    Route::get('projects/{project}/estimates/{estimate}/export', [EstimateController::class, 'export'])
        ->name('projects.estimates.export');

    Route::resource('spaces', SpaceController::class)->except('show');
    Route::resource('items', ItemController::class)->except('show');
    Route::resource('assemblies', AssemblyController::class)->except('show');

    Route::get('assembly-mappings', [AssemblyMappingController::class, 'edit'])
        ->name('assembly-mappings.edit');
    Route::put('assembly-mappings', [AssemblyMappingController::class, 'update'])
        ->name('assembly-mappings.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
