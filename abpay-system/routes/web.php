<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Route::middleware(['auth', 'admin'])->group(function () {
//     Route::get('/admin/users', [UserController::class, 'index'])->name('users.index');
//     Route::get('/admin/users/create', [UserController::class, 'create'])->name('users.create');
//     Route::post('/admin/users', [UserController::class, 'store'])->name('users.store');
//     Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
//     Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('users.update');
//     Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
// });

Route::get('/admin/users', [UserController::class, 'index'])->name('users.index');
Route::get('/admin/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/admin/users', [UserController::class, 'store'])->name('users.store');
Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');


Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/orders/updateStatus', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
Route::get('/orders/get-order-details/{orderId}', [OrderController::class,'getOrderDetails'])->name('orders.getOrderDetails');
Route::get('/orders/cros/orderLists', [OrderController::class, 'orderLists'])->name('orders.orderLists');