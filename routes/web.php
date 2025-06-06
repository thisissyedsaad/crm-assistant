<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;

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


Route::get('/', function () {
    return view('auth.login'); // this will load the login page
});

// Route::get('/admin/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
  
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::resource('orders', OrderController::class);
    Route::post('/admin/get-customer', [OrderController::class, 'getCustomer'])->name('getCustomer');
    Route::get('/admin/orders/autocomplete', [OrderController::class, 'autocomplete'])->name('orders.autocomplete');
    
    Route::resource('customers', CustomerController::class);
    Route::get('/admin/customers/last-order', [CustomerController::class, 'getLastOrder'])->name('customers.lastorder');
    Route::get('/admin/customers/search/autocomplete', [CustomerController::class, 'autocomplete'])->name('customers.autocomplete');
    Route::get('/admin/customers/ordercount', [CustomerController::class, 'getOrderCount'])->name('customers.ordercount');

});
require __DIR__.'/auth.php';