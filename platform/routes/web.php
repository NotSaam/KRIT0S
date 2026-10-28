<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EngagementController;
use App\Http\Controllers\FindingController;
use App\Http\Controllers\FindingTemplateController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('engagements', EngagementController::class);
    Route::post('engagements/{engagement}/publish', [EngagementController::class, 'publish'])->name('engagements.publish');

    Route::scopeBindings()->group(function () {
        Route::resource('engagements.findings', FindingController::class)->except(['index', 'show']);

        Route::post('engagements/{engagement}/reports', [ReportController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('engagements.reports.store');
        Route::get('engagements/{engagement}/reports/{report}', [ReportController::class, 'download'])
            ->name('engagements.reports.download');
    });

    Route::resource('templates', FindingTemplateController::class)
        ->except('show')
        ->parameters(['templates' => 'template']);

    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('clients', ClientController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
