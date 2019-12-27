<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Ticket::class, 'concert_id', 'id', 'id', 'order_id');
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

    public function getTicketPriceInDollars() : string
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function findAvailableTickets(int $quantity) : Collection
    {
        /** @var Collection<Ticket> $tickets */
        $tickets = $this->tickets()->available()->take($quantity)->get();

        if ($tickets->count() < $quantity) {
            throw new NotEnoughTicketsException;
        }

        return $tickets;
    }

    public function addTickets(int $quantity) : Concert
    {
        $this->tickets()->createMany(
            array_fill(0, $quantity, [])
        );

        return $this;
    }

    public function countRemainingTickets() : int
    {
        return $this->tickets()->available()->count();
    }
}
