<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PublishedConcertsController extends Controller
{
    public function store(Request $request)
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->findOrFail($request->get('concert_id'));

        abort_if($concert->isPublished(), Response::HTTP_UNPROCESSABLE_ENTITY);

        $concert->publish();

        return redirect()->route('backstage.concerts.index');
    }
}
