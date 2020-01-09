<?php

namespace App;

use App\Billing\Charge;
use App\Facades\ConfirmationNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Order extends Model
{
    protected $guarded = [];

    public function tickets() : HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function toArray()
    {
        return [
            'email' => $this->email,
            'amount' => $this->amount,
            'confirmation_number' => $this->confirmation_number,
            'tickets' => $this->tickets()->get()->map(function ($ticket) {
                return ['code' => $ticket->code];
            })->all(),
        ];
    }

    public static function forTickets(Collection $tickets, string $email, Charge $charge) : Order
    {
        /** @var Order $order */
        $order = self::create([
            'email' => $email,
            'amount' => $charge->getAmount(),
            'confirmation_number' => ConfirmationNumber::generate(),
            'card_last_four' => $charge->getCardLastFour(),
        ]);

        $tickets->each->claimFor($order);

        return $order;
    }

    public static function findByConfirmationNumber(string $confirmationNumber)
    {
        return self::where('confirmation_number', $confirmationNumber)->firstOrFail();
    }
}
