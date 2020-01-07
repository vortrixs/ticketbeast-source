<?php

namespace Factories;

use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Database\Eloquent\Collection;

class OrderFactory
{
    public static function createForConcert(Concert $concert, array $overrides = [], int $ticketQuantity = 1) : Order
    {
        /** @var Order $order */
        $order = factory(Order::class)->create($overrides);

        /** @var Collection $tickets */
        $tickets = factory(Ticket::class, $ticketQuantity)->create(['concert_id' => $concert->id]);

        $order->tickets()->saveMany($tickets);

        return $order;
    }
}
