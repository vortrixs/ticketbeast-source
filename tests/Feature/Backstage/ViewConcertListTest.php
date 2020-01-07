<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Factories\ConcertFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class ViewConcertListTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::macro('assertContains', function ($value) {
            Assert::assertTrue(
                $this->contains($value),
                'Failed asserting that the collection contained the specified value.'
            );
        });

        Collection::macro('assertNotContains', function ($value) {
            Assert::assertFalse(
                $this->contains($value),
                'Failed asserting that the collection did not contain the specified value.'
            );
        });

        Collection::macro('assertEquals', function ($items) {
            Assert::assertCount(count($this), $items);
            $this->zip($items)->each(function ($pair) {
                list($a, $b) = $pair;
                Assert::assertTrue($a->is($b));
            });
        });
    }

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
