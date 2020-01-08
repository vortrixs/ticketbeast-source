<?php

namespace App\Jobs;

use App\Concert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;

class ProcessPosterImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Concert
     */
    private $concert;
    /**
     * @var Storage
     */
    private $storage;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Concert $concert, FilesystemAdapter $storage)
    {
        $this->concert = $concert;
        $this->storage = $storage;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $content = $this->storage->get($this->concert->poster_image_path);
        $image = Image::make($content)->widen(600)->limitColors(255)->encode();

        $this->storage->put($this->concert->poster_image_path, $image->getEncoded());
    }

    public function getConcert() : Concert
    {
        return $this->concert;
    }
}
