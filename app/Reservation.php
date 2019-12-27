<?php


namespace App;

use Illuminate\Support\Collection;

class Reservation
{
    private $tickets;

    public function __construct(Collection $tickets)
    {
        $this->tickets = $tickets;
    }

    public function getTotalAmount() : int
    {
        return $this->tickets->sum('price');
    }
}
