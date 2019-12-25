<?php

namespace App\Billing;

interface IPaymentGateway
{
    public function charge(int $amount, string $token) : IPaymentGateway;
}
