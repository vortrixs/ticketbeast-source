<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Illuminate\Support\Arr;
use Stripe\Charge;
use Tests\TestCase;

class StripePaymentGatewayTest extends TestCase
{
    private function lastCharge()
    {
        return Arr::first(Charge::all(['limit' => 1], ['api_key' => config('services.stripe.secret')])->data);
    }

    private function newCharges($lastCharge)
    {
        return Charge::all(['ending_before' => $lastCharge], ['api_key' => config('services.stripe.secret')])->data;
    }

    /**
     * @test
     */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        $gateway = new StripePaymentGateway(config('services.stripe.secret'));

        $token = $gateway->getToken([
            'number' => '4242424242424242',
            'exp_month' => 1,
            'exp_year' => date('Y')+1,
            'cvc' => '123',
        ])->id;

        $lastCharge = $this->lastCharge();

        $charge = $gateway->charge(2500, $token);

        $retrievedCharge = $gateway->retrieveCharge($charge->id);

        $this->assertEquals($charge->id, $retrievedCharge->id);
        $this->assertEquals(2500, $retrievedCharge->amount);
    }

    /**
     * @test
     */
    public function charges_with_an_invalid_token_fails()
    {
        $lastCharge = $this->lastCharge();

        try {
            (new StripePaymentGateway(config('services.stripe.secret')))->charge(2500, 'invalid-token');
        } catch (PaymentFailedException $e) {
            $this->assertCount(0, $this->newCharges($lastCharge));

            return;
        }

        $this->fail('Charging with an invalid payment token did not throw a PaymentFailedException');
    }
}
