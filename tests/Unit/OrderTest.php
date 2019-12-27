<?php


namespace Tests\Unit;

use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function tickets_are_released_when_an_order_is_cancelled()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create()->addTickets(10);

        $order = Order::forTickets($concert->findAvailableTickets(5), 'foo@bar.com', 5*2000);

        $this->assertEquals(5, $concert->countRemainingTickets());

        $order->cancel();

        $this->assertEquals(10, $concert->countRemainingTickets());
    }

    /**
     * @test
     */
    public function converting_to_an_array()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 1200])->addTickets(5);

        $order = Order::forTickets($concert->findAvailableTickets(5), 'foo@bar.com', 5*1200);

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
    public function creating_an_order_from_tickets_email_and_amount()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create()->addTickets(5);

        $this->assertEquals(5, $concert->countRemainingTickets());

        $order = Order::forTickets($concert->findAvailableTickets(3), 'foo@bar.com', 3600);

        $this->assertEquals('foo@bar.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(2, $concert->countRemainingTickets());
    }
}
