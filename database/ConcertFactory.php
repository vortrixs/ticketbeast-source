<?php

namespace Factories;

use App\Concert;

class ConcertFactory
{
    public static function createPublished(array $overrides = []) : Concert
    {
        return factory(Concert::class)->create($overrides)->publish();
    }

    public static function createUnpublished(array $overrides = []) : Concert
    {
        return factory(Concert::class)->state('unpublished')->create($overrides);
    }
}
