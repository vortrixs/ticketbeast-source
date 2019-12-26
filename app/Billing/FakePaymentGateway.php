<?php

namespace App\Billing;

use Illuminate\Support\Collection;

class FakePaymentGateway implements IPaymentGateway
{
    /**
     * @var Collection
     */
    private $charges;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidTestToken() : string
    {
        return 'valid-token';
    }

    public function totalCharges() : int
    {
        return $this->charges->sum();
    }

    public function charge(int $amount, string $token) : IPaymentGateway
    {
        if ($token != $this->getValidTestToken()) {
            throw new PaymentFailedException;
        }

        $this->charges->add($amount);

        return $this;
    }
}
