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

    public function getToken(array $params = []) : string
    {
        return 'valid-token';
    }

    public function charge(int $amount, string $token)
    {
        if (is_callable($this->beforeFirstChargeCallback)) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
        }

        if ($token != $this->getToken()) {
            throw new PaymentFailedException;
        }

        $this->updateState($amount);

        return $this->charges->last();
    }


    public function beforeFirstCharge(\Closure $callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }

    public function retrieveCharge($id) : \stdClass
    {
        $key = $this->charges->search(function ($item) use ($id) {
            return $item->id === $id;
        });

        return $this->charges->get($key);
    }

    public function retrieveAllCharge() : Collection
    {
        return $this->charges;
    }

    private function updateState(int $amount)
    {
        $lastCharge = $this->charges->last();

        $id = null === $lastCharge ? 1 : $lastCharge->id+1;

        $this->charges->add((object) ['id' => $id, 'amount' => $amount]);
    }
}
