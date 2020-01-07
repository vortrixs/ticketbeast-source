<?php


namespace Tests\Feature\Backstage;


use App\Concert;
use App\User;
use Factories\ConcertFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PublishConcertTest extends TestCase
{
    use DatabaseMigrations;

    const POST_URI = 'backstage/published-concerts';

    /**
     * @test
     */
    public function a_promoter_can_publish_their_own_concert()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $concert = ConcertFactory::createUnpublished([
            'user_id' => $user->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post($this::POST_URI, [
            'concert_id' => $concert->id
        ]);

        $concert = $concert->fresh();

        $response->assertRedirect('/backstage/concerts');
        $this->assertTrue($concert->isPublished());
        $this->assertEquals(3, $concert->countRemainingTickets());
    }

    /**
     * @test
     */
    public function a_concert_can_only_be_published_once()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post($this::POST_URI, [
            'concert_id' => $concert->id
        ]);

        $concert = $concert->fresh();

        $response->assertStatus(422);
        $this->assertEquals(3, $concert->countRemainingTickets());
    }

    /**
     * @test
     */
    public function a_promoter_cannot_publish_other_concerts()
    {
        /** @var User $userA */
        $userA = factory(User::class)->create();
        /** @var User $userB */
        $userB = factory(User::class)->create();

        $concert = ConcertFactory::createUnpublished([
            'user_id' =>  $userB->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($userA)->post($this::POST_URI, [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(404);

        $concert = $concert->fresh();
        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->countRemainingTickets());
    }

    /**
     * @test
     */
    public function a_guest_cannot_publish_concerts()
    {
        $concert = ConcertFactory::createUnpublished([
            'ticket_quantity' => 3,
        ]);

        $response = $this->post($this::POST_URI, [
            'concert_id' => $concert->id,
        ]);

        $response->assertRedirect('/login');

        $concert = $concert->fresh();
        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->countRemainingTickets());
    }

    /**
     * @test
     */
    public function concerts_that_do_not_exist_cannot_be_published()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post($this::POST_URI, [
            'concert_id' => 999
        ]);

        $response->assertStatus(404);
    }
}
