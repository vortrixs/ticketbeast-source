<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static Order find(int $id)
 * @method static create(array $data)
 *
 * @property string $email
 * @property int $id
 * @property int $concert_id
 * @property int amount
 */
class Order extends Model
{
    protected $guarded = [];

    public function tickets() : HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * @throws \Exception If this.delete() fails
     */
    public function cancel() : void
    {
        /** @var Ticket $ticket */
        foreach ($this->tickets()->get() as $ticket) {
            $ticket->release();
        }

        $this->delete();
    }

    public function toArray()
    {
        return [
            'email' => $this->email,
            'ticket_quantity' => $this->tickets()->count(),
            'amount' => $this->amount
        ];
    }

    public static function forTickets(Collection $tickets, string $email, int $amount) : Order
    {
        /** @var Order $order */
        $order = self::create([
            'email' => $email,
            'amount' => $amount,
        ]);

        $order->tickets()->saveMany($tickets);

        return $order;
    }
}
