<?php

namespace Tests\Unit;

use App\Billing\FakePaymentGateway;
use App\Concert;
use App\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function converting_to_an_array()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 1200])->addTickets(5);
        $paymentGateway = new FakePaymentGateway;

        $order = $concert->reserveTickets(5, 'foo@bar.com')->complete($paymentGateway, $paymentGateway->getToken());

        $result = $order->toArray();

        $this->assertEquals([
            'email' => 'foo@bar.com',
            'ticket_quantity' => 5,
            'amount' => $order->amount,
        ], $result);
    }

    /**
     * @test
     */
    public function retrieve_an_order_by_confirmation_number()
    {
        $order = factory(Order::class)->create(['confirmation_number' => 'ORDER_CONFIRMATION_NUMBER_1234']);

        $foundOrder = Order::findByConfirmationNumber('ORDER_CONFIRMATION_NUMBER_1234');

        $this->assertEquals($order->id, $foundOrder->id);
    }

    /**
     * @test
     */
    public function retrieving_a_nonexistent_order_by_confirmation_number_throws_an_exception()
    {
        $this->expectException(ModelNotFoundException::class);

        Order::findByConfirmationNumber('NONEXISTENT_ORDER_CONFIRMATION_NUMBER_1234');

        $this->fail('No matching order was found for the specified confirmation number, but an exception was not thrown');
    }
}
