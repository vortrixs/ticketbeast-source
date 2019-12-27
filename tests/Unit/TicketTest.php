<?php


namespace Tests\Unit;

use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function a_ticket_can_be_released()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create()->addTickets(1);

        $order = Order::forTickets($concert->findAvailableTickets(1), 'foo@bar.com', 2000);

        /** @var Ticket $ticket */
        $ticket = $order->tickets()->first();

        $this->assertEquals($order->id, $ticket->order_id);

        $ticket->release();

        $this->assertNull($ticket->fresh()->order_id);
    }

    /**
     * @test
     */
    public function a_ticket_can_be_reserved()
    {
        /** @var Ticket $ticket */
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);
    }
}
