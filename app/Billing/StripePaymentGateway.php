<?php

namespace App\Billing;

use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;
use Stripe\Token;

class StripePaymentGateway implements IPaymentGateway
{
    /**
     * @var string
     */
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param int $amount
     * @param string $token
     *
     * @throws \Stripe\Exception\ApiErrorException
     *
     * @return Charge
     */
    public function charge(int $amount, string $token) : Charge
    {
        try {
            return Charge::create([
                'amount' => $amount,
                'source' => $token,
                'currency' => 'usd',
            ], ['api_key' => $this->apiKey]);
        } catch (InvalidRequestException $e) {
            throw new PaymentFailedException;
        }
    }

    /**
     * @param array $cardData
     * @return int
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getToken(array $cardData = []) : string
    {
        return $this->createTokenObject($cardData)->id;
    }

    /**
     * @param $cardData
     * @return Token
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createTokenObject($cardData)
    {
        return Token::create(['card' => $cardData], ['api_key' => $this->apiKey]);
    }

    /**
     * @param string $guid
     *
     * @throws \Stripe\Exception\ApiErrorException
     *
     * @return Charge
     */
    public function retrieveCharge(string $guid) : Charge
    {
        return Charge::retrieve($guid, ['api_key' => $this->apiKey]);
    }
}
