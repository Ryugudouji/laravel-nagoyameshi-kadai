<?php

namespace App\Http\Controllers;

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
        // 現在ログイン中のユーザーのSetupIntentオブジェクトを作成
        $intent = Auth::user()->createSetupIntent();

        return view('subscription.create', compact('intent'));
    }

    /**
     * storeアクション（有料プラン登録機能）
     */
    public function store(Request $request)
    {
        $request->user()->newSubscription(
            'premium_plan', 'price_1QTekHP1x9xomPwVGawxXAOm'
        )->create($request->paymentMethodId);

        session()->flash('flash_message', '有料プランへの登録が完了しました。');

        return redirect()->route('home');

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

        $user->updateDefaultPaymentMethod($paymentMethodId);

        session()->flash('flash_message', 'お支払い方法を変更しました。');

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

        // サブスクリプションをすぐに解約
        $user->subscription('premium_plan')->cancelNow();

        session()->flash('flash_message', '有料プランを解約しました。');

        return redirect()->route('home');
    }
}
