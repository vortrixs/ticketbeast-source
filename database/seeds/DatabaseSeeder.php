<?php

use App\Concert;
use Carbon\Carbon;
use Factories\ConcertFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        ConcertFactory::createPublished([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'date' => Carbon::parse('January 12, 2020 8:00PM'),
            'ticket_price' => 3250,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17916',
            'additional_information' => 'For tickets, call (555) 555-5555.',
            'ticket_quantity' => 10,
        ]);

        factory(\App\User::class)->create([
            'email' => 'hanserik@sitetech.dk',
            'password' => 'admin',
        ]);
    }
}
