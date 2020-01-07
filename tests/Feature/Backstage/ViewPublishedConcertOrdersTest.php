<?php


namespace Tests\Feature\Backstage;


use App\User;
use Factories\ConcertFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ViewPublishedConcertOrdersTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function a_promoter_can_view_the_orders_of_their_own_published_concert()
    {
        $this->withoutExceptionHandling();

        /** @var User $user */
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.published_concert_orders.index');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /**
     * @test
     */
    public function a_promoter_cannot_view_the_orders_of_unpublished_concerts()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function a_promoter_cannot_view_the_orders_of_another_promoters_published_concert()
    {
        /** @var User $userA */
        $userA = factory(User::class)->create();
        /** @var User $userB */
        $userB = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $userB->id]);

        $response = $this->actingAs($userA)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function a_guest_cannot_view_the_orders_of_any_published_concert()
    {
        $concert = ConcertFactory::createPublished();

        $response = $this->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertRedirect('/login');
    }
}
