<?php

namespace App\Billing;

use App\Billing\Charge;
use Stripe\Charge as StripeCharge;
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
     * @param int    $amount
     * @param string $token
     *
     * @throws \Stripe\Exception\ApiErrorException
     *
     * @return Charge
     */
    public function charge(int $amount, string $token, string $accountId) : Charge
    {
        try {
            /** @var StripeCharge $stripeCharge */
            $stripeCharge = StripeCharge::create(
                [
                    'amount' => $amount,
                    'source' => $token,
                    'currency' => 'usd',
                    'destination' => [
                        'account' => $accountId,
                        'amount' => $amount * .9,
                    ]
                ],
                ['api_key' => $this->apiKey]
            );

            // destination => ['amount', 'account']

            return new Charge(
                ['amount' => $stripeCharge->amount, 'card_last_four' => $stripeCharge->source->last4, 'destination' => $accountId]
            );
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
}
