<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        if (false === Auth::attempt($request->only(['email', 'password']))) {
            return redirect('/login')
                ->withErrors([
                    'email' => 'These credentials do not match our records.',
                ])
                ->exceptInput('password');
        }

        return redirect('/backstage/concerts');
    }

    public function show()
    {
        return view('login.show');
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/login');
    }
}
