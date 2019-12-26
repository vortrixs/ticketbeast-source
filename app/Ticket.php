<?php


namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Builder available()
 *
 * @property int order_id
 * @property int concert_id
 */
class Ticket extends Model
{
    protected $guarded = [];

    public function scopeAvailable(Builder $query) : Builder
    {
        return $query->whereNull('order_id');
    }

    public function release()
    {
        $this->update(['order_id' => null]);
    }
}
