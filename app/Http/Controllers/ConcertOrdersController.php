<?php

namespace App\Http\Controllers;

use App\Billing\IPaymentGateway;
use App\Billing\PaymentFailedException;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Mail\OrderConfirmationEmail;
use App\Order;
use App\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class ConcertOrdersController extends Controller
{
    /**
     * @var IPaymentGateway
     */
    private $paymentGateway;

    public function __construct(IPaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store(int $concertId) : JsonResponse
    {
        /** @var Concert $concert */
        $concert = Concert::published()->findOrFail($concertId);

        $this->validate(request(), [
            'email' => 'required|email',
            'ticket_quantity' => 'required|gte:1',
            'payment_token' => 'required'
        ]);

        try {
            $reservation = $concert->reserveTickets(request('ticket_quantity'), request('email'));
            $order       = $reservation->complete($this->paymentGateway, request('payment_token'));

            Mail::to($order->email)->send(new OrderConfirmationEmail($order));
        } catch (PaymentFailedException $e) {
            $reservation->cancel();

            return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json($order->toArray(), Response::HTTP_CREATED);
    }
}
