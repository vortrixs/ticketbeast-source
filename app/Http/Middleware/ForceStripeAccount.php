<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;

class ForceStripeAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if (null === Auth::user()->stripe_account_id) {
            return redirect()->route('backstage.stripe.connect');
        }

        return $next($request);
    }
}
