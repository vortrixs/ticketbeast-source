<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static Concert find(int $id)
 * @method static Concert create(array $array)
 * @method static Builder whereNotNull(string $string)
 * @method static Concert findOrFail(int $id)
 * @method static Builder published()
 *
 * @property int $id
 * @property string $title
 * @property string $subtitle
 * @property Carbon $date
 * @property int $ticket_price
 * @property string $venue
 * @property string $venue_address
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $additional_information
 * @property Carbon|null $published_at
 */
class Concert extends Model
{
    protected $guarded = [];

    protected $dates = ['date'];

    /**
     * @return HasMany|Order
     */
    public function orders() : HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany|Ticket
     */
    public function tickets() : HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function scopePublished(Builder $query) : Builder
    {
        return $query->whereNotNull('published_at');
    }

    public function getDate() : string
    {
        return $this->date->format('F j, Y');
    }

    public function getTime() : string
    {
        return $this->date->format('g:iA');
    }

    public function getTicketPrice() : string
    {
        return number_format($this->ticket_price / 100, 2);
    }


    public function orderTickets(string $email, int $quantity) : Order
    {
        /** @var Collection<Ticket> $tickets */
        $tickets = $this->tickets()->available()->take($quantity)->get();

        if ($tickets->count() < $quantity) {
            throw new NotEnoughTicketsException;
        }

        /** @var Order $order */
        $order = $this->orders()->create(['email' => $email]);

        $order->tickets()->saveMany($tickets);

        return $order;
    }

    public function addTickets(int $quantity) : Concert
    {
        $this->tickets()->createMany(
            array_fill(0, $quantity, [])
        );

        return $this;
    }

    public function getRemainingTickets()
    {
        return $this->tickets()->available()->count();
    }
}
