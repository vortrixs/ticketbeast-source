<?php

namespace Tests\Feature;

use App\Facades\InvitationCode;
use App\Invitation;
use App\Mail\InvitationEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvitePromoterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function inviting_a_promoter_via_the_cli()
    {
        Mail::fake();
        InvitationCode::shouldReceive('generate')->andReturn('TEST_CODE_1234');
        $this->artisan('invite-promoter', ['email' => 'foo@bar.com']);

        $this->assertEquals(1, Invitation::count());

        $invitation = Invitation::first();

        $this->assertEquals('foo@bar.com', $invitation->email);
        $this->assertEquals('TEST_CODE_1234', $invitation->code);

        Mail::assertSent(InvitationEmail::class, function (InvitationEmail $mailable) use ($invitation) {
            return $mailable->hasTo('foo@bar.com') && $mailable->hasInvitation($invitation);
        });
    }
}
