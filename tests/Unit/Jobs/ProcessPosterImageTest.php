<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessPosterImage;
use Factories\ConcertFactory;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessPosterImageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_resizes_the_poster_image_to_600px_wide()
    {
        $disk = Storage::fake('public');
        $posterPath = 'posters/example-poster.png';

        $disk->put(
            $posterPath,
            file_get_contents(base_path('tests/__fixtures__/full-size-poster.png'))
        );

        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => $posterPath
        ]);

        ProcessPosterImage::dispatch($concert, $disk);

        $resizedImage = $disk->get($posterPath);

        list($width, $height) = getimagesizefromstring($resizedImage);

        $this->assertEquals(600, $width);
        $this->assertEquals(776, $height);
    }

    /**
     * @test
     */
    public function it_optimizes_the_poster_image()
    {
        $disk = Storage::fake('public');
        $posterPath = 'posters/example-poster.png';

        $disk->put(
            $posterPath,
            file_get_contents(base_path('tests/__fixtures__/small-unoptimized-poster.png'))
        );

        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => $posterPath
        ]);

        ProcessPosterImage::dispatch($concert, $disk);

        $originalSize = filesize(base_path('tests/__fixtures__/small-unoptimized-poster.png'));
        $optimizedImageSize = $disk->size($posterPath);

        $this->assertLessThan($originalSize, $optimizedImageSize);

        $optimizedImageContents = $disk->get($posterPath);
        $controlImageContents = file_get_contents(base_path('tests/__fixtures__/optimized-poster.png'));

        $this->assertEquals($controlImageContents, $optimizedImageContents);
    }
}
