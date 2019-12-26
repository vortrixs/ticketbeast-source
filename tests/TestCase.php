<?php

namespace Tests;

use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

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
}
