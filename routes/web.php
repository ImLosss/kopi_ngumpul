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
});
