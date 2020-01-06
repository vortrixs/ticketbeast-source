<?php


namespace Tests\Feature\Backstage;


use App\Concert;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class AddConcertTest extends TestCase
{
    use DatabaseMigrations;

    public function from(string $uri) : self
    {
        session()->setPreviousUrl(url($uri));

        return $this;
    }

    private function validParams(array $override = []) : array
    {
        return array_merge([
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
        ], $override);
    }

    private function assertGivenDataWasInvalidFor(TestResponse $response, $field)
    {
        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors($field);
        $this->assertEquals(0, Concert::count());
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
        $this->assertEquals(0, Concert::count());
    }

    /**
     * @test
     */
    public function adding_a_valid_concert()
    {
        $this->withoutExceptionHandling();

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

        tap(Concert::first(), function (Concert $concert) use ($response, $user) {
            $response->assertStatus(302);
            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertTrue($concert->user()->first()->is($user));

            $this->assertTrue($concert->isPublished());

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
        $response = $this->post('/backstage/concerts', $this->validParams());

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
            ->post('/backstage/concerts', $this->validParams(['title' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'title');
    }

    /**
     * @test
     */
    public function subtitle_is_optional()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', $this->validParams(['subtitle' => '']));

        $this->assertEquals(1, Concert::count());

        tap(Concert::first(), function (Concert $concert) use ($response) {
            $response->assertStatus(302);
            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertNull($concert->subtitle);
        });
    }

    /**
     * @test
     */
    public function additional_information_is_optional()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->post('/backstage/concerts', $this->validParams(['additional_information' => '']));

        $this->assertEquals(1, Concert::count());

        tap(Concert::first(), function (Concert $concert) use ($response) {
            $response->assertStatus(302);
            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertNull($concert->additional_information);
        });
    }

    /**
     * @test
     */
    public function date_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['date' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'date');
    }

    /**
     * @test
     */
    public function date_must_be_a_valid_date()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['date' => 'not a date']));

        $this->assertGivenDataWasInvalidFor($response, 'date');
    }

    /**
     * @test
     */
    public function time_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['time' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'time');
    }

    /**
     * @test
     */
    public function time_must_be_a_valid_time()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['time' => 'not a time']));

        $this->assertGivenDataWasInvalidFor($response, 'time');
    }

    /**
     * @test
     */
    public function venue_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['venue' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'venue');
    }

    /**
     * @test
     */
    public function venue_address_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['venue_address' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'venue_address');
    }

    /**
     * @test
     */
    public function city_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['city' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'city');
    }

    /**
     * @test
     */
    public function state_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['state' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'state');
    }

    /**
     * @test
     */
    public function zip_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['zip' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'zip');
    }

    /**
     * @test
     */
    public function ticket_price_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['ticket_price' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'ticket_price');
    }

    /**
     * @test
     */
    public function ticket_price_must_be_numeric()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['ticket_price' => 'not a number']));

        $this->assertGivenDataWasInvalidFor($response, 'ticket_price');
    }

    /**
     * @test
     */
    public function ticket_price_must_be_at_least_5()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['ticket_price' => '4']));

        $this->assertGivenDataWasInvalidFor($response, 'ticket_price');
    }

    /**
     * @test
     */
    public function ticket_quantity_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['ticket_quantity' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'ticket_quantity');
    }

    /**
     * @test
     */
    public function ticket_quantity_must_be_an_integer()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['ticket_quantity' => '1.5']));

        $this->assertGivenDataWasInvalidFor($response, 'ticket_quantity');
    }

    /**
     * @test
     */
    public function ticket_quantity_must_be_at_least_1()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['ticket_quantity' => '0']));

        $this->assertGivenDataWasInvalidFor($response, 'ticket_quantity');
    }
}
