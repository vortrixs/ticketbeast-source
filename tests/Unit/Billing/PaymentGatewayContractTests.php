<?php


namespace Tests\Unit\Billing;

use App\Billing\IPaymentGateway;
use App\Billing\PaymentFailedException;

/**
 * @property IPaymentGateway $gateway
 */
trait PaymentGatewayContractTests
{
    /**
     * @test
     */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        $token = $this->gateway->getToken($this->getTokenData());
        $lastCharge = $this->lastCharge();

        $charge = $this->gateway->charge(2500, $token, env('STRIPE_TEST_PROMOTER_ID'));

        $this->assertCount(1, $this->newCharges($lastCharge));
        $this->assertEquals(2500, $charge->getAmount());
    }

    /**
     * @test
     */
    public function charges_with_an_invalid_token_fails()
    {
        $lastCharge = $this->lastCharge();

        try {
            $this->gateway->charge(2500, 'invalid-token', env('STRIPE_TEST_PROMOTER_ID'));
        } catch (PaymentFailedException $e) {
            $this->assertCount(0, $this->newCharges($lastCharge));

            return;
        }

        $this->fail('Charging with an invalid payment token did not throw a PaymentFailedException');
    }

    /**
     * @test
     */
    public function can_get_details_about_a_successful_charge()
    {
        $token = $this->gateway->getToken($this->getTokenData());

        /** @var  $charge */
        $charge = $this->gateway->charge(2500, $token, env('STRIPE_TEST_PROMOTER_ID'));

        $this->assertEquals(substr($this::TEST_CARD_NUMBER, -4), $charge->getCardLastFour());
        $this->assertEquals(2500, $charge->getAmount());
        $this->assertEquals(env('STRIPE_TEST_PROMOTER_ID'), $charge->getDestination());
    }
}
