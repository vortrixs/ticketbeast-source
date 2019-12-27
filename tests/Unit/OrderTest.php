<?php

namespace Tests\Unit;

use App\Billing\FakePaymentGateway;
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
    public function converting_to_an_array()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 1200])->addTickets(5);
        $paymentGateway = new FakePaymentGateway;

        $order = $concert->reserveTickets(5, 'foo@bar.com')->complete($paymentGateway, $paymentGateway->getValidTestToken());

        $result = $order->toArray();

        $this->assertEquals([
            'email' => 'foo@bar.com',
            'ticket_quantity' => 5,
            'amount' => $order->amount,
        ], $result);
    }
}
