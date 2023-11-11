<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // 顯示使用者列表
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    // 顯示新增使用者的表單
    public function create()
    {
        return view('admin.users.create');
    }

    // 處理新增使用者的資料
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,customer',
        ]);

        User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'role' => $request->input('role'),
        ]);

        return redirect()->route('users.index')->with('success', '使用者新增成功');
    }

    // 顯示編輯使用者的表單
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    // 處理更新使用者的資料
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,customer',
        ]);

        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role' => $request->input('role'),
        ]);

        return redirect()->route('users.index')->with('success', '使用者資料更新成功');
    }

    // 刪除使用者
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', '使用者刪除成功');
    }
}
