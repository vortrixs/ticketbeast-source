<?php


namespace App\Http\Controllers\Backstage;


use App\Concert;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PublishedConcertOrdersController extends Controller
{
    public function index($concertId)
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->published()->findOrFail($concertId);

        return view('backstage.published_concert_orders.index', [
            'concert' => $concert,
            'orders' => $concert->orders()->latest()->take(10)->get()
        ]);
    }
}
