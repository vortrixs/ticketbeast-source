<?php


namespace Tests\Browser;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PromoterLoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function logging_in_successfully()
    {
        factory(User::class)->create([
            'email' => 'foo@bar.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'foo@bar.com')
                ->type('password', 'password')
                ->press('Log in')
                ->assertPathIs('/backstage/concerts');
        });
    }

    /**
     * @test
     */
    public function logging_in_with_invalid_credentials()
    {
        factory(User::class)->create([
            'email' => 'foo@bar.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'foo@bar.com')
                ->type('password', 'wrong-password')
                ->press('Log in')
                ->assertPathIs('/login')
                ->assertSee('credentials do not match');
        });
    }

    /**
     * @test
     */
    public function logging_out_the_current_user()
    {
        Auth::login(factory(User::class)->create());

        $response = $this->post('/logout');

        $response->assertRedirect('login');
        $this->assertFalse(Auth::check());
    }
}
