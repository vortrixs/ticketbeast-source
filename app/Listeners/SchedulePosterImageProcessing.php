<?php

namespace App\Listeners;

use App\Events\ConcertAdded;
use App\Jobs\ProcessPosterImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SchedulePosterImageProcessing
{
    public function handle(ConcertAdded $event)
    {
        $concert = $event->getConcert();

        if ($concert->hasPoster()) {
            ProcessPosterImage::dispatch($concert, $event->getStorage());
        }
    }
}
