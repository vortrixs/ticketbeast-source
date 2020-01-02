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
use Illuminate\Support\Collection;
use Mockery\MockInterface;
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

        $order->tickets()->saveMany([
            factory(Ticket::class)->create(['code' => 'TICKETCODE1']),
            factory(Ticket::class)->create(['code' => 'TICKETCODE2']),
            factory(Ticket::class)->create(['code' => 'TICKETCODE3']),
        ]);

        $result = $order->toArray();

        $this->assertEquals([
            'confirmation_number' => 'ORDER_CONFIRMATION_NUMBER_1234',
            'email' => 'foo@bar.com',
            'amount' => 6000,
            'tickets' => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ],
        ], $result);
    }

    /**
     * @test
     */
    public function creating_an_order_from_tickets_email_and_charge()
    {
        $charge = new Charge(['amount' => 3600, 'card_last_four' => 1234]);
        $tickets = collect([
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
        ]);

        $order = Order::forTickets($tickets, 'foo@bar.com', $charge);

        $this->assertEquals('foo@bar.com', $order->email);
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(1234, $order->card_last_four);
        $tickets->each->shouldHaveReceived('claimFor', [$order]);
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
