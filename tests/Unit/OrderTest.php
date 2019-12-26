<?php


namespace Tests\Unit;

use App\Concert;
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

        $order = $concert->orderTickets('foo@bar.com', 5);

        $this->assertEquals(5, $concert->getRemainingTickets());

        $order->cancel();

        $this->assertEquals(10, $concert->getRemainingTickets());
    }

    /**
     * @test
     */
    public function converting_to_an_array()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 1200])->addTickets(5);

        $order = $concert->orderTickets('foo@bar.com', 5);

        $result = $order->toArray();

        $this->assertEquals([
            'email' => 'foo@bar.com',
            'ticket_quantity' => 5,
            'amount' => 5*1200,
        ], $result);
    }
}
