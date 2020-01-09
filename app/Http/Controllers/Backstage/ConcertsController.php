<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Events\ConcertAdded;
use App\Http\Controllers\Controller;
use App\NullObject;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use phpDocumentor\Reflection\Types\Nullable;

/**
 * Class ConcertsController
 */
class ConcertsController extends Controller
{
    public function create() : View
    {
        return view('backstage.concerts.create');
    }

    public function store()
    {
        $this->validate(request(), [
            'title' => 'required',
            'date' => 'required|date',
            'time' => 'required|date_format:g:iA',
            'venue' => 'required',
            'venue_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'ticket_price' => 'required|numeric|min:5',
            'ticket_quantity' => 'required|integer|min:1',
            'poster_image' => 'nullable|image|dimensions:min_width=600,ratio=8.5/11',
        ]);

        $concert = Auth::user()->concerts()->create([
            'title' => request('title'),
            'subtitle' => request('subtitle'),
            'date' => Carbon::parse(vsprintf('%s %s', [request('date'), request('time')])),
            'ticket_price' => request('ticket_price')*100,
            'venue' => request('venue'),
            'venue_address' => request('venue_address'),
            'city' => request('city'),
            'state' => request('state'),
            'zip' => request('zip'),
            'additional_information' => request('additional_information'),
            'ticket_quantity' => request('ticket_quantity'),
            'poster_image_path' => request('poster_image', new  NullObject)->store('posters', config('filesystems.default')),
        ]);

        ConcertAdded::dispatch($concert, Storage::disk(config('filesystems.default')));

        return redirect()->route('backstage.concerts.index');
    }

    public function index()
    {
        return view('backstage.concerts.index', [
            'concerts' => Auth::user()->concerts()->get(),
            'published_concerts' => Auth::user()->concerts()->get()->filter->isPublished(),
            'unpublished_concerts' => Auth::user()->concerts()->get()->reject->isPublished(),
        ]);
    }

    public function edit(int $concertId)
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->findOrFail($concertId);

        abort_if($concert->isPublished(), Response::HTTP_FORBIDDEN);

        return view('backstage.concerts.edit', ['concert' => $concert]);
    }

    public function update(int $concertId)
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->findOrFail($concertId);

        abort_if($concert->isPublished(), Response::HTTP_FORBIDDEN);

        $this->validate(request(), [
            'title' => 'required',
            'date' => 'required|date',
            'time' => 'required|date_format:g:iA',
            'venue' => 'required',
            'venue_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'ticket_price' => 'required|numeric|min:5',
            'ticket_quantity' => 'required|integer|min:1',
        ]);

        $concert->update([
            'title' => request('title'),
            'subtitle' => request('subtitle'),
            'date' => Carbon::parse(vsprintf('%s %s', [request('date'), request('time')])),
            'ticket_price' => request('ticket_price')*100,
            'venue' => request('venue'),
            'venue_address' => request('venue_address'),
            'city' => request('city'),
            'state' => request('state'),
            'zip' => request('zip'),
            'additional_information' => request('additional_information'),
            'ticket_quantity' => request('ticket_quantity'),
        ]);

        return redirect()->route('backstage.concerts.index');
    }
}
