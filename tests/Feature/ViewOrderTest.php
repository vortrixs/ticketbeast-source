<?php

namespace Tests\Feature;

use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ViewOrderTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function user_can_view_their_order_confirmation()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create();

        /** @var Order $order */
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDER_CONFIRMATION_1234',
            'card_last_four' => 1881,
            'amount' => 8500,
        ]);

        factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKET_CODE_123',
        ]);

        factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKET_CODE_456',
        ]);

        $response = $this->get("/orders/ORDER_CONFIRMATION_1234");

        $response->assertStatus(200);

        $response->assertViewHas('order', function ($viewOrder) use ($order) {
            return $order->id === $viewOrder->id;
        });

        $response->assertSee('ORDER_CONFIRMATION_1234');
        $response->assertSee('$85.00');
        $response->assertSee('**** **** **** 1881');
        $response->assertSee('TICKET_CODE_123');
        $response->assertSee('TICKET_CODE_456');
    }
}
