<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * createアクション（有料プラン登録ページ）
     */
    public function create()
    {
        $user = Auth::user();


        // 現在ログイン中のユーザーのSetupIntentオブジェクトを作成
        $intent = Auth::user()->createSetupIntent();

        return view('subscription.create', compact('intent'));
    }

    /**
     * storeアクション（有料プラン登録機能）
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // プレミアムプラン登録処理
        $request->user()->newSubscription(
            'premium_plan',
            'price_1QTekHP1x9xomPwVGawxXAOm'
            )->create($request->paymentMethodId);

        return redirect()->route('home')->with('flash_message', '有料プランへの登録が完了しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * editアクション（お支払い方法編集ページ）
     */
    public function edit()
    {
        $user = Auth::user();

        $intent = $user->createSetupIntent();

        return view('subscription.edit', compact('user', 'intent'));
    }

    /**
     * updateアクション（お支払い方法更新機能）
     */
    public function update(Request $request)
{
    $user = Auth::user();

    // 支払い方法IDを取得
    $paymentMethodId = $request->paymentMethodId;

    // ユーザーがStripeカスタマーであることを確認
    if (!$user->hasStripeId()) {
        $user->createAsStripeCustomer();
    }

    // 支払い方法を更新
    try {
        $user->updateDefaultPaymentMethod($paymentMethodId);
        session()->flash('flash_message', 'お支払い方法を変更しました。');
    } catch (\Exception $e) {
        return redirect()->route('home')->withErrors('支払い方法の変更に失敗しました。');
    }

    return redirect()->route('home');
}

    /**
     * cancelアクション（有料プラン解約ページ）
     */
    public function cancel(Request $request)
    {
        $user = Auth::user();

        // サブスクリプションをビューに渡す
        return view('subscription.cancel', compact('user'));
    }

    /**
     * destroyアクション（有料プラン解約機能）
     */
    public function destroy(Request $request)
{
    $user = $request->user();

    // サブスクリプションの確認
    if ($user->subscription('premium_plan')) {
        // サブスクリプションを即座に解約
        $user->subscription('premium_plan')->cancelNow();
        session()->flash('flash_message', '有料プランを解約しました。');
    } else {
        return redirect()->route('home')->withErrors('解約するサブスクリプションが見つかりません。');
    }

    return redirect()->route('home');
}
}
