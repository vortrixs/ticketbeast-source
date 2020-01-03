<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login()
    {
        if (false === Auth::attempt(request(['email', 'password'])))
        {
            return redirect('/login')->withErrors([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        return redirect('/backstage/concerts');
    }

    public function show()
    {
        return view('login.show');
    }
}
