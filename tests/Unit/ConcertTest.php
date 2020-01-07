<?php

namespace Tests\Unit;

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use App\Ticket;
use Carbon\Carbon;
use Factories\ConcertFactory;
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

        $this->assertEquals('67.50', $concert->getTicketPriceInDollars());
    }

    /**
     * @test
     */
    public function concerts_with_a_published_at_date_are_published()
    {
        $publishedConcertA = ConcertFactory::createPublished();
        $publishedConcertB = ConcertFactory::createPublished();
        $publishedConcertC = ConcertFactory::createUnpublished();

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($publishedConcertC));
    }

    /**
     * @test
     */
    public function concerts_can_be_published()
    {
        $concert = ConcertFactory::createUnpublished(['ticket_quantity' => 5]);

        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->countRemainingTickets());

        $concert->publish();

        $this->assertTrue($concert->isPublished());
        $this->assertEquals(5, $concert->countRemainingTickets());
    }

    /**
     * @test
     */
    public function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => null]));

        $this->assertEquals(2, $concert->countRemainingTickets());
    }

    /**
     * @test
     */
    public function tickets_sold_only_includes_tickets_associated_with_an_order()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => null]));

        $this->assertEquals(3, $concert->countTicketsSold());
    }

    /**
     * @test
     */
    public function total_tickets_include_all_tickets()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => null]));

        $this->assertEquals(5, $concert->countTotalTickets());
    }

    /**
     * @test
     */
    public function calculating_the_percentage_of_ticketes_sold()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 5)->create(['order_id' => null]));

        $this->assertEquals(28.57, $concert->percentTicketsSold());
    }

    /**
     * @test
     */
    public function calculating_the_revenue_in_dollars()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create();

        /** @var Order $orderA */
        $orderA = factory(Order::class)->create(['amount' => 3850]);
        /** @var Order $orderB */
        $orderB = factory(Order::class)->create(['amount' => 9625]);

        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => $orderA->id]));
        $concert->tickets()->saveMany(factory(Ticket::class, 5)->create(['order_id' => $orderB->id]));

        $this->assertEquals(134.75, $concert->revenueInDollars());
    }

    /**
     * @test
     */
    public function can_reserve_available_tickets()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 3]);

        $this->assertEquals(3, $concert->countRemainingTickets());

        $concert->reserveTickets(2, 'foo@bar.com');

        $this->assertEquals(1, $concert->countRemainingTickets());
    }

    /**
     * @test
     */
    public function cannot_reserve_tickets_that_have_already_been_purchased()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 1)->create(['order_id' => null]));

        try {
            $concert->reserveTickets(2, 'foo@bar.com');
        } catch (\Exception $e) {
            $this->assertEquals(1, $concert->countRemainingTickets());

            return;
        }

        $this->fail('Reserving tickets succeeded even though the tickets were already sold.');
    }

    /**
     * @test
     */
    public function cannot_reserve_tickets_that_have_already_been_reserved()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 3]);

        $concert->reserveTickets(2, 'foo@bar.com');

        try {
            $concert->reserveTickets(2, 'baz@bar.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals(1, $concert->countRemainingTickets());

            return;
        }

        $this->fail('Reserving tickets succeeded even though the tickets were already reserved.');
    }

    /**
     * @test
     */
    public function cannot_reserve_more_tickets_than_are_available()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 10]);

        try {
            $concert->reserveTickets(11, 'foo@bar.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals(10, $concert->countRemainingTickets());

            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining.');
    }
}
