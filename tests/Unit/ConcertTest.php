<?php

namespace Tests\Unit;

use App\Concert;
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
}
