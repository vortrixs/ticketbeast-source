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
use Illuminate\Http\Request;
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

    public function store(Request $request, int $id) : JsonResponse
    {
        /** @var Concert $concert */
        $concert = Concert::published()->findOrFail($id);

        $request->validate([
            'email' => 'required|email',
            'ticket_quantity' => 'required|gte:1',
            'payment_token' => 'required'
        ]);

        try {
            $reservation = $concert->reserveTickets(
                $request->get('ticket_quantity'),
                $request->get('email')
            );

            $order       = $reservation->complete(
                $this->paymentGateway,
                $request->get('payment_token'),
                $concert->user->stripe_account_id
            );

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
