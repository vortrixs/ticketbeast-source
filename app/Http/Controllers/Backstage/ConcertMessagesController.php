<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;
use App\Jobs\SendAttendeeMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConcertMessagesController extends Controller
{
    public function create(int $concertId)
    {
        $concert = Auth::user()->concerts()->findOrFail($concertId);

        return view('backstage.concert_messages.new', ['concert' => $concert]);
    }

    public function store(Request $request, int $id)
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->findOrFail($id);

        $request->validate([
            'subject' => 'required',
            'message' => 'required',
        ]);

        $message = $concert->attendeeMessages()->create($request->only(['subject', 'message']));

        SendAttendeeMessage::dispatch($message);

        return redirect()->route('backstage.concert_messages.new', $concert)->with('flash', 'Your message has been sent.');
    }
}
