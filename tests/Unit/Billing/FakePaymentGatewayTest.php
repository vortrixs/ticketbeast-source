<?php


namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    private $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakePaymentGateway;
    }

    protected function getTokenData()
    {
        return [];
    }

    protected function lastCharge()
    {
        return $this->gateway->retrieveAllCharge()->last();
    }

    protected function newCharges($lastCharge)
    {
        $charges = $this->gateway->retrieveAllCharge();

        $id = $charges->search(function ($item) use ($lastCharge) {
            return $item->id == $lastCharge->id;
        });

        return $charges->slice($id+1);
    }

    /**
     * @test
     */
    public function running_a_hook_before_the_first_charge()
    {
        $timesCallbackRan = 0;

        $this->gateway->beforeFirstCharge(function (FakePaymentGateway $paymentGateway) use (&$timesCallbackRan) {
            $timesCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getToken());
            $this->assertEquals(2500, $paymentGateway->retrieveAllCharge()->sum('amount'));
        });

        $this->gateway->charge(2500, $this->gateway->getToken());

        $this->assertEquals(1, $timesCallbackRan);
        $this->assertEquals(5000, $this->gateway->retrieveAllCharge()->sum('amount'));
    }
}
