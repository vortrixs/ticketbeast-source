<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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

    public function orders() : HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
    * @return string
    */
    public function getDate() : string
    {
        return $this->date->format('F j, Y');
    }

    /**
     * @return string
     */
    public function getTime() : string
    {
        return $this->date->format('g:iA');
    }

    /**
    * @return string
    */
    public function getTicketPrice() : string
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function scopePublished(Builder $query) : Builder
    {
        return $query->whereNotNull('published_at');
    }

    public function orderTickets(string $email, int $ticketQuantity) : Order
    {
        /** @var Order $order */
        $order = $this->orders()->create(['email' => $email]);

        foreach (range(1, $ticketQuantity) as $i) {
            $order->tickets()->create([]);
        }

        return $order;
    }
}
