<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Stripe\OAuth;
use Stripe\Stripe;

class StripeConnectController extends Controller
{
    public function connect()
    {
        return view('backstage.stripe.connect');
    }

    public function authorizeRedirect()
    {
        $url = Oauth::authorizeUrl([
            'response_type' => 'code',
            'scope' => 'read_write',
            'client_id' => config('services.stripe.client_id'),
        ]);

        return redirect($url);
    }

    public function redirect()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $response = OAuth::token([
            'grant_type' => 'authorization_code',
            'code' => request()->query('code'),
        ]);

        Auth::user()->update([
            'stripe_account_id' => $response->stripe_user_id,
            'stripe_access_token' => $response->access_token,
        ]);

        return redirect()->route('backstage.concerts.index');
    }
}
