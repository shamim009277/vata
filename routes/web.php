<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\BusinessSettingController;
use App\Http\Controllers\SubscriptionPlanController;

Route::get('/', function () {
    return Inertia::render('auth/Login', [
        'canResetPassword' => Route::has('password.request'),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard1');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::put('/plans/{plan}/status', [SubscriptionPlanController::class, 'updateStatus']);
    Route::resource('subscription-plans', SubscriptionPlanController::class);
    Route::resource('business-settings', BusinessSettingController::class);

    Route::put('/modules/{module}/status', [ModuleController::class, 'updateStatus']);
    Route::resource('modules', ModuleController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
