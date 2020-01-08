<?php

namespace Tests\Feature;

use App\Invitation;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function viewing_an_unused_invitation()
    {
        $invitation = factory(Invitation::class)->create([
            'code' => 'TEST_CODE_1234',
            'user_id' => null,
        ]);

        $response = $this->get('/invitation/TEST_CODE_1234');

        $response->assertStatus(200);
        $response->assertViewIs('invitation.show');
        $this->assertTrue($response->data('invitation')->is($invitation));
    }

    /**
     * @test
     */
    public function viewing_a_used_invitation()
    {
        factory(Invitation::class)->create([
            'code' => 'TEST_CODE_1234',
            'user_id' => factory(User::class)->create()->id
        ]);

        $response = $this->get('/invitation/TEST_CODE_1234');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function viewing_an_invitation_that_does_not_exist()
    {
        $response = $this->get('/invitation/TEST_CODE_9999');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function registering_with_a_valid_invitation_code()
    {
        $this->withoutExceptionHandling();

        /** @var Invitation $invitation */
        $invitation = factory(Invitation::class)->create([
            'code' => 'TEST_CODE_1234',
            'user_id' => null,
        ]);

        $response = $this->post('/register', [
            'email' => 'foo@bar.com',
            'password' => 'password',
            'code' => 'TEST_CODE_1234',
        ]);

        $response->assertRedirect('/backstage/concerts');
        $this->assertEquals(1, User::count());

        $user = User::first();

        $this->assertAuthenticatedAs($user);

        $invitation = $invitation->fresh();

        $this->assertEquals('foo@bar.com', $user->email);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertTrue($invitation->user()->first()->is($user));
        $this->assertTrue($invitation->hasBeenUsed());
    }
}
