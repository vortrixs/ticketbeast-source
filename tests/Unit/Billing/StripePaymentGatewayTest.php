<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Illuminate\Support\Arr;
use Stripe\Charge;
use Tests\TestCase;

class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    private $gateway;

    const TEST_CARD_NUMBER = 4242424242424242;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new StripePaymentGateway(config('services.stripe.secret'));
    }

    protected function lastCharge()
    {
        return Arr::first(Charge::all(['limit' => 1], ['api_key' => config('services.stripe.secret')])->data);
    }

    protected function newCharges($lastCharge)
    {
        return Charge::all(['ending_before' => $lastCharge], ['api_key' => config('services.stripe.secret')])->data;
    }

    protected function getTokenData()
    {
        return [
            'number' => $this::TEST_CARD_NUMBER,
            'exp_month' => 1,
            'exp_year' => date('Y')+1,
            'cvc' => '123',
        ];
    }
}
