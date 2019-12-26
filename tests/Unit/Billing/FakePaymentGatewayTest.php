<?php


namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\IPaymentGateway;
use App\Billing\PaymentFailedException;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    /**
     * @test
     */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        $gateway = new FakePaymentGateway;

        $gateway->charge(2500, $gateway->getValidTestToken());

        $this->assertEquals(2500, $gateway->getTotalCharges());
    }

    /**
     * @test
     */
    public function charges_with_an_invalid_token_fails()
    {
        try {
            (new FakePaymentGateway)->charge(2500, 'invalid-token');
        } catch (PaymentFailedException $e) {
            $this->assertIsObject($e);

            return;
        }

        $this->fail();
    }
}
