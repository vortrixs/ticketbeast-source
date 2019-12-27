<?php

namespace App\Billing;

use Illuminate\Support\Collection;

class FakePaymentGateway implements IPaymentGateway
{
    /**
     * @var Collection
     */
    private $charges;

    /**
     * @var \Closure
     */
    private $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidTestToken() : string
    {
        return 'valid-token';
    }

    public function getTotalCharges() : int
    {
        return $this->charges->sum();
    }

    public function charge(int $amount, string $token) : IPaymentGateway
    {
        if (is_callable($this->beforeFirstChargeCallback)) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
        }

        if ($token != $this->getValidTestToken()) {
            throw new PaymentFailedException;
        }

        $this->charges->add($amount);

        return $this;
    }

    public function beforeFirstCharge(\Closure $callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }
}
