<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static Order find(int $id)
 *
 * @property string $email
 * @property int $id
 * @property int $concert_id
 */
class Order extends Model
{
    protected $guarded = [];

    public function tickets() : HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function concert() : BelongsTo
    {
        return $this->belongsTo(Concert::class);
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
            'amount' => $this->tickets()->count() * $this->concert()->first()->ticket_price
        ];
    }
}
