<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    // indexアクション（予約一覧ページ）
    public function index()
    {
        // ログイン中のユーザーの予約を取得
        $reservations = Reservation::where('user_id', auth()->id()) // ログイン中のユーザー
            ->orderBy('reserved_datetime', 'desc') // 新しい順
            ->paginate(15);

        return view('reservations.index', compact('reservations'));
    }

    // createアクション（予約ページ）
    public function create(Restaurant $restaurant)
    {
        return view('reservations.create', compact('restaurant'));
    }

    // storeアクション（予約機能）
    public function store(Request $request)
    {
            $validatedData = $request->validate([
            'reservation_date' => ['required', 'date_format:Y-m-d'],
            'reservation_time' => ['required', 'date_format:H:i'],
            'number_of_people' => ['required', 'integer', 'between:1,50'],
        ]);

        $reservation = new Reservation();
        $reservation->restaurant_id = $request->input('restaurant_id');
        $reservation->user_id = Auth::id();
        $reservation->reserved_datetime = $validatedData['reservation_date'] . ' ' . $validatedData['reservation_time'];
        $reservation->number_of_people = $validatedData['number_of_people'];

        $reservation->save();

        session()->flash('flash_message', '予約が完了しました。');

        return redirect()->route('reservations.index');
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

    // destroyアクション（予約キャンセル機能）
    public function destroy(Reservation $reservation)
{
    // 他人の予約をキャンセルできないようにするチェック
    if ($reservation->user_id !== Auth::id()) {
        // 不正なアクセス時の処理
        return redirect()
            ->route('reservations.index')
            ->with('error_message', '不正なアクセスです。');
    }

    // 予約データを削除
    $reservation->delete();

    // 削除後のリダイレクト
    return redirect()
        ->route('reservations.index')
        ->with('flash_message', '予約をキャンセルしました。');
}
}
