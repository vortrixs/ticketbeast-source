<?php

namespace App\Billing;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FakePaymentGateway implements IPaymentGateway
{
    /**
     * @var Collection
     */
    private $charges;

    /**
     * @var Collection
     */
    private $tokens;

    /**
     * @var \Closure
     */
    private $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens = collect();
    }

    public function getToken(array $params = ['card_number' => 4242424242424242]) : string
    {
        $token = 'fake-tok_' . Str::random(24);
        $this->tokens[$token] = $params['card_number'];

        return $token;
    }

    /**
     * @param int    $amount
     * @param string $token
     *
     * @return \stdClass
     */
    public function charge(int $amount, string $token, string $accountId) : Charge
    {
        if (is_callable($this->beforeFirstChargeCallback)) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
        }

        if (false === $this->tokens->has($token)) {
            throw new PaymentFailedException;
        }

        return $this->addCharge($amount, $token, $accountId);
    }


    public function beforeFirstCharge(\Closure $callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }

    public function getCharges() : Collection
    {
        return $this->charges;
    }

    public function getTotalCharges()
    {
        return $this->charges->sum('data.amount');
    }

    private function addCharge(int $amount, string $token, string $accountId)
    {
        $lastCharge = $this->charges->last();

        $charge = new Charge([
            'amount' => $amount,
            'card_last_four' => substr($this->tokens->get($token), -4),
            'destination' => $accountId,
        ]);

        $charge->id = null === $lastCharge ? 1 : $lastCharge->id+1;

        $this->charges->add($charge);

        return $charge;
    }

    public function getTotalChargesFor(string $accountId)
    {
        return $this->charges->filter(function (Charge $charge) use ($accountId) {
            return $charge->getDestination() === $accountId;
        })->sum('data.amount');
    }
}
