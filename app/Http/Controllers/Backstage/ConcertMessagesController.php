<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;
use App\Jobs\SendAttendeeMessage;
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
        $concert = Auth::user()->concerts()->findOrFail($concertId);

        $this->validate(request(), [
            'subject' => 'required',
            'message' => 'required',
        ]);

        $message = $concert->attendeeMessages()->create(request(['subject', 'message']));

        SendAttendeeMessage::dispatch($message);

        return redirect()->route('backstage.concert_messages.new', $concert)->with('flash', 'Your message has been sent.');
    }
}
