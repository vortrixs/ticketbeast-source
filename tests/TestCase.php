<?php

namespace Tests;

use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;

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
