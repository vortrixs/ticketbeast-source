<?php

namespace Tests\Feature\Backstage;

use App\AttendeeMessage;
use App\User;
use Factories\ConcertFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class MessageAttendeesTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function a_promoter_can_view_the_message_form_for_their_own_concert()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.concert_messages.new');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function a_promoter_cannot_view_the_message_form_for_another_promoters_concert()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => factory(User::class)->create()->id,
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(404);
    }

    /** @test */
    function a_guest_cannot_view_the_message_form_for_any_concert()
    {
        $concert = ConcertFactory::createPublished();

        $response = $this->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertRedirect('/login');
    }

    /**
     * @test
     */
    public function a_promoter_can_send_a_new_message()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'Some Subject',
            'message' => 'Some Message',
        ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHas('flash');

        $message = AttendeeMessage::first();

        $this->assertEquals($concert->id, $message->concert_id);
        $this->assertEquals('Some Subject', $message->subject);
        $this->assertEquals('Some Message', $message->message);
    }
}
