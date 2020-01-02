<?php


namespace Tests\Unit;

use App\Facades\TicketCode;
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
        /** @var Ticket $ticket */
        $ticket = factory(Ticket::class)->state('reserved')->create();
        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();

        $this->assertNull($ticket->fresh()->reserved_at);
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

    /**
     * @test
     */
    public function a_ticket_can_be_claimed_for_an_order()
    {
        /** @var Order $order */
        $order = factory(Order::class)->create();

        /** @var Ticket $ticket */
        $ticket = factory(Ticket::class)->create(['code' => null]);

        TicketCode::shouldReceive('generateFor')->with($ticket)->andReturn('TICKETCODE1');

        $this->assertNull($ticket->code);

        $ticket->claimFor($order);

        $this->assertContains($ticket->id, $order->tickets()->pluck('id'));

        $this->assertEquals('TICKETCODE1', $ticket->code);
    }
}
