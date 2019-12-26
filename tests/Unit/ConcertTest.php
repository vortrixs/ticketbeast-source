<?php

namespace Tests\Unit;

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function can_get_formatted_date()
    {
        $concert = factory(Concert::class)->make(['date' => Carbon::parse('2020-01-12 8:00PM')]);

        $this->assertEquals('January 12, 2020', $concert->getDate());
    }

    /**
     * @test
     */
    public function can_get_formatted_time()
    {
        $concert = factory(Concert::class)->make(['date' => Carbon::parse('2020-01-02 17:00:00')]);

        $this->assertEquals('5:00PM', $concert->getTime());
    }

    /**
     * @test
     */
    public function can_get_ticket_price_in_dollars()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->make(['ticket_price' => 6750]);

        $this->assertEquals('67.50', $concert->getTicketPrice());
    }

    /**
     * @test
     */
    public function concerts_with_a_published_at_date_are_published()
    {
        $publishedConcertA = factory(Concert::class)->state('published')->create();
        $publishedConcertB = factory(Concert::class)->state('published')->create();
        $publishedConcertC = factory(Concert::class)->state('unpublished')->create();

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($publishedConcertC));
    }

    /**
     * @test
     */
    public function can_order_concert_tickets()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create()->addTickets(3);

        $order = $concert->orderTickets('foo@bar.com', 3);

        $this->assertEquals('foo@bar.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /**
     * @test
     */
    public function can_add_tickets()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create()->addTickets(50);

        $this->assertEquals(50, $concert->getRemainingTickets());
    }

    /**
     * @test
     */
    public function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create()->addTickets(50);

        $concert->orderTickets('foo@bar.com', 30);

        $this->assertEquals(20, $concert->getRemainingTickets());
    }

    /**
     * @test
     */
    public function trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create()->addTickets(10);

        try {
            $concert->orderTickets('foo@bar.com', 11);
        } catch (NotEnoughTicketsException $e) {
            $this->assertOrderDoesntExistFor($concert, 'foo@bar.com');

            $this->assertEquals(10, $concert->getRemainingTickets());

            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining.');
    }

    /**
     * @test
     */
    public function cannot_order_tickets_that_have_already_been_purchased()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create()->addTickets(10);

        $concert->orderTickets('foo@bar.com', 8);

        try {
            $concert->orderTickets('baz@bar.com', 3);
        } catch (NotEnoughTicketsException $e) {
            $this->assertOrderDoesntExistFor($concert, 'baz@bar.com');

            $this->assertEquals(2, $concert->getRemainingTickets());

            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining.');
    }
}
