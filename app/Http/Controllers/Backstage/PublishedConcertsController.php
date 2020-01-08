<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PublishedConcertsController extends Controller
{
    public function store()
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->findOrFail(request('concert_id'));

        abort_if($concert->isPublished(), Response::HTTP_UNPROCESSABLE_ENTITY);

        $concert->publish();

        return redirect()->route('backstage.concerts.index');
    }
}
