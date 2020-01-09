<?php

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('invite-promoter {email}', function (string $email) {
    \App\Invitation::create([
        'code' => \App\Facades\InvitationCode::generate(),
        'email' => $email,
    ])->send();
})->describe('Invite a new promoter to create an account');
