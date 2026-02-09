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
use App\Http\Controllers\DeliveryChallanController;
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
        Route::resource('business-store', BusinessStoreController::class)->middleware('permission:business-store.index');

        Route::put('/items/{item}/status', [ItemController::class, 'updateStatus']);
    Route::resource('items', ItemController::class);

    Route::put('/stock-lists/{stock_list}/status', [StockListController::class, 'updateStatus']);
    Route::resource('stock-lists', StockListController::class)->middleware('permission:stock-lists.index');

    Route::put('/field-lists/{field_list}/status', [FieldListController::class, 'updateStatus']);
    Route::resource('field-lists', FieldListController::class)->middleware('permission:field-lists.index');

    Route::get('/payment_head/groups', [PaymentHeadController::class, 'groups']);
    Route::put('/payment_head/{payment_head}/status', [PaymentHeadController::class, 'updateStatus']);
    Route::resource('payment_head', PaymentHeadController::class)->middleware('permission:payment_head.index');

    Route::put('/modules/{module}/status', [ModuleController::class, 'updateStatus']);
    Route::resource('modules', ModuleController::class)->middleware('permission:modules.index');

    Route::post('/invoice/delivary', [InvoiceController::class, 'invoiceDelivary'])->name('invoice.delivary')->middleware('permission:invoices.index');
    Route::get('/invoice/all', [InvoiceController::class, 'allinvoice'])->name('invoice.all')->middleware('permission:invoice.all');
    Route::get('/invoice/advance', [InvoiceController::class, 'advanceinvoice'])->name('invoice.advance')->middleware('permission:invoice.advance');
    Route::resource('invoices', InvoiceController::class)->middleware('permission:invoices.index');


    Route::get('/row-productions/all', [RowProductionController::class, 'allrowProduction'])->name('row-productions.all')->middleware('permission:row-productions.all');
    Route::post('/row-productions/lock', [RowProductionController::class, 'lockProduction'])->name('row-productions.lock');
    Route::resource('row-productions', RowProductionController::class)->middleware('permission:row-productions.index');


    Route::get('/delivery/invoice', [DeliveryController::class, 'invoice']);
    Route::get('/delivery/all', [DeliveryController::class, 'alldelivery'])->name('delivery.all')->middleware('permission:delivery.all');
    Route::get('/today/delivery', [DeliveryController::class, 'index'])->name('delivery.today')->middleware('permission:deliveries.index');
    Route::resource('deliveries', DeliveryController::class)->middleware('permission:deliveries.index');

    Route::resource('payment-khata', PaymentKhataController::class)->middleware('permission:payment-khata.index');

    Route::post('/loads/round/store', [LoadController::class, 'roundStore'])->name('load.round.store');
    Route::post('/loads/stock', [LoadController::class, 'checkStock'])->name('load.stock');
    Route::resource('loads', LoadController::class)->middleware('permission:loads.index');

    Route::resource('unloads', UnloadController::class)->middleware('permission:unloads.index');
    Route::resource('assets', AssetsController::class)->middleware('permission:assets.index');
    Route::post('asset/issue/store',[AssetsController::class,'issueStore'])->name('assets.issue.store');
    Route::put('asset/issue/{issue}',[AssetsController::class,'issueUpdate'])->name('assets.issue.update');
    Route::delete('asset/issue/{issue}',[AssetsController::class,'issueDestroy'])->name('assets.issue.destroy');
    Route::post('asset/issue/{issue}/lost',[AssetsController::class,'issueLost'])->name('assets.issue.lost');
    Route::put('asset/lost/{lost}',[AssetsController::class,'lostUpdate'])->name('assets.lost.update');
    Route::delete('asset/lost/{lost}',[AssetsController::class,'lostDestroy'])->name('assets.lost.destroy');
    Route::post('asset/issue/{issue}/damage',[AssetsController::class,'issueDamage'])->name('assets.issue.damage');
    Route::get('assets/products/search', [AssetsController::class, 'productsSearch'])->name('assets.products.search');
    Route::resource('customers', \App\Http\Controllers\CustomerController::class)->middleware('permission:customers.index');

    Route::get('delivery-challans/create', [DeliveryChallanController::class, 'create'])->name('delivery-challans.create');
    Route::post('delivery-challans', [DeliveryChallanController::class, 'store'])->name('delivery-challans.store');
    Route::get('delivery-challans/invoice-items/{invoice}', [DeliveryChallanController::class, 'getInvoiceItems'])->name('delivery-challans.invoice-items');

    // Administration Routes
    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth']], function () {
        Route::resource('menus', App\Http\Controllers\Admin\MenuController::class)->middleware('permission:admin.menus.index');
        Route::resource('roles', App\Http\Controllers\Admin\RoleController::class)->middleware('permission:admin.roles.index');
        Route::resource('permissions', App\Http\Controllers\Admin\PermissionController::class)->middleware('permission:admin.permissions.index');
        Route::resource('users', App\Http\Controllers\Admin\UserController::class)->middleware('permission:admin.users.index');
    });

    Route::resource('delivery-challans', DeliveryChallanController::class)->except(['create', 'store'])->middleware('permission:delivery-challans.index');

    // Vehicle Management Routes
    Route::get('vehicles/dashboard', [App\Http\Controllers\VehicleController::class, 'dashboard'])->name('vehicles.dashboard')->middleware('permission:vehicles.dashboard');
    Route::resource('vehicles', App\Http\Controllers\VehicleController::class)->middleware('permission:vehicles.index');
    Route::resource('vehicle-trips', App\Http\Controllers\VehicleTripController::class)->parameters([
        'vehicle-trips' => 'trip'
    ])->middleware('permission:vehicle-trips.index');
    Route::resource('vehicle-fuels', App\Http\Controllers\VehicleFuelController::class)->parameters([
        'vehicle-fuels' => 'fuel'
    ])->middleware('permission:vehicle-fuels.index');
    Route::resource('vehicle-services', App\Http\Controllers\VehicleServiceController::class)->parameters([
        'vehicle-services' => 'service'
    ])->middleware('permission:vehicle-services.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
