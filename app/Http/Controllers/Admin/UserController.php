<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {

        $keyword = $request->input('keyword');

        if ($keyword !== null) {
            $users = User::where('name', 'like', "%{$keyword}%")
                ->orWhere('kana', 'like', "%{$keyword}%")
                ->paginate(15);
        } else {
            $users = User::paginate(15);
        }

        $total = $users->total();

        return view('admin.users.index', compact('users', 'keyword', 'total'));

    }

    public function show($id)
    {

    $user = User::findOrFail($id);

    return view('admin.users.show', compact('user'));
    }
}
