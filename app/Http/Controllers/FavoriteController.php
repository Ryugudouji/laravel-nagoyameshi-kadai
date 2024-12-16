<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // indexアクション（お気に入り一覧ページ）
    public function index()
    {
        $favorite_restaurants = Auth::user()
            ->favorites()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('favorites.index', ['favorite_restaurants' => $favorite_restaurants,]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    // storeアクション（お気に入り追加機能）
    public function store($id)
    {
        // 対象の店舗を取得
        $restaurant = Restaurant::findOrFail($id);

        Auth::user()->favorites()->attach($restaurant->id);

        session()->flash('flash_message', 'お気に入りに追加しました。');

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    // destroyアクション（お気に入り解除機能）
    public function destroy($id)
    {
        $restaurant = Restaurant::findOrFail($id);

        Auth::user()->favorites()->detach($restaurant->id);

        session()->flash('flash_message', 'お気に入りを解除しました。');

        return redirect()->back();
    }
}
