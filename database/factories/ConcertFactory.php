<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Concert;
use App\User;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Concert::class, function (Faker $faker) {
    return [
        'title' => 'Example Band',
        'subtitle' => 'with The Fake Openers',
        'date' => Carbon::parse('+2 weeks'),
        'ticket_price' => 2000,
        'venue' => 'The Example Theatre',
        'venue_address' => '123 Example Lane',
        'city' => 'Fakeville',
        'state' => 'ON',
        'zip' => '90210',
        'additional_information' => 'Some sample additional information.',
        'ticket_quantity' => 5,
        'user_id' => function () { return factory(User::class)->create()->id; },
    ];
});

$factory->state(Concert::class, 'published', function (Faker $faker) {
    return ['published_at' => Carbon::parse('-1 week')];
});

$factory->state(Concert::class, 'unpublished', function (Faker $faker) {
    return ['published_at' => null];
});
