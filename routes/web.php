<?php

use App\Http\Controllers\AssetsController;
use App\Http\Controllers\BusinessSettingController;
use App\Http\Controllers\BusinessStoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\FieldListController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PaymentHeadController;
use App\Http\Controllers\PaymentKhataController;
use App\Http\Controllers\RowProductionController;
use App\Http\Controllers\StockListController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\UnloadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('auth/Login', [
        'canResetPassword' => Route::has('password.request'),
    ]);
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware(['auth', 'verified']);
Route::get('session/get', [DashboardController::class, 'session_get'])->name('session.get')->middleware(['auth', 'verified']);
Route::post('dashboard/session_change', [DashboardController::class, 'sessionChange'])->name('dashboard.session_change')->middleware(['auth', 'verified']);


Route::middleware(['auth'])->group(function () {
    Route::put('/plans/{plan}/status', [SubscriptionPlanController::class, 'updateStatus']);
    Route::resource('subscription-plans', SubscriptionPlanController::class);
    Route::resource('business-store', BusinessStoreController::class);

    Route::put('/items/{item}/status', [ItemController::class, 'updateStatus']);
    Route::resource('items', ItemController::class);

    Route::put('/stock-lists/{stock_list}/status', [StockListController::class, 'updateStatus']);
    Route::resource('stock-lists', StockListController::class);

    Route::put('/field-lists/{field_list}/status', [FieldListController::class, 'updateStatus']);
    Route::resource('field-lists', FieldListController::class);

    Route::get('/payment_head/groups', [PaymentHeadController::class, 'groups']);
    Route::put('/payment_head/{payment_head}/status', [PaymentHeadController::class, 'updateStatus']);
    Route::resource('payment_head', PaymentHeadController::class);

    Route::put('/modules/{module}/status', [ModuleController::class, 'updateStatus']);
    Route::resource('modules', ModuleController::class);

    Route::post('/invoice/delivary', [InvoiceController::class, 'invoiceDelivary'])->name('invoice.delivary');
    Route::get('/invoice/all', [InvoiceController::class, 'allinvoice'])->name('invoice.all');
    Route::get('/invoice/advance', [InvoiceController::class, 'advanceinvoice'])->name('invoice.advance');
    Route::resource('invoices', InvoiceController::class);


    Route::get('/row-productions/all', [RowProductionController::class, 'allrowProduction'])->name('row-productions.all');
    Route::post('/row-productions/lock', [RowProductionController::class, 'lockProduction'])->name('row-productions.lock');
    Route::resource('row-productions', RowProductionController::class);


    Route::get('/delivery/invoice', [DeliveryController::class, 'invoice']);
    Route::resource('deliveries', DeliveryController::class);

    Route::resource('payment-khata', PaymentKhataController::class);

    Route::post('/loads/round/store', [LoadController::class, 'roundStore'])->name('load.round.store');
    Route::post('/loads/stock', [LoadController::class, 'checkStock'])->name('load.stock');
    Route::resource('loads', LoadController::class);

    Route::resource('unloads', UnloadController::class);
    Route::resource('assets', AssetsController::class);
    Route::post('asset/issue/store',[AssetsController::class,'issueStore'])->name('assets.issue.store');
    Route::get('assets/products/search', [AssetsController::class, 'productsSearch'])->name('assets.products.search');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
