<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // indexアクション（レビュー一覧ページ）
    public function index(Restaurant $restaurant)
    {
        $user = Auth::user();

        $isPremium = $this->isPremiumUser($user);

        $query = Review::where('restaurant_id', $restaurant->id)
            ->orderBy('created_at', 'desc');

        if ($isPremium) {
            // 1ページあたり5件のページネーションを適用
            $reviews = $query->paginate(5);
        } else {
            // 3件までのデータを取得
            $reviews = $query->take(3)->get();
        }

        return view('reviews.index', compact('restaurant', 'reviews'));

    }

    // createアクション（レビュー投稿ページ）
    public function create(Restaurant $restaurant)
    {
        return view('reviews.create', compact('restaurant'));
    }

    // storeアクション（レビュー投稿機能）
    public function store(Request $request, Restaurant $restaurant)
    {
        // バリデーション
        $request->validate([
            'score' => 'required|integer|between:1,5',
            'content' => 'required|string',
        ]);

        $review = new Review();
        $review->score = $request->score;
        $review->content = $request->content;
        $review->restaurant_id = $restaurant->id;
        $review->user_id = Auth::id();

        $review->save();

        return redirect()->route('reviews.index', $restaurant)
            ->with('flash_message', 'レビューを投稿しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    // editアクション（レビュー編集ページ）
    public function edit(Restaurant $restaurant, Review $review)
    {
        // ログイン中のユーザーのレビューか確認
    if ($review->user_id !== Auth::id()) {
        // 一致しない場合一覧ページにリダイレクト
        return redirect()->route('reviews.index', $restaurant->id)
            ->with('error_message', '不正なアクセスです。');
    }

    // レビューとレストランをビューに渡す
    return view('reviews.edit', compact('restaurant', 'review'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Restaurant $restaurant, Review $review)
    {
        // ログイン中のユーザーがレビューの所有者か確認
        if ($review->user_id !== Auth::id()) {
            // 一致しない場合一覧ページにリダイレクト
            return redirect()->route('reviews.index', $restaurant->id)
                ->with('error_message', '不正なアクセスです。');
        }

        // editと同じバリデーション
        $request->validate([
            'score' => 'required|integer|between:1,5',
            'content' => 'required|string',
        ]);

        // レビューの更新
        $review->update([
            'score' => $request->score,
            'content' => $request->content,
        ]);

        // 更新後、レビュー一覧ページにリダイレクト
        return redirect()->route('reviews.index', $restaurant->id)
            ->with('flash_message', 'レビューを編集しました。');
    }

    // destroyアクション（レビュー削除機能）
    public function destroy(Restaurant $restaurant, Review $review)
    {
        // ログイン中のユーザーがレビューの所有者か確認
    if ($review->user_id !== Auth::id()) {
        // 一致しない場合一覧ページにリダイレクト
        return redirect()->route('reviews.index', $restaurant->id)
            ->with('error_message', '不正なアクセスです。');
    }

    // レビューの削除
    $review->delete();

    // 削除後、レビュー一覧ページにリダイレクト
    return redirect()->route('reviews.index', $restaurant->id)
        ->with('flash_message', 'レビューを削除しました。');
    }
}
