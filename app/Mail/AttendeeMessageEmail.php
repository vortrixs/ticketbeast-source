<?php

namespace App\Mail;

use App\AttendeeMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AttendeeMessageEmail extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * @var AttendeeMessage
     */
    private $attendeeMessage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(AttendeeMessage $attendeeMessage)
    {
        $this->attendeeMessage = $attendeeMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->attendeeMessage->subject)
            ->text('emails.attendee_message_email', [
                'attendee_message' => $this->attendeeMessage
            ]);
    }

    public function hasMessage(AttendeeMessage $message) : bool
    {
        return $this->attendeeMessage->is($message);
    }
}
