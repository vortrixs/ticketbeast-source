<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Illuminate\Support\Arr;
use Stripe\Charge;
use Stripe\Transfer;
use Tests\TestCase;

/**
 * @group integration
 */
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

    protected function lastCharge() : Charge
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

    /**
     * @test
     */
    public function ninety_percent_of_the_payment_is_transferred_to_the_destination_account()
    {
        $token = $this->gateway->getToken($this->getTokenData());

        $this->gateway->charge(5000, $token, env('STRIPE_TEST_PROMOTER_ID'));

        $lastStripeCharge = $this->lastCharge();

        $this->assertEquals(5000, $lastStripeCharge->amount);
        $this->assertEquals(env('STRIPE_TEST_PROMOTER_ID'), $lastStripeCharge->destination);

        $transfer = Transfer::retrieve(
            $lastStripeCharge->transfer,
            ['api_key' => config('services.stripe.secret')]
        );

        $this->assertEquals(4500, $transfer->amount);
    }
}
