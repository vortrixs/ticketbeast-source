<?php


namespace App;

use App\Facades\TicketCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method Builder available()
 * @method Builder sold()
 *
 * @property int order_id
 * @property int concert_id
 * @property Carbon reserved_at
 */
class Ticket extends Model
{
    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function concert() : BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }

    /**
     * @return BelongsTo
     */
    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeAvailable(Builder $query) : Builder
    {
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    public function scopeSold(Builder $query) : Builder
    {
        return $query->whereNotNull('order_id');
    }

    public function release()
    {
        $this->update(['reserved_at' => null]);
    }

    public function getPriceAttribute() : int
    {
        return $this->concert()->first()->ticket_price;
    }

    public function reserve()
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }

    public function claimFor(Order $order)
    {
        $this->code = TicketCode::generateFor($this);

        $order->tickets()->save($this);
    }
}
