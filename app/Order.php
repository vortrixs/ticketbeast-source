<?php

namespace App;

use App\Billing\Charge;
use App\Facades\ConfirmationNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @method static Order find(int $id)
 * @method static Order create(array $data)
 * @method static Builder where(string $string, string $confirmationNumber)
 * @method static Builder whereIn(string $column, $values)
 *
 * @property string $email
 * @property int $id
 * @property int $concert_id
 * @property int amount
 * @property string confirmation_number
 */
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
