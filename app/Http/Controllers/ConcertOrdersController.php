<?php

namespace App\Http\Controllers;

use App\Billing\IPaymentGateway;
use App\Billing\PaymentFailedException;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
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

        $ticketQuantity = request('ticket_quantity');

        try {
            /** @var Order $order */
            $order = $concert->orderTickets(request('email'), $ticketQuantity);

            $this->paymentGateway->charge(
                $ticketQuantity * $concert->ticket_price,
                request('payment_token')
            );
        } catch (PaymentFailedException $e) {
            $order->cancel();

            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }

        return response()->json($order->toArray(), 201);
    }
}
