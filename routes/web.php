<?php

use App\Http\Controllers\AssemblyController;
use App\Http\Controllers\AssemblyMappingController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\EstimateAdjustmentController;
use App\Http\Controllers\FinishLevelController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectRequirementController;
use App\Http\Controllers\ProjectWorkflowController;
use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('projects.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('projects.index');
})->middleware('auth')->name('dashboard');

Route::get('share/{token}/auth', [\App\Http\Controllers\ProjectShareController::class, 'showAuth'])
    ->name('projects.share.auth');
Route::post('share/{token}/auth', [\App\Http\Controllers\ProjectShareController::class, 'verifyAuth'])
    ->name('projects.share.verify');

Route::middleware('share.passcode')->group(function () {
    Route::get('share/{token}', [\App\Http\Controllers\ProjectShareController::class, 'show'])
        ->name('projects.share.show');
    Route::post('share/{token}/approve', [\App\Http\Controllers\ProjectShareController::class, 'approve'])
        ->name('projects.share.approve');
    Route::post('share/{token}/comments', [\App\Http\Controllers\ProjectCommentController::class, 'storeGuest'])
        ->name('projects.share.comments.store');
});

Route::middleware('auth')->group(function () {
    Route::resource('projects', ProjectController::class);
    Route::get('projects/{project}/requirements', [ProjectRequirementController::class, 'edit'])
        ->name('projects.requirements.edit');
    Route::put('projects/{project}/requirements', [ProjectRequirementController::class, 'update'])
        ->name('projects.requirements.update');
    Route::post('projects/{project}/estimates', [EstimateController::class, 'store'])
        ->name('projects.estimates.store');
    Route::put('projects/{project}/compliance/{compliance}', [ProjectWorkflowController::class, 'updateCompliance'])
        ->name('projects.compliance.update');
    Route::put('projects/{project}/tasks/{task}', [ProjectWorkflowController::class, 'updateTask'])
        ->name('projects.tasks.update');
    Route::post('projects/{project}/comments', [\App\Http\Controllers\ProjectCommentController::class, 'store'])
        ->name('projects.comments.store');
    Route::post('projects/{project}/comments/{comment}/resolve', [\App\Http\Controllers\ProjectCommentController::class, 'resolve'])
        ->name('projects.comments.resolve');
    Route::delete('projects/{project}/comments/{comment}', [\App\Http\Controllers\ProjectCommentController::class, 'destroy'])
        ->name('projects.comments.destroy');
    Route::get('projects/{project}/estimates/{estimate}', [EstimateController::class, 'show'])
        ->name('projects.estimates.show');
    Route::get('projects/{project}/estimates/{estimate}/export', [EstimateController::class, 'export'])
        ->name('projects.estimates.export');
    Route::post('projects/{project}/estimates/{estimate}/approve', [EstimateController::class, 'approve'])
        ->name('projects.estimates.approve');

    Route::post('projects/{project}/estimates/{estimate}/adjustments', [EstimateAdjustmentController::class, 'store'])
        ->name('projects.estimates.adjustments.store');
    Route::put('projects/{project}/estimates/{estimate}/adjustments/{adjustment}', [EstimateAdjustmentController::class, 'update'])
        ->name('projects.estimates.adjustments.update');
    Route::delete('projects/{project}/estimates/{estimate}/adjustments/{adjustment}', [EstimateAdjustmentController::class, 'destroy'])
        ->name('projects.estimates.adjustments.destroy');

    // Read-only routes for Estimator / Admin, forbidden for Client
    Route::middleware('role:admin,estimator')->group(function () {
        Route::get('spaces', [SpaceController::class, 'index'])->name('spaces.index');
        Route::get('items', [ItemController::class, 'index'])->name('items.index');
        Route::get('assemblies', [AssemblyController::class, 'index'])->name('assemblies.index');
        Route::get('assembly-mappings', [AssemblyMappingController::class, 'edit'])->name('assembly-mappings.edit');
        Route::get('finish-levels', [FinishLevelController::class, 'index'])->name('finish-levels.index');
        Route::post('projects/{project}/estimates/{estimate}/lock', [EstimateController::class, 'lock'])->name('projects.estimates.lock');
        Route::post('projects/{project}/estimates/{estimate}/unlock', [EstimateController::class, 'unlock'])->name('projects.estimates.unlock');
        
        // Admin-only write routes
        Route::middleware('role:admin')->group(function () {
            Route::get('spaces/create', [SpaceController::class, 'create'])->name('spaces.create');
            Route::post('spaces', [SpaceController::class, 'store'])->name('spaces.store');
            Route::get('spaces/{space}/edit', [SpaceController::class, 'edit'])->name('spaces.edit');
            Route::put('spaces/{space}', [SpaceController::class, 'update'])->name('spaces.update');
            Route::delete('spaces/{space}', [SpaceController::class, 'destroy'])->name('spaces.destroy');

            Route::get('items/create', [ItemController::class, 'create'])->name('items.create');
            Route::post('items', [ItemController::class, 'store'])->name('items.store');
            Route::post('items/bulk-update', [ItemController::class, 'bulkUpdate'])->name('items.bulk-update');
            Route::get('items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
            Route::put('items/{item}', [ItemController::class, 'update'])->name('items.update');
            Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');

            Route::get('assemblies/create', [AssemblyController::class, 'create'])->name('assemblies.create');
            Route::post('assemblies', [AssemblyController::class, 'store'])->name('assemblies.store');
            Route::get('assemblies/{assembly}/edit', [AssemblyController::class, 'edit'])->name('assemblies.edit');
            Route::put('assemblies/{assembly}', [AssemblyController::class, 'update'])->name('assemblies.update');
            Route::delete('assemblies/{assembly}', [AssemblyController::class, 'destroy'])->name('assemblies.destroy');

            Route::put('assembly-mappings', [AssemblyMappingController::class, 'update'])->name('assembly-mappings.update');

            Route::get('finish-levels/create', [FinishLevelController::class, 'create'])->name('finish-levels.create');
            Route::post('finish-levels', [FinishLevelController::class, 'store'])->name('finish-levels.store');
            Route::get('finish-levels/{finish_level}', [FinishLevelController::class, 'show'])->name('finish-levels.show');
            Route::get('finish-levels/{finish_level}/edit', [FinishLevelController::class, 'edit'])->name('finish-levels.edit');
            Route::put('finish-levels/{finish_level}', [FinishLevelController::class, 'update'])->name('finish-levels.update');
            Route::delete('finish-levels/{finish_level}', [FinishLevelController::class, 'destroy'])->name('finish-levels.destroy');
        });
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
