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

        $charge = $this->gateway->charge(2500, $token);

        $retrievedCharge = $this->gateway->retrieveCharge($charge->id);

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
            $this->gateway->charge(2500, 'invalid-token');
        } catch (PaymentFailedException $e) {
            $this->assertCount(0, $this->newCharges($lastCharge));

            return;
        }

        $this->fail('Charging with an invalid payment token did not throw a PaymentFailedException');
    }
}
