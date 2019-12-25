<?php

namespace App\Billing;

use Illuminate\Support\Collection;

class FakePaymentGateway implements IPaymentGateway
{
    /**
     * @var Collection
     */
    private $charges;

    public function __construct(Collection $collection = null)
    {
        $this->charges = null === $collection
            ? collect()
            : $collection;
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

        return new FakePaymentGateway($this->charges->add($amount));
    }
}
