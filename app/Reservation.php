<?php


namespace App;

use App\Billing\IPaymentGateway;
use Illuminate\Support\Collection;

class Reservation
{
    private $tickets;
    private $email;

    public function __construct(Collection $tickets, string $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public function getTotalAmount() : int
    {
        return $this->tickets->sum('price');
    }

    public function cancel()
    {
        $this->tickets->each(function (Ticket $ticket) {
            $ticket->release();
        });
    }

    public function complete(IPaymentGateway $gateway, string $token) : Order
    {
        $gateway->charge($this->getTotalAmount(), $token);

        return Order::forTickets($this->tickets, $this->email, $this->getTotalAmount());
    }
}
