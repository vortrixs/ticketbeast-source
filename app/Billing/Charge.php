<?php


namespace App\Billing;

class Charge
{
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getCardLastFour()
    {
        return $this->data['card_last_four'];
    }

    public function getAmount()
    {
        return $this->data['amount'];
    }

    public function getDestination()
    {
        return $this->data['destination'];
    }
}
