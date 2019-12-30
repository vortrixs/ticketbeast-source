<?php


namespace Tests\Unit;

use App\Billing\FakePaymentGateway;
use App\Concert;
use App\Order;
use App\Reservation;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery\MockInterface;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function calculating_the_total_cost()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets, 'foo@bar.com');

        $this->assertEquals(3*1200, $reservation->getTotalAmount());
    }

    /**
     * @test
     */
    public function reserved_tickets_are_released_when_a_reservation_is_cancelled()
    {
        $tickets = collect([
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
        ]);

        $reservation = new Reservation($tickets, 'foo@bar.com');

        $reservation->cancel();

        $tickets->each(function (MockInterface $ticket) {
            $ticket->shouldHaveReceived('release')->once();
        });
    }

    /**
     * @test
     */
    public function completing_a_reservation()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create(['ticket_price' => 1200])->addTickets(3);
        $tickets = $concert->findAvailableTickets(3);
        $paymentGateway = new FakePaymentGateway;
        $reservation = new Reservation($tickets, 'foo@bar.com');

        /** @var Order $order */
        $order = $reservation->complete($paymentGateway, $paymentGateway->getToken());

        $this->assertOrderExistsFor($concert, $order->email);
        $this->assertEquals(3, $order->tickets()->count());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(3600, $paymentGateway->getTotalCharges());
    }
}
