<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\Admin\UserController as AdminUserController; // 管理者用
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController as UserUserController;  // 一般ユーザー用
use App\Http\Controllers\RestaurantController as UserRestaurantController;


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

// Route::get('/', function () {
//     return view('welcome');
// });


require __DIR__.'/auth.php';

// 管理者としてログインしていない状態でのみアクセス可能にするルーティング
Route::group(['middleware' => 'guest:admin'], function() {
    // 管理者未ログイン時にアクセス可能なトップページ
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // ゲスト（管理者未ログイン）のみがアクセス可能な店舗一覧ページ
    Route::resource('restaurants', UserRestaurantController::class)->only(['index']);
});


// 管理者専用のルーティング
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    // 管理者ホームページ
    Route::get('home', [Admin\HomeController::class, 'index'])->name('home');

    // 管理者用リソースルーティング
    Route::resource('users', AdminUserController::class);
    Route::resource('restaurants', AdminRestaurantController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('company', CompanyController::class);
    Route::resource('terms', TermController::class);

});


// ユーザーのルーティング
Route::group(['middleware' => ['auth', 'verified']], function() {
    Route::resource('user', UserUserController::class)->except(['create', 'store', 'destroy']);
});



/*
/admin/home admin.home

/admin/users
/admin/users/create
...
/admin/restaurants
/admin/restaurants/create


Route::get('home', [Admin\HomeController::class, 'index'])->middleware('auth:admin')->name('home');

Route::resource('admin/users', UserController::class);
Route::resource('admin/restaurants', RestaurantController::class);
 */
