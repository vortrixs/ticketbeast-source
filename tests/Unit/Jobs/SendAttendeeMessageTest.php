<?php


namespace Tests\Unit\Jobs;

use App\AttendeeMessage;
use App\Jobs\SendAttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Factories\ConcertFactory;
use Factories\OrderFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendAttendeeMessageTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function it_sends_the_message_to_all_concert_attendees()
    {
        Mail::fake();
        $concertA = ConcertFactory::createPublished();
        $concertB = ConcertFactory::createPublished();
        $message = AttendeeMessage::create([
            'concert_id' => $concertA->id,
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        OrderFactory::createForConcert($concertA, ['email' => 'foo@bar.com']);
        OrderFactory::createForConcert($concertA, ['email' => 'baz@bar.com']);
        OrderFactory::createForConcert($concertA, ['email' => 'qux@bar.com']);
        OrderFactory::createForConcert($concertB, ['email' => 'quz@bar.com']);

        SendAttendeeMessage::dispatch($message);

        Mail::assertQueued(AttendeeMessageEmail::class, function (AttendeeMessageEmail $mailable) use ($message) {
            return $mailable->hasTo('foo@bar.com') && $mailable->hasMessage($message);
        });

        Mail::assertQueued(AttendeeMessageEmail::class, function (AttendeeMessageEmail $mailable) use ($message) {
            return $mailable->hasTo('baz@bar.com') && $mailable->hasMessage($message);
        });

        Mail::assertQueued(AttendeeMessageEmail::class, function (AttendeeMessageEmail $mailable) use ($message) {
            return $mailable->hasTo('qux@bar.com') && $mailable->hasMessage($message);
        });

        Mail::assertNotQueued(AttendeeMessageEmail::class, function (AttendeeMessageEmail $mailable) use ($message) {
            return $mailable->hasTo('quz@bar.com');
        });
    }
}
