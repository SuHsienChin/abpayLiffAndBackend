<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
//use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    //use AuthenticatesUsers;

    // 使用者登入頁面
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 登入驗證
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    // 登入成功後的重定向
    protected function authenticated(Request $request, $user)
    {
        // 可在此處進行登入後的特定處理
        return redirect()->intended(route('/admin/users'));
    }
}
