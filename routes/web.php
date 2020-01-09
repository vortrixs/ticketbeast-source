<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;

Route::get('/concerts/{id}', 'ConcertsController@show')->name('concerts.show');

Route::post('/concerts/{id}/orders', 'ConcertOrdersController@store');

Route::get('orders/{confirmationNumber}', 'OrdersController@show');

Route::group(['namespace' => 'Auth'], function () {
    Route::get('/login', 'LoginController@show')->name('login');
    Route::post('/login', 'LoginController@login');

    Route::post('/logout', 'LoginController@logout');

    Route::post('/register', 'RegisterController@register')->name('register');
});

Route::get('/invitation/{code}', 'InvitationController@show')->name('invitation.show');

Route::group(['middleware' => 'auth', 'prefix' => 'backstage', 'namespace' => 'Backstage'], function () {
    Route::get('/concerts', 'ConcertsController@index')->name('backstage.concerts.index');
    Route::post('/concerts', 'ConcertsController@store');

    Route::get('/concerts/new', 'ConcertsController@create');

    Route::get('/concerts/{id}/edit', 'ConcertsController@edit')->name('backstage.concerts.edit');

    Route::patch('/concerts/{id}', 'ConcertsController@update')->name('backstage.concerts.update');

    Route::post('/published-concerts', 'PublishedConcertsController@store');

    Route::get('/published-concerts/{id}/orders', 'PublishedConcertOrdersController@index')
        ->name('backstage.published_concert_orders.index');

    Route::get('/concerts/{id}/messages/new', 'ConcertMessagesController@create')
        ->name('backstage.concert_messages.new');
    Route::post('/concerts/{id}/messages', 'ConcertMessagesController@store')
        ->name('backstage.concert_messages');

    Route::get('/stripe-connect/connect', 'StripeConnectController@connect')->name('backstage.stripe.connect');
    Route::get('/stripe-connect/authorize', 'StripeConnectController@authorizeRedirect')->name('backstage.stripe.authorize');
    Route::get('/stripe-connect/redirect', 'StripeConnectController@redirect')->name('backstage.stripe.redirect');
});
