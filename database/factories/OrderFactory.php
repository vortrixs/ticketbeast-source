<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Order;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'amount' => 5250,
        'email' => 'fake@mail.com',
        'confirmation_number' => 'ORDER_CONFIRMATION_NUMBER_1234',
        'card_last_four' => '1234',
    ];
});
