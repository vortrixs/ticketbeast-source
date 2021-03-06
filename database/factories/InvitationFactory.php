<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Invitation;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Invitation::class, function (Faker $faker) {
    return [
        'code' => 'TEST_CODE_1234',
        'email' => 'foo@bar.com',
    ];
});
