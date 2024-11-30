<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {

        // restaurantsテーブルから6件のデータを取得
        $highly_rated_restaurants = Restaurant::take(6)->get();

        // categoriesテーブルからすべてのデータを取得
        $categories = Category::all();

        // 作成日時が新しい順に並べたrestaurantsテーブルの6件のデータを取得
        $new_restaurants = Restaurant::orderBy('created_at', 'desc')->take(6)->get();

        // ビューにデータを渡す
        return view('home',[
            'highly_rated_restaurants' => $highly_rated_restaurants,
            'categories' => $categories,
            'new_restaurants' => $new_restaurants,

        ]);
    }
}
