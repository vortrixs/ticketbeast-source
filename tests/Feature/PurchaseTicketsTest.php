<?php


namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\IPaymentGateway;
use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var FakePaymentGateway
     */
    private $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakePaymentGateway;
        $this->app->instance(IPaymentGateway::class, $this->gateway);
    }

    private function orderTickets(Concert $concert, array $params) : TestResponse
    {
        return $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    /**
     * @test
     */
    public function customer_can_purchase_tickets_to_published_concerts()
    {
        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 3250]);

        $response = $this->orderTickets($concert, [
            'email' => 'foo@bar.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->gateway->getValidTestToken(),
        ]);

        $response->assertStatus(201);

        $this->assertEquals(9750, $this->gateway->totalCharges());

        /** @var Order $order */
        $order = $concert->orders()->where('email', 'foo@bar.com')->first();

        $this->assertNotNull($order);

        $this->assertEquals(3, $order->tickets()->count());
    }

    /**
     * @test
     */
    public function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create([]);

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->gateway->getValidTestToken(),
        ]);

        $this->assertValidationErrors($response, 'email');
    }

    /**
     * @test
     */
    public function email_must_be_valid_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create([]);

        $response = $this->orderTickets($concert, [
            'email' => 'not-a-valid-email',
            'ticket_quantity' => 3,
            'payment_token' => $this->gateway->getValidTestToken(),
        ]);

        $this->assertValidationErrors($response, 'email');
    }

    /**
     * @test
     */
    public function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create([]);

        $response = $this->orderTickets($concert, [
            'email' => 'foo@bar.com',
            'payment_token' => $this->gateway->getValidTestToken(),
        ]);

        $this->assertValidationErrors($response, 'ticket_quantity');
    }

    /**
     * @test
     */
    public function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create([]);

        $response = $this->orderTickets($concert, [
            'email' => 'foo@bar.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->gateway->getValidTestToken(),
        ]);

        $this->assertValidationErrors($response, 'ticket_quantity');
    }

    /**
     * @test
     */
    public function payment_token_is_required()
    {
        $concert = factory(Concert::class)->state('published')->create([]);

        $response = $this->orderTickets($concert, [
            'email' => 'foo@bar.com',
            'ticket_quantity' => 3,
        ]);

        $this->assertValidationErrors($response, 'payment_token');
    }

    /**
     * @test
     */
    public function an_order_is_not_created_if_payment_fails()
    {
        $concert = factory(Concert::class)->state('published')->create([]);

        $response = $this->orderTickets($concert, [
            'email' => 'foo@bar.com',
            'ticket_quantity' => 0,
            'payment_token' => 'invalid-payment-token',
        ]);

        $response->assertStatus(422);

        $order = $concert->orders()->where('email', 'foo@bar.com')->first();

        $this->assertNull($order);
    }

    /**
     * @test
     */
    public function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->state('unpublished')->create([]);

        $response = $this->orderTickets($concert, [
            'email' => 'foo@bar.com',
            'ticket_quantity' => 1,
            'payment_token' => $this->gateway->getValidTestToken(),
        ]);

        $response->assertStatus(404);
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->gateway->totalCharges());
    }
}
