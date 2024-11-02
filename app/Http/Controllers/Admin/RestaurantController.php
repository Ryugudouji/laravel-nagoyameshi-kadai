<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');

        if ($keyword !==null) {
            $restaurants = Restaurant::where('name', 'like', "%{$keyword}%")->paginate(15);

            $total = $restaurants->total();
        } else {
            $restaurants = Restaurant::paginate(15);
            $total = $restaurants->total();
        }

        return view('admin.restaurants.index', compact('restaurants', 'keyword', 'total'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $categories = Category::all();

        return view('admin.restaurants.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'name' => 'required',
            'image' => 'image|max:2048',
            'description' => 'required',
            'lowest_price' => 'required|integer|min:0|lte:highest_price',
            'highest_price' => 'required|integer|min:0|gte:lowest_price',
            'postal_code' => 'required|digits:7',
            'address' => 'required',
            'opening_time' => 'required|before:closing_time',
            'closing_time' => 'required|after:opening_time',
            'seating_capacity' => 'required|integer|min:0',
        ]);

            // フォームの入力内容をもとに、テーブルにデータを追加する
            $restaurant = new Restaurant();
            $restaurant->name = $request->input('name');
            $restaurant->description = $request->input('description');
            $restaurant->lowest_price =  $request->input('lowest_price');
            $restaurant->highest_price =  $request->input('highest_price');
            $restaurant->postal_code =  $request->input('postal_code');
            $restaurant->address =  $request->input('address');
            $restaurant->opening_time =  $request->input('opening_time');
            $restaurant->closing_time =  $request->input('closing_time');
            $restaurant->seating_capacity =  $request->input('seating_capacity');

            // アップロードされたファイル（name="image"）が存在すれば処理を実行する
        if ($request->hasFile('image')) {
            // アップロードされたファイル（name="image"）をstorage/app/public/restaurantsフォルダに保存し
            // 戻り値（ファイルパス）を変数$imageに代入する
            $image = $request->file('image')->store('public/restaurants');
            // ファイルパスからファイル名のみを取得し、Restaurantインスタンスのimageプロパティに代入する
            $restaurant->image = basename($image);
        } else {
            // 画像ファイルがない場合
            $restaurant->image = ''; // 空文字を代入
        }

            $restaurant->save();

            $category_ids = array_filter($request->input('category_ids'));
            $restaurant->categories()->sync($category_ids);

            // リダイレクト先とフラッシュメッセージ
            return redirect()->route('admin.restaurants.index')
                ->with('flash_message', '店舗を登録しました。');

    }

    /**
     * Display the specified resource.
     */
    public function show(Restaurant $restaurant)
    {
        return view('admin.restaurants.show', compact('restaurant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Restaurant $restaurant)
    {
        $categories = Category::all();

        $category_ids = $restaurant->categories->pluck('id')->toArray();

        return view('admin.restaurants.edit', compact('restaurant', 'categories', 'category_ids'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        // バリデーション
        $request->validate([
            'name' => 'required',
            'image' => 'image|max:2048',
            'description' => 'required',
            'lowest_price' => 'required|integer|min:0|lte:highest_price',
            'highest_price' => 'required|integer|min:0|gte:lowest_price',
            'postal_code' => 'required|digits:7',
            'address' => 'required',
            'opening_time' => 'required|before:closing_time',
            'closing_time' => 'required|after:opening_time',
            'seating_capacity' => 'required|integer|min:0',
        ]);

            // フォームの入力内容をもとに、テーブルにデータを追加する
            $restaurant->name = $request->input('name');
            $restaurant->description = $request->input('description');
            $restaurant->lowest_price =  $request->input('lowest_price');
            $restaurant->highest_price =  $request->input('highest_price');
            $restaurant->postal_code =  $request->input('postal_code');
            $restaurant->address =  $request->input('address');
            $restaurant->opening_time =  $request->input('opening_time');
            $restaurant->closing_time =  $request->input('closing_time');
            $restaurant->seating_capacity =  $request->input('seating_capacity');

            // アップロードされたファイル（name="image"）が存在すれば処理を実行する
        if ($request->hasFile('image')) {
            // アップロードされたファイル（name="image"）をstorage/app/public/restaurantsフォルダに保存し
            // 戻り値（ファイルパス）を変数$imageに代入する
            $image = $request->file('image')->store('public/restaurants');
            // ファイルパスからファイル名のみを取得し、Restaurantインスタンスのimageプロパティに代入する
            $restaurant->image = basename($image);
        }

            $restaurant->save();

            $category_ids = array_filter($request->input('category_ids'));
            $restaurant->categories()->sync($category_ids);

            // リダイレクト先とフラッシュメッセージ
            return redirect()->route('admin.restaurants.show', $restaurant)
                ->with('flash_message', '店舗を編集しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')
            ->with('flash_massage', '店舗を削除しました。');
    }
}
