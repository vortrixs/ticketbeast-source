<?php

namespace App\Http\Controllers;

use App\Concert;
use Illuminate\View\View;

/**
 * Class ConcertsController
 */
class ConcertsController extends Controller
{
    /**
     * @param int $id
     *
     * @return View
     */
    public function show(int $id) : View
    {
         $concert = Concert::published()->findOrFail($id);

        return view('concerts.show', ['concert' => $concert]);
    }
}
