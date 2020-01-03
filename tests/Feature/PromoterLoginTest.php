<?php


namespace Tests\Feature;


use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PromoterLoginTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function logging_in_with_valid_credentials()
    {
        $user = factory(User::class)->create([
            'email' => 'foo@bar.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'foo@bar.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/backstage/concerts');
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->is($user));
    }

    /**
     * @test
     */
    public function logging_in_with_invalid_credentials()
    {
        factory(User::class)->create([
            'email' => 'foo@bar.com',
            'password' => bcrypt('wrong-password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'foo@bar.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());
    }

    /**
     * @test
     */
    public function logging_in_with_an_account_that_does_not_exist()
    {
        $response = $this->post('/login', [
            'email' => 'foo@bar.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());
    }
}
