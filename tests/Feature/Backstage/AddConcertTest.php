<?php


namespace Tests\Feature\Backstage;


use App\Concert;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AddConcertTest extends TestCase
{
    use DatabaseMigrations;

    public function from(string $uri) : self
    {
        session()->setPreviousUrl(url($uri));

        return $this;
    }

    /**
     * @test
     */
    public function promoters_can_view_the_add_concert_form()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function guests_cannot_view_the_add_concert_form()
    {
        $response = $this->get('/backstage/concerts/new');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * @test
     */
    public function adding_a_valid_concert()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', [
           'title' => 'No Warning',
           'subtitle' => 'with Cruel Hand and Backtrack',
           'additional_information' => 'You must be 19 years of age to attend this concert',
           'date' => '2020-11-18',
           'time' => '8:00PM',
           'venue' => 'The Mosh Pit',
           'venue_address' => '123 Fake St.',
           'city' => 'Laraville',
           'state' => 'ON',
           'zip' => '12345',
           'ticket_price' => '32.50',
           'ticket_quantity' => '75',
        ]);

        tap(Concert::first(), function (Concert $concert) use ($response) {
            $response->assertStatus(302);
            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertEquals('No Warning', $concert->title);
            $this->assertEquals('with Cruel Hand and Backtrack', $concert->subtitle);
            $this->assertEquals('You must be 19 years of age to attend this concert', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2020-11-18 8:00PM'), $concert->date);
            $this->assertEquals('The Mosh Pit', $concert->venue);
            $this->assertEquals('123 Fake St.', $concert->venue_address);
            $this->assertEquals('Laraville', $concert->city);
            $this->assertEquals('ON', $concert->state);
            $this->assertEquals('12345', $concert->zip);
            $this->assertEquals(3250, $concert->ticket_price);
            $this->assertEquals(75, $concert->countRemainingTickets());
        });
    }

    /**
     * @test
     */
    public function guests_cannot_add_new_concerts()
    {
        $response = $this->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert',
            'date' => '2020-11-18',
            'time' => '8:00PM',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertEquals(0, Concert::count());
    }

    /**
     * @test
     */
    public function title_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', [
                'title' => '',
                'subtitle' => 'with Cruel Hand and Backtrack',
                'additional_information' => 'You must be 19 years of age to attend this concert',
                'date' => '2020-11-18',
                'time' => '8:00PM',
                'venue' => 'The Mosh Pit',
                'venue_address' => '123 Fake St.',
                'city' => 'Laraville',
                'state' => 'ON',
                'zip' => '12345',
                'ticket_price' => '32.50',
                'ticket_quantity' => '75',
            ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('title');
        $this->assertEquals(0, Concert::count());
    }

    /**
     * @test
     */
    public function subtitle_is_optional()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => '',
            'additional_information' => 'You must be 19 years of age to attend this concert',
            'date' => '2020-11-18',
            'time' => '8:00PM',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        tap(Concert::first(), function (Concert $concert) use ($response) {
            $response->assertStatus(302);
            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertEquals('No Warning', $concert->title);
            $this->assertNull($concert->subtitle);
            $this->assertEquals('You must be 19 years of age to attend this concert', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2020-11-18 8:00PM'), $concert->date);
            $this->assertEquals('The Mosh Pit', $concert->venue);
            $this->assertEquals('123 Fake St.', $concert->venue_address);
            $this->assertEquals('Laraville', $concert->city);
            $this->assertEquals('ON', $concert->state);
            $this->assertEquals('12345', $concert->zip);
            $this->assertEquals(3250, $concert->ticket_price);
            $this->assertEquals(75, $concert->countRemainingTickets());
        });
    }
}
