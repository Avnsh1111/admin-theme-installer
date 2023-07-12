<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function login() : View
    {
        return view('admin.auth.login');
    }

    public function checkLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email,type,1',
            'password' => 'required|min:6'
        ]);

        $isRemember = $request->has('remember') ? true : false;

        $credentials = $request->only('email','password');

        if (Auth::guard('admin')->attempt($credentials,$isRemember)) {
            return redirect()->route('admin.dashboard');
        }

        dd($request->all());
    }
}
