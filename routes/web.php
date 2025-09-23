<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\RawMaterialController;
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

    Route::put('/raw-materials/{raw_material}/status', [RawMaterialController::class, 'updateStatus']);
    Route::resource('raw-materials', RawMaterialController::class);

    Route::put('/items/{item}/status', [ItemController::class, 'updateStatus']);
    Route::resource('items', ItemController::class);

    Route::put('/suppliers/{supplier}/status', [SupplierController::class, 'updateStatus']);
    Route::resource('suppliers', SupplierController::class);

    Route::put('/customers/{customer}/status', [CustomerController::class, 'updateStatus']);
    Route::resource('customers', CustomerController::class);

    Route::resource('purchases', PurchaseController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
