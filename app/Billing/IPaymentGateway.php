<?php

namespace App\Billing;

interface IPaymentGateway
{
    public function charge(int $amount, string $token, string $accountId) : Charge;

    public function getToken(array $params) : string;
}
