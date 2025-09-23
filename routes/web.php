<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Schedular\CurrentJobsController;
use App\Http\Controllers\Schedular\CompletedJobsController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\Admin\IpWhitelistController; // Add this import
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\TwoFactorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// IP Whitelist Management Routes (Only for Super Admin)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super-admin', 'ensure2fa', 'ip.whitelist'])->group(function () {
    Route::prefix('ip-whitelist')->name('ip-whitelist.')->group(function () {
        Route::get('/', [IpWhitelistController::class, 'index'])->name('index');
        Route::get('/create', [IpWhitelistController::class, 'create'])->name('create');
        Route::post('/', [IpWhitelistController::class, 'store'])->name('store');
        Route::get('/{ipWhitelist}/edit', [IpWhitelistController::class, 'edit'])->name('edit');
        Route::put('/{ipWhitelist}', [IpWhitelistController::class, 'update'])->name('update');
        Route::delete('/{ipWhitelist}', [IpWhitelistController::class, 'destroy'])->name('destroy');
        
        // AJAX Routes
        Route::post('/{ipWhitelist}/toggle-status', [IpWhitelistController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/bulk-status', [IpWhitelistController::class, 'bulkStatusUpdate'])->name('bulk-status');
        Route::get('/current-ip', [IpWhitelistController::class, 'getCurrentIp'])->name('current-ip');
    });
});

Route::get('/optimize-clear', function () {
    // Optional: require a secret key to avoid public access
    if (request('key') !== 'csdCacheClear') {
        abort(403, 'Unauthorized');
    }
    Artisan::call('optimize:clear');
    return 'Application cache cleared successfully!';
});


    Route::get('/', function () {
        return view('auth.login'); // this will load the login page
    });
Route::middleware(['ip.whitelist'])->group(function () {

    // 2FA Routes (authenticated users ke liye)
    Route::middleware('auth')->group(function () {
        Route::get('/admin/2fa/setup', [TwoFactorController::class, 'show'])->name('2fa.show');
        Route::post('/admin/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
        Route::post('/admin/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
        
        Route::get('/admin/2fa/verify', [TwoFactorController::class, 'showVerifyForm'])->name('2fa.verify');
        Route::post('/admin/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.post');
    });

    // Route::get('/admin/dashboard', function () {
    //     return view('dashboard');
    // })->middleware(['auth', 'verified'])->name('dashboard');

    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'ensure2fa'])
        ->name('dashboard');

    Route::middleware('auth', 'ensure2fa')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    });

    Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin|super-admin', 'ensure2fa'])->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::prefix('admin')->name('admin.')->middleware(['auth', 'ensure2fa'])->group(function () {
        Route::resource('orders', OrderController::class);
        Route::post('/admin/get-customer', [OrderController::class, 'getCustomer'])->name('getCustomer');
        Route::get('/admin/orders/autocomplete', [OrderController::class, 'autocomplete'])->name('orders.autocomplete');
        
        Route::resource('customers', CustomerController::class);
        Route::get('/admin/customers/last-order', [CustomerController::class, 'getLastOrder'])->name('customers.lastorder');
        Route::get('/admin/customers/search/autocomplete', [CustomerController::class, 'autocomplete'])->name('customers.autocomplete');
        Route::get('/admin/customers/ordercount', [CustomerController::class, 'getOrderCount'])->name('customers.ordercount');

        Route::prefix('schedular')->name('schedular.')->group(function () {
            Route::get('current-jobs/get-notifications', [App\Http\Controllers\Schedular\CurrentJobsController::class, 'getNotifications'])->name('current-jobs.get-notifications');
            
            Route::resource('current-jobs', CurrentJobsController::class);
            Route::post('current-jobs/update-status', [CurrentJobsController::class, 'updateOrderStatus'])->name('current-jobs.update-status');
            Route::post('current-jobs/get-customer', [CurrentJobsController::class, 'getCustomer'])->name('current.getCustomer');

            Route::resource('completed-jobs', CompletedJobsController::class);
            Route::post('completed-jobs/get-customer', [CompletedJobsController::class, 'getCustomer'])->name('completed.getCustomer');

        // Insert this near your other CurrentJobsController routes
        Route::post('/current-jobs/remove-orders', [CurrentJobsController::class, 'removeOrders'])->name('current-jobs.remove-orders');
        });

        Route::resource('trainings', TrainingController::class);
    });
    
});

    require __DIR__.'/auth.php';
