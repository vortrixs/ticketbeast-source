<?php


namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class EditConcertTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Predefined old data
     *
     * @param array $override
     * @return array
     */
    private function oldAttributes(array $override = []) : array
    {
        return array_merge([
            'title' => 'Old Title',
            'subtitle' => 'Old Subtitle',
            'date' => Carbon::parse('2017-01-01 5:00PM'),
            'ticket_price' => 2000,
            'venue' => 'Old Venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'additional_information' => 'Old additional information',
            'ticket_quantity' => 5,
        ], $override);
    }

    /**
     * Predefined new data
     *
     * @param array $override
     * @return array
     */
    private function validParams(array $override = []) : array
    {
        return array_merge([
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'date' => '2020-12-12',
            'time' => '8:00PM',
            'ticket_price' => '72.50',
            'venue' => 'New Venue',
            'venue_address' => 'New address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99999',
            'additional_information' => 'New additional information',
            'ticket_quantity' => 10,
        ], $override);
    }

    private function assertGivenDataWasInvalidFor(TestResponse $response, string $field, int $concertId)
    {
        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concertId}/edit");
        $response->assertSessionHasErrors($field);
    }

    private function assertFreshConcertFieldEquals(Concert $concert, string $field, string $equals)
    {
        tap($concert->fresh(), function (Concert $concert) use ($field, $equals) {
            $this->assertEquals($equals, $concert->{$field});
        });
    }

    /**
     * @test
     */
    public function promoters_can_view_the_edit_form_for_their_own_unpublished_concerts()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /**
     * @test
     */
    public function promoters_cannot_view_the_edit_form_for_their_own_published_concerts()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create(['user_id' => $user->id]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function promoters_cannot_view_the_edit_form_for_other_concerts()
    {
        /** @var User $userA */
        $userA = factory(User::class)->create();
        /** @var User $userB */
        $userB = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create(['user_id' => $userB->id]);

        $response = $this->actingAs($userA)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function promoters_see_a_404_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get("/backstage/concerts/999/edit");

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_any_concert()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * @test
     */
    public function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
    {
        $response = $this->get("/backstage/concerts/999/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * @test
     */
    public function promoters_can_edit_their_own_unpublished_concerts()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'subtitle' => 'Old Subtitle',
            'date' => Carbon::parse('2017-01-01 5:00PM'),
            'ticket_price' => 2000,
            'venue' => 'Old Venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'additional_information' => 'Old additional information',
            'ticket_quantity' => 5,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'date' => '2020-12-12',
            'time' => '8:00PM',
            'ticket_price' => '72.50',
            'venue' => 'New Venue',
            'venue_address' => 'New address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99999',
            'additional_information' => 'New additional information',
            'ticket_quantity' => '10',
        ]);

        $response->assertRedirect('/backstage/concerts');

        tap($concert->fresh(), function (Concert $concert) {
            $this->assertEquals('New Title', $concert->title);
            $this->assertEquals('New Subtitle', $concert->subtitle);
            $this->assertEquals(Carbon::parse('2020-12-12 8:00PM'), $concert->date);
            $this->assertEquals(7250, $concert->ticket_price);
            $this->assertEquals('New Venue', $concert->venue);
            $this->assertEquals('New address', $concert->venue_address);
            $this->assertEquals('New city', $concert->city);
            $this->assertEquals('New state', $concert->state);
            $this->assertEquals('99999', $concert->zip);
            $this->assertEquals('New additional information', $concert->additional_information);
            $this->assertEquals(10, $concert->ticket_quantity);
        });
    }

    /**
     * @test
     */
    public function promoters_cannot_edit_other_unpublished_concerts()
    {
        /** @var User $userA */
        $userA = factory(User::class)->create();
        /** @var User $userB */
        $userB = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $userB->id,
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($userA)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(404);

        $this->assertArraySubset($this->oldAttributes(['user_id' => $userB->id]), $concert->fresh()->getAttributes());
    }

    /**
     * @test
     */
    public function promoters_cannot_edit_published_concerts()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(403);

        $this->assertArraySubset($this->oldAttributes(['user_id' => $user->id]), $concert->fresh()->getAttributes());
    }

    /**
     * @test
     */
    public function guests_cannot_edit_concerts()
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create($this->oldAttributes());

        $this->assertFalse($concert->isPublished());

        $response = $this->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $this->assertArraySubset($this->oldAttributes(), $concert->fresh()->getAttributes());
    }

    /**
     * @test
     */
    public function title_is_required()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['title' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'title', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'title', 'Old Title');
    }

    /**
     * @test
     */
    public function subtitle_is_optional()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'subtitle' => 'Old Subtitle',
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['subtitle' => '']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts");

        tap($concert->fresh(), function (Concert $concert) {
            $this->assertNull($concert->subtitle);
        });
    }

    /**
     * @test
     */
    public function additional_information_is_optional()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'additional_information' => 'Old additional information',
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['additional_information' => '']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts");

        tap($concert->fresh(), function (Concert $concert) {
            $this->assertNull($concert->additional_information);
        });
    }

    /**
     * @test
     */
    public function date_is_required()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2020-01-01 8:00PM'),
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['date' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'date', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'date', Carbon::parse('2020-01-01 8:00PM'));
    }

    /**
     * @test
     */
    public function date_must_be_a_valid_date()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2020-01-01 8:00PM'),
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['date' => 'not a date']));

        $this->assertGivenDataWasInvalidFor($response, 'date', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'date', Carbon::parse('2020-01-01 8:00PM'));
    }

    /**
     * @test
     */
    public function time_is_required()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2020-01-01 8:00PM'),
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['time' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'time', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'date', Carbon::parse('2020-01-01 8:00PM'));
    }

    /**
     * @test
     */
    public function time_must_be_a_valid_time()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2020-01-01 8:00PM'),
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['time' => 'not a time']));

        $this->assertGivenDataWasInvalidFor($response, 'time', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'date', Carbon::parse('2020-01-01 8:00PM'));
    }

    /**
     * @test
     */
    public function venue_is_required()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'venue' => 'Old Venue',
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['venue' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'venue', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'venue', 'Old Venue');
    }

    /**
     * @test
     */
    public function venue_address_is_required()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'venue_address' => 'Old Venue Address',
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['venue_address' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'venue_address', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'venue_address', 'Old Venue Address');
    }

    /**
     * @test
     */
    public function city_is_required()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'city' => 'Old City',
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['city' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'city', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'city', 'Old City');
    }

    /**
     * @test
     */
    public function state_is_required()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'state' => 'Old State',
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['state' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'state', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'state', 'Old State');
    }

    /**
     * @test
     */
    public function zip_is_required()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'zip' => 'Old Zip',
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['zip' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'zip', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'zip', 'Old Zip');
    }

    /**
     * @test
     */
    public function ticket_price_is_required()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_price' => 5250,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['ticket_price' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'ticket_price', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'ticket_price', 5250);
    }

    /**
     * @test
     */
    public function ticket_price_must_be_numeric()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_price' => 5250,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['ticket_price' => 'not a price']));

        $this->assertGivenDataWasInvalidFor($response, 'ticket_price', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'ticket_price', 5250);
    }

    /**
     * @test
     */
    public function ticket_price_must_be_at_least_5()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_price' => 5250,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['ticket_price' => '4.99']));

        $this->assertGivenDataWasInvalidFor($response, 'ticket_price', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'ticket_price', 5250);
    }

    /**
     * @test
     */
    public function ticket_quantity_is_required()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['ticket_quantity' => '']));

        $this->assertGivenDataWasInvalidFor($response, 'ticket_quantity', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'ticket_quantity', 5);
    }

    /**
     * @test
     */
    public function ticket_quantity_must_be_an_integer()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['ticket_quantity' => '7.8']));

        $this->assertGivenDataWasInvalidFor($response, 'ticket_quantity', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'ticket_quantity', 5);
    }

    /**
     * @test
     */
    public function ticket_quantity_must_be_at_least_1()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['ticket_quantity' => '0']));

        $this->assertGivenDataWasInvalidFor($response, 'ticket_quantity', $concert->id);
        $this->assertFreshConcertFieldEquals($concert, 'ticket_quantity', 5);
    }
}
