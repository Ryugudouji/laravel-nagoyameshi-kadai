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
use App\Http\Controllers\ReviewController;


require __DIR__.'/auth.php';

    // 管理者としてログインしていない状態でのみアクセス可能にするルーティング
    Route::group(['middleware' => 'guest:admin'], function() {
        // 管理者未ログイン時にアクセス可能なトップページ
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // ゲスト（管理者未ログイン）のみがアクセス可能な店舗一覧ページ
        Route::resource('restaurants', UserRestaurantController::class)->only(['index', 'show']);

        // ユーザーのルーティング
        Route::group(['middleware' => ['auth', 'verified']], function() {
            Route::resource('user', UserUserController::class)->except(['create', 'store', 'destroy']);

            // サブスクリプションのルーティング
            Route::middleware(['auth', 'verified'])->group(function(){
                // 未登録ユーザー用
                Route::middleware(['unsubscribed'])->group(function () {
                    Route::get('subscription/create', [App\Http\Controllers\SubscriptionController::class, 'create'])
                        ->name('subscription.create');
                    Route::post('subscription', [App\Http\Controllers\SubscriptionController::class, 'store'])
                        ->name('subscription.store');
                });

                // 登録済みユーザー用
                Route::middleware(['subscribed'])->group(function () {
                    Route::get('subscription/edit', [App\Http\Controllers\SubscriptionController::class, 'edit'])
                        ->name('subscription.edit');
                    Route::put('subscription', [App\Http\Controllers\SubscriptionController::class, 'update'])
                        ->name('subscription.update');
                    Route::get('subscription/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancel'])
                        ->name('subscription.cancel');
                    Route::delete('subscription', [App\Http\Controllers\SubscriptionController::class, 'destroy'])
                        ->name('subscription.destroy');


                        // レビュー関連ルートの定義
                        // 「index」アクションは、管理者でなく、メール認証済みの一般ユーザーがアクセス可能
                        Route::resource('restaurants.reviews', ReviewController::class)
                        ->only(['index']);

                        // その他のアクションは、管理者でなく、メール認証済みかつ有料プランのユーザーがアクセス可能
                        Route::resource('restaurants.reviews', ReviewController::class)
                            ->middleware('subscribed')
                            ->except('index');
                });
            });
        });
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
