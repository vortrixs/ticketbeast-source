<?php

namespace Tests;

use App\Concert;
use App\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;
use PHPUnit\Framework\Assert;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        \Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

        TestResponse::macro('data', function (string $key) {
            return $this->getOriginalContent()->getData()[$key];
        });

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

    protected function assertValidationErrors(TestResponse $response, string $field) : void
    {
        $response->assertStatus(422)->assertJsonValidationErrors($field);
    }

    protected function assertOrderExistsFor(Concert $concert, string $email, ?Order &$order = null) : void
    {
        $order = $concert->orders()->where('email', $email)->first();

        $this->assertNotNull($order);
    }

    protected function assertOrderDoesntExistFor(Concert $concert, string $email, ?Order &$order = null) : void
    {
        $order = $concert->orders()->where('email', $email)->first();

        $this->assertNull($order);
    }

    public static function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        array_walk($subset, function ($value, $key) use ($array) {
            self::assertArrayHasKey($key, $array);
            self::assertEquals($value, $array[$key]);
        });
    }
}
