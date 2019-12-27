<?php


namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method Builder available()
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

    public function release()
    {
        $this->update(['order_id' => null]);
    }

    public function getPriceAttribute() : int
    {
        return $this->concert()->first()->ticket_price;
    }

    public function reserve()
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }
}
