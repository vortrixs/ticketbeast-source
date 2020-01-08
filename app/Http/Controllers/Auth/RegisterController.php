<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Invitation;
use App\User;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function register()
    {
        $invitation = Invitation::findByCode(request('code'));

        $user = User::create([
            'email' => request('email'),
            'password' => bcrypt(request('password')),
        ]);

        $invitation->useForUser($user);

        Auth::login($user);

        return redirect()->route('backstage.concerts.index');
    }
}
