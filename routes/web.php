<?php

use App\Events\UserUpdated;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\RoleController;
use App\Http\Controllers\admin\SalesRecordController;
use App\Http\Controllers\admin\StockController;
use App\Http\Controllers\admin\StockInController;
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

    // routeDahboard
    Route::get('/', [AdminController::class, 'index'])->name('home');
    Route::get('/getPrediction', [AdminController::class, 'getPrediction'])->name('admin.dataTable.getPrediction');
    // endRoute

    // routeStock
    Route::resource('stock', StockController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/getStock', [StockController::class, 'getStock'])->name('admin.dataTable.getStock');
    Route::get('stock/print', [StockController::class, 'print'])->name('stock.print');
    //endRoute

    // routeCategory
    Route::resource('category', CategoryController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/getCategories', [CategoryController::class, 'getCategories'])->name('admin.dataTable.getCategories');
    // endCategory

    // routeSalesRecord
    Route::get('sales-record/print', [SalesRecordController::class, 'print'])->name('sales-record.print');
    Route::resource('sales-record', SalesRecordController::class)->only(['index', 'create', 'store', 'edit', 'update', 'store', 'destroy']);
    Route::get('/getSalesRecord', [SalesRecordController::class, 'getSalesRecord'])->name('admin.dataTable.getSalesRecord');
    // endRoute

    // routeUser
    Route::resource('user', UserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/getUsers', [UserController::class, 'getUsers'])->name('admin.dataTable.getUsers');
    // endRoute

    // routeStock-in
    Route::get('stock-in/print', [StockInController::class, 'print'])->name('stock-in.print');
    Route::resource('stock-in', StockInController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/getStockIn', [StockInController::class, 'getStockIn'])->name('admin.dataTable.getStockIn');
    // endRoute

    // routeRole
    Route::resource('role', RoleController::class)->only(['index']);
    // endRoute

});
