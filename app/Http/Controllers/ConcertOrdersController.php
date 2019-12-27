<?php

namespace App\Http\Controllers;

use App\Billing\IPaymentGateway;
use App\Billing\PaymentFailedException;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use App\Reservation;
use Illuminate\Http\JsonResponse;

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
            $tickets = $concert->reserveTickets(request('ticket_quantity'));

            $reservation = new Reservation($tickets);

            $this->paymentGateway->charge(
                $reservation->getTotalAmount(),
                request('payment_token')
            );

            $order = Order::forTickets($tickets, request('email'), $reservation->getTotalAmount());
        } catch (PaymentFailedException $e) {
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }

        return response()->json($order->toArray(), 201);
    }
}
