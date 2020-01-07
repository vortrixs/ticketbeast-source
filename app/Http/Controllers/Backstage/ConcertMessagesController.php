<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConcertMessagesController extends Controller
{
    public function create(int $concertId)
    {
        $concert = Auth::user()->concerts()->findOrFail($concertId);

        return view('backstage.concert_messages.new', ['concert' => $concert]);
    }

    public function store(int $concertId)
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->find($concertId);

        $concert->attendeeMessages()->create(request(['subject', 'message']));

        return redirect()->route('backstage.concert_messages.new', $concert)->with('flash', 'Your message has been sent.');
    }
}
