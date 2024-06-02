<?php

use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\CartController;
use App\Http\Controllers\admin\CashierController;
use App\Http\Controllers\admin\DiscountController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\RoleController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\auth\LogoutController;
use Illuminate\Support\Facades\Route;

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

Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/login', [LoginController::class, 'authenticate'])->name('login')->middleware('guest');
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout')->middleware('auth');

Route::group([
    'middleware' => ['auth'],
    'namespace'  => 'App\Http\Controllers\admin',
    'prefix'     => '/',
], function () {
    Route::get('/', [AdminController::class, 'index'])->name('home');

    // routeUser
    Route::resource('user', UserController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy'])->names([
        'index' => 'user',
        'update'  => 'order.confirm',
        'show'  => 'order.view',
        'edit' => 'user.edit',
        'store' => 'order.storepayment',
        'destroy' => 'user.destroy'
    ]);

    Route::get('/getUser', [UserController::class, 'getUser'])->name('admin.dataTable.getUser');
    //endRoute

    //routeRoles
    Route::resource('role', RoleController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy'])->names([
        'index' => 'role',
    ]);
    //endRoute

    //routeProduct
    Route::resource('product', ProductController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy', 'create'])->names([
        'index' => 'product',
        'create' => 'product.create',
        'store' => 'product.store',
        'edit' => 'product.edit',
        'show' => 'product.show',
    ]);

    Route::get('/edit', [ProductController::class, 'edit']);
    Route::get('/getProduct', [ProductController::class, 'getProduct'])->name('admin.dataTable.getProduct');
    //endRoute

    //routeCashier
    Route::resource('cashier', CashierController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy', 'create'])->names([
        'index' => 'cashier',
        'store' => 'cashier.store'
    ]);

    Route::get('/get-detail/{id}', [CashierController::class, 'getDetail']);
    //endRoute

    //routeDiscount
    Route::resource('discount', DiscountController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy', 'create'])->names([
        'index' => 'discount',
        'destroy' => 'discount.destroy',
        'edit' => 'discount.edit',
        'create' => 'discount.create',
        'store' => 'discount.store'
    ]);

    Route::get('/getDiscount', [DiscountController::class, 'getDiscount'])->name('admin.dataTable.getDiscount');
    //endRoute

    //routeCart
    Route::resource('cart', CartController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy', 'create'])->names([
        'destroy' => 'cart.destroy',
    ]);
    //endRoute
});