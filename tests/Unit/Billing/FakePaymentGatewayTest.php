<?php


namespace Tests\Unit\Billing;

use App\Billing\Charge;
use App\Billing\FakePaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    private $gateway;

    const TEST_CARD_NUMBER = 4242424242424242;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakePaymentGateway;
    }

    protected function lastCharge()
    {
        return $this->gateway->getCharges()->last();
    }

    protected function newCharges($lastCharge)
    {
        $charges = $this->gateway->getCharges();

        if (null !== $lastCharge) {
            $id = $charges->search(function ($item) use ($lastCharge) {
                return $item->id == $lastCharge->id;
            });
        }

        return $charges->slice(
            isset($id) ? $id+1 : 0
        );
    }

    protected function getTokenData()
    {
        return ['card_number' => $this::TEST_CARD_NUMBER];
    }

    /**
     * @test
     */
    public function running_a_hook_before_the_first_charge()
    {
        $timesCallbackRan = 0;

        $this->gateway->beforeFirstCharge(function (FakePaymentGateway $paymentGateway) use (&$timesCallbackRan) {
            $timesCallbackRan++;
            $paymentGateway->charge(
                2500,
                $paymentGateway->getToken($this->getTokenData()),
                'test_account_1234'
            );
            $this->assertEquals(2500, $paymentGateway->getTotalCharges());
        });

        $this->gateway->charge(
            2500,
            $this->gateway->getToken($this->getTokenData()),
            'test_account_1234'
        );

        $this->assertEquals(1, $timesCallbackRan);
        $this->assertEquals(5000, $this->gateway->getTotalCharges());
    }

    /**
     * @test
     */
    public function can_get_total_charges_for_a_specific_account()
    {
        $gateway = new FakePaymentGateway;

        $gateway->charge(1000, $gateway->getToken(), 'test_account_0000');
        $gateway->charge(2500, $gateway->getToken(), 'test_account_1234');
        $gateway->charge(4000, $gateway->getToken(), 'test_account_1234');

        $this->assertEquals(6500, $gateway->getTotalChargesFor('test_account_1234'));
    }
}
