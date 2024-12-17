<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // usersテーブルのデータの件数
        $total_users = DB::table('users')->count();

        // 有料会員数 (subscriptionsテーブルのstripe_statusが'active'のデータ数)
        $total_premium_users = DB::table('subscriptions')
            ->where('stripe_status', 'active')
            ->count();

        // 無料会員数 (全会員数から有料会員数を減算)
        $total_free_users = $total_users - $total_premium_users;

        // restaurantsテーブルのデータの件数
        $total_restaurants = DB::table('restaurants')->count();

        // reservationsテーブルのデータの件数
        $total_reservations = DB::table('reservations')->count();

        // 月間売上 (月額300を有料会員数と掛け合わせる)
        $sales_for_this_month = 300 * $total_premium_users;

        // ビューにデータを渡す
        return view('admin.home', compact(
            'total_users',
            'total_premium_users',
            'total_free_users',
            'total_restaurants',
            'total_reservations',
            'sales_for_this_month'
        ));
    }
}
