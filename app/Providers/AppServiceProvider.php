<?php

namespace App\Providers;

use App\Billing\IPaymentGateway;
use App\Billing\StripePaymentGateway;
use App\HashidsTicketCodeGenerator;
use App\IConfirmationNumberGenerator;
use App\ITicketCodeGenerator;
use App\RandomOrderConfirmationNumberGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(StripePaymentGateway::class, function () {

            return new StripePaymentGateway(config('services.stripe.secret'));
        });

        $this->app->bind(HashidsTicketCodeGenerator::class, function () {

            return new HashidsTicketCodeGenerator(config('app.ticket_code_salt'));
        });

        $this->app->bind(IPaymentGateway::class, StripePaymentGateway::class);
        $this->app->bind(IConfirmationNumberGenerator::class, RandomOrderConfirmationNumberGenerator::class);
        $this->app->bind(ITicketCodeGenerator::class, HashidsTicketCodeGenerator::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
