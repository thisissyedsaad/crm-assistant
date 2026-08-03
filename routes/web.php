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
use App\Http\Controllers\Admin\IpWhitelistController;
use App\Http\Controllers\Admin\ApiRequestLogController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\Admin\SalesTargetDashboardController;
use App\Http\Controllers\Staff\StaffSalesDashboardController;

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
        Route::post('users/{user}/reset-2fa', [UserController::class, 'reset2fa'])->name('users.reset2fa')->middleware('role:super-admin');

        // Sales Target Dashboard Routes (Admin Only)
        Route::prefix('sales-dashboard')->name('sales-dashboard.')->group(function () {
            Route::get('/', [SalesTargetDashboardController::class, 'index'])->name('index');
            Route::get('/chart-data', [SalesTargetDashboardController::class, 'getChartData'])->name('chart-data');
            Route::post('/refresh', [SalesTargetDashboardController::class, 'refresh'])->name('refresh');
        });

        Route::get('/targets', [TargetController::class, 'index'])->name('targets.index');
        Route::post('/targets/update', [TargetController::class, 'updateTarget'])->name('targets.update');
        Route::post('/targets/save-all', [TargetController::class, 'saveAll'])->name('targets.save-all');
        Route::post('/targets/copy-previous', [TargetController::class, 'copyFromPreviousMonth'])->name('targets.copy-previous');
        Route::post('/targets/toggle-hide', [TargetController::class, 'toggleHideFromDashboard'])->name('targets.toggle-hide');
        Route::post('/targets/calendar/get', [TargetController::class, 'getWorkingDaysCalendar'])->name('targets.calendar.get');
        Route::post('/targets/calendar/save', [TargetController::class, 'saveWorkingDaysCalendar'])->name('targets.calendar.save');

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

        Route::get('api-logs', [ApiRequestLogController::class, 'index'])->name('api-logs.index')->middleware('role:super-admin');
    });
    
});

// Staff Sales Dashboard Routes (accessible by all authenticated users for their own data)
// Admin/Super-admin can view any user's dashboard by passing user_id parameter
Route::prefix('admin')->name('admin.')->middleware(['auth', 'ensure2fa', 'ip.whitelist'])->group(function () {
    Route::get('/staff-sales-dashboard/{user_id?}', [StaffSalesDashboardController::class, 'index'])->name('staff-sales-dashboard.index');
    Route::get('/staff-sales-dashboard/chart-data/{user_id?}', [StaffSalesDashboardController::class, 'getChartData'])->name('staff-sales-dashboard.chart-data');
});

    require __DIR__.'/auth.php';
