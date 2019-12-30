<?php


namespace App;

use Illuminate\Support\Str;

class RandomOrderConfirmationNumberGenerator implements IConfirmationNumberGenerator
{
    public function generate(): string
    {
        $pool = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        return substr(str_shuffle(str_repeat($pool, 24)), 0, 24);
    }
}
