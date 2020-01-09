<?php

namespace Tests\Unit\Http\Middleware;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Tests\TestCase;

class ForceStripeAccountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function users_without_a_stripe_account_are_forced_to_connect_with_stripe()
    {
        $user = factory(User::class)->create([
            'stripe_account_id' => null,
        ]);

        $middleware = new ForceStripeAccount;

        $response = $middleware->handle($request, $callback);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('backstage.stripe.authorize'), $response->getTargetUrl());
    }
}
