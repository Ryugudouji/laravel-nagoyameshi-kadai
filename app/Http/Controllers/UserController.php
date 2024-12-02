<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // 管理者はホームにリダイレクト
    if (Auth::guard('admin')->check()) {
        return redirect()->route('home');
    }

        return view('user.index', ['user' => $user]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(User $user)
    {

        // 管理者がアクセスしている場合はホームにリダイレクト
        if (Auth::guard('admin')->check()) {
            return redirect()->route('home');
        }

        // 現在ログイン中のユーザーと、編集対象のユーザーIDを比較
        if ($user->id !== Auth::id()) {
            // 一致しない場合は会員情報ページにリダイレクト
            return redirect()->route('user.index')->with('error_message', '不正なアクセスです。');
        }

        // 一致する場合、編集ページを表示
        return view('user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {

        // 現在ログイン中のユーザーと、更新対象のユーザーIDを比較
    if ($user->id !== Auth::id()) {
        // 一致しない場合は会員情報ページにリダイレクト
        return redirect()->route('user.index')->with('error_message', '不正なアクセスです。');
    }

    $validatedData = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'kana' => ['required', 'string', 'regex:/\A[ァ-ヴー\s]+\z/u', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        'postal_code' => ['required', 'digits:7'],
        'address' => ['required', 'string', 'max:255'],
        'phone_number' => ['required', 'digits_between:10, 11'],
        'birthday' => ['nullable', 'digits:8'],
        'occupation' => ['nullable', 'string', 'max:255'],
    ]);

    // データを更新
    $user->update($validatedData);

    // リダイレクトとフラッシュメッセージ
    return redirect()->route('user.index')->with('flash_message', '会員情報を編集しました。');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
