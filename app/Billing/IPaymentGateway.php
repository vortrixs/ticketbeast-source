<?php

namespace App\Billing;

interface IPaymentGateway
{
    public function charge(int $amount, string $token) : Charge;

    public function getToken(array $params) : string;
}
