<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @method static AttendeeMessage first()
 * @method static int count()
 * @method static AttendeeMessage create(array $array)
 */
class AttendeeMessage extends Model
{
    protected $guarded = [];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function orders() : Builder
    {
        return $this->concert()->first()->orders();
    }

    public function withChunkedRecipients(int $chunk, \Closure $callback)
    {
        $this->orders()->chunk($chunk, function (Collection $orders) use ($callback) {
            $callback($orders->pluck('email'));
        });
    }
}
