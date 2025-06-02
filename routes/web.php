<?php

use App\Events\UserUpdated;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\CartController;
use App\Http\Controllers\admin\CashierController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\DiscountController;
use App\Http\Controllers\admin\IngredientTransactionController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\PartnerProductController;
use App\Http\Controllers\admin\PaymentController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\RecipeController;
use App\Http\Controllers\admin\StockController;
use App\Http\Controllers\admin\ReportController;
use App\Http\Controllers\admin\RoleController;
use App\Http\Controllers\admin\TableController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\auth\LogoutController;
use App\Services\OrderService;
use Illuminate\Support\Facades\Cache;
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
    Route::get('/filterRating', [AdminController::class, 'filterRating'])->name('filterRating');
    Route::get('/getPrediction', [AdminController::class, 'getPrediction'])->name('admin.dataTable.getPrediction');
    Route::get('/printPrediction', [AdminController::class, 'printPrediction'])->name('admin.printPrediction');
    //endRoute

    // routeUser
    Route::resource('user', UserController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy', 'create'])->names([
        'index' => 'user',
        'update'  => 'user.update',
        'edit' => 'user.edit',
        'store' => 'user.store',
        'destroy' => 'user.destroy',
        'create' => 'user.create'
    ]);

    Route::get('/getUser', [UserController::class, 'getUser'])->name('admin.dataTable.getUser');
    //endRoute

    //routeRoles
    Route::resource('role', RoleController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy'])->names([
        'index' => 'role',
    ]);
    //endRoute

    //routeStock
    Route::resource('stock', StockController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy', 'create'])->names([
        'index' => 'stock'
    ]);

    Route::get('/edit', [StockController::class, 'edit']);
    Route::get('/getStock', [StockController::class, 'getStock'])->name('admin.dataTable.getStock');
    //endRoute

    //RouteIngredientTransaction
    Route::resource('ingredient', IngredientTransactionController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy', 'create'])->names([
        'index' => 'ingredient'
    ]);

    Route::get('/getIngredientTransaction', [IngredientTransactionController::class, 'getIngredientTransaction'])->name('admin.dataTable.getIngredientTransaction');
    Route::post('/printIngredientTransaction', [IngredientTransactionController::class, 'printIngredientTransaction'])->name('admin.printIngredientTransaction');
    //endRoute

    //RouteProduct
    Route::resource('product', ProductController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy', 'create'])->names([
        'index' => 'product'
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

    //routeCart
    Route::resource('cart', CartController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy', 'create'])->names([
        'destroy' => 'cart.destroy',
    ]);
    //endRoute

    //routeCategory
    Route::resource('category', CategoryController::class)->names([
        'index' => 'category',
        'edit' => 'category.edit',
        'update' => 'category.update',
        'destroy' => 'category.destroy'
    ]);

    Route::get('/getCategories', [CategoryController::class, 'getCategories'])->name('admin.dataTable.getCategories');
    //endRoute

    //routeOrder
    Route::resource('order', OrderController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy', 'create'])->names([
        'index' => 'order.index',
        'update' => 'order.update',
        'show' => 'order.show'
    ]);
    //endRoute

    // routePayment
    Route::resource('payment', PaymentController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy', 'create'])->names([
        'index' => 'payment',
        'show' => 'payment.show',
    ]);

    Route::get('/getAllOrder', [PaymentController::class, 'getAllOrder'])->name('admin.dataTable.getAllOrder');
    Route::get('/getPayment/{id}', [PaymentController::class, 'getPayment'])->name('admin.dataTable.getPayment');
    Route::post('/payment/billOrUpdate', [PaymentController::class, 'billOrUpdate'])->name('payment.billOrUpdate');
    Route::patch('payment/update/{id}', [PaymentController::class, 'updateStatus'])->name('payment.updateStatus');
    //endRoute

    // routeReport
    Route::resource('report', ReportController::class)->only(['index', 'update', 'show', 'edit', 'store', 'destroy', 'create'])->names([
        'index' => 'report',
        'show' => 'report.show'
    ]);

    Route::get('/getAllReport', [ReportController::class, 'getAllReport'])->name('admin.dataTable.getAllReport');
    Route::get('/reportFilter', [ReportController::class, 'filterReport'])->name('report.filter');
    Route::get('/getReport/{id}', [ReportController::class, 'getReport'])->name('admin.dataTable.getReport');
    Route::post('/report/print', [ReportController::class, 'printReport'])->name('report.printReport');
    //endRoute

    Route::get('/users/updates', function () {
        // Simpan timestamp terakhir kali event dipanggil
        if (!Cache::has('user_updated_at')) {
            return response()->json(['timestamp' => null]);
        }
        return response()->json(['timestamp' => Cache::get('user_updated_at')]);
    });
});