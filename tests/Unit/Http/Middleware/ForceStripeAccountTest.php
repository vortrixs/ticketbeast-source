<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\ForceStripeAccount;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ForceStripeAccountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function users_without_a_stripe_account_are_forced_to_connect_with_stripe()
    {
        $this->be(factory(User::class)->create([
            'stripe_account_id' => null,
        ]));

        $middleware = new ForceStripeAccount;

        $response = $middleware->handle(new Request, function (Request $request) {
            $this->fail('Next middleware was called when it should not have been.');
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('backstage.stripe.connect'), $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function users_with_a_stripe_account_connected_ca_continue()
    {
        $this->be(factory(User::class)->create([
            'stripe_account_id' => 'test_stripe_account_1234',
        ]));

        $middleware = new ForceStripeAccount;

        $request = new Request;

        $middleware->handle($request, function (Request $requestParam) use ($request) {
            $this->assertSame($request, $requestParam);
        });
    }

    /**
     * @test
     */
    public function middleware_is_applied_to_all_relevant_backstage_routes()
    {
        $routes = [
            'backstage.concerts.index',
            'backstage.concerts.store',
            'backstage.concerts.new',
            'backstage.concerts.edit',
            'backstage.concerts.update',
            'backstage.published_concerts.store',
            'backstage.published_concert_orders.index',
            'backstage.concert_messages.new',
            'backstage.concert_messages'
        ];

        foreach ($routes as $route) {
            $this->assertContains(
                ForceStripeAccount::class,
                Route::getRoutes()->getByName($route)->gatherMiddleware(),
                "Assertion failed for route '{$route}'"
            );
        }
    }
}
