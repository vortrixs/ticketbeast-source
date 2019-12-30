<?php

namespace Tests\Unit;

use App\Billing\Charge;
use App\Billing\FakePaymentGateway;
use App\Concert;
use App\IConfirmationNumberGenerator;
use App\Order;
use App\Ticket;
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
        $order = factory(Order::class)->create([
            'email' => 'foo@bar.com',
            'amount' => 6000,
            'confirmation_number' => 'ORDER_CONFIRMATION_NUMBER_1234',
        ]);

        $order->tickets()->saveMany(
            factory(Ticket::class)->times(5)->create()
        );

        $result = $order->toArray();

        $this->assertEquals([
            'confirmation_number' => 'ORDER_CONFIRMATION_NUMBER_1234',
            'email' => 'foo@bar.com',
            'ticket_quantity' => 5,
            'amount' => 6000,
        ], $result);
    }

    /**
     * @test
     */
    public function creating_an_order_from_tickets_email_and_charge()
    {
        $tickets = factory(Ticket::class)->times(3)->create();
        $charge = new Charge(['amount' => 3600, 'card_last_four' => 1234]);

        $order = Order::forTickets($tickets, 'foo@bar.com', $charge);

        $this->assertEquals('foo@bar.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(1234, $order->card_last_four);
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
