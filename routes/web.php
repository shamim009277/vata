<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;




Route::get('/', function () {
    return Inertia::render('auth/Login', [
        'canResetPassword' => Route::has('password.request'),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard1');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::put('/units/{unit}/status', [UnitController::class, 'updateStatus']);
    Route::resource('units', UnitController::class);

    Route::put('/items/{item}/status', [ItemController::class, 'updateStatus']);
    Route::resource('items', ItemController::class);

    Route::put('/raw-materials/{raw_material}/status', [RawMaterialController::class, 'updateStatus']);
    Route::resource('raw-materials', RawMaterialController::class);

    Route::put('/suppliers/{supplier}/status', [SupplierController::class, 'updateStatus']);
    Route::resource('suppliers', SupplierController::class);

    Route::put('/customers/{customer}/status', [CustomerController::class, 'updateStatus']);
    Route::resource('customers', CustomerController::class);

    Route::resource('purchases', PurchaseController::class);
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
