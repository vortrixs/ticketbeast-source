<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Invitation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $invitation = Invitation::findByCode(request('code'));

        abort_if($invitation->hasBeenUsed(), Response::HTTP_NOT_FOUND);

        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        $user = User::create([
            'email' => request('email'),
            'password' => bcrypt(request('password')),
        ]);

        $invitation->useForUser($user);

        Auth::login($user);

        return redirect()->route('backstage.concerts.index');
    }
}
