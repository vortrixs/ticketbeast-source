<?php

namespace App\Http\Controllers;

use App\Billing\IPaymentGateway;
use App\Billing\PaymentFailedException;
use App\Concert;
use App\Order;
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
        $concert = Concert::published()->findOrFail($concertId);

        $this->validate(request(), [
            'email' => 'required|email',
            'ticket_quantity' => 'required|gte:1',
            'payment_token' => 'required'
        ]);

        try {
            $ticketQuantity = request('ticket_quantity');

            $this->paymentGateway->charge(
                $ticketQuantity * $concert->ticket_price,
                request('payment_token')
            );

            $order = $concert->orderTickets(request('email'), $ticketQuantity);
        } catch (PaymentFailedException $e) {
            return response()->json([], 422);
        }

        return response()->json([], 201);
    }
}
