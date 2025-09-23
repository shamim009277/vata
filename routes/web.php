<?php

use App\Http\Controllers\BusinessSettingController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;




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

    Route::put('/units/{unit}/status', [UnitController::class, 'updateStatus']);
    Route::resource('units', UnitController::class);
});

Route::get('/set-locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'bn'])) {
        Session::put('locale', $locale);
        App::setLocale($locale);
    }
    return back();
})->name('set-locale');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
