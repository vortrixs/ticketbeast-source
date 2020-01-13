<?php

namespace Tests\Unit\Mail;

use App\Invitation;
use App\Mail\InvitationEmail;
use Tests\TestCase;

class InvitationEmailTest extends TestCase
{
    /**
     * @test
     */
    public function email_contains_a_link_to_accept_the_invitation()
    {
        $invitation = factory(Invitation::class)->make([
            'email' => 'foo@bar.com',
            'code' => 'TEST_CODE_1234',
        ]);

        $email = new InvitationEmail($invitation);

        $this->assertStringContainsString(url('/invitation/TEST_CODE_1234'), $email->render());
    }

    /**
     * @test
     */
    public function email_has_the_correct_subject()
    {
        $invitation = factory(Invitation::class)->make();

        $email = new InvitationEmail($invitation);

        $this->assertEquals('You\'re invited to join TicketBeast!', $email->build()->subject);
    }
}