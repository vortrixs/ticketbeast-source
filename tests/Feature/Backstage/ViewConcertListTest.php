<?php

namespace Tests\Feature\Backstage;

use App\User;
use Factories\ConcertFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ViewConcertListTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function guests_cannot_view_a_promoters_concert_list()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * @test
     */
    public function promoters_can_only_view_a_list_of_their_own_concerts()
    {
        $this->withoutExceptionHandling();

        $userA = factory(User::class)->create();
        $userB = factory(User::class)->create();

        $publishedConcertA = ConcertFactory::createPublished(['user_id' => $userA->id]);
        $publishedConcertC = ConcertFactory::createPublished(['user_id' => $userA->id]);

        $unpublishedConcertA = ConcertFactory::createUnpublished(['user_id' => $userA->id]);
        $unpublishedConcertC = ConcertFactory::createUnpublished(['user_id' => $userA->id]);

        ConcertFactory::createPublished(['user_id' => $userB->id]);
        ConcertFactory::createUnpublished(['user_id' => $userB->id]);


        $response = $this->actingAs($userA)->get('/backstage/concerts');

        $response->assertStatus(200);

        $response->data('published_concerts')->assertEquals([
            $publishedConcertA,
            $publishedConcertC
        ]);

        $response->data('unpublished_concerts')->assertEquals([
            $unpublishedConcertA,
            $unpublishedConcertC
        ]);
    }
}
