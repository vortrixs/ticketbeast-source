<?php


namespace Tests\Unit\Mail;


use App\Mail\OrderConfirmationEmail;
use App\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderConfirmationEmailTest extends TestCase
{
    /**
     * @test
     */
    public function email_contains_a_link_to_the_order_confirmation_page()
    {
        $order = factory(Order::class)->make([
            'confirmation_number' => 'ORDER_CONFIRMATION_NUMBER_1234',
        ]);

        $email = (new OrderConfirmationEmail($order))->render();

        $this->assertStringContainsString(url('/orders/ORDER_CONFIRMATION_NUMBER_1234'), $email);
    }

    /**
     * @test
     */
    public function email_has_a_subject()
    {
        $order = factory(Order::class)->make([
            'confirmation_number' => 'ORDER_CONFIRMATION_NUMBER_1234',
        ]);
        $email = new OrderConfirmationEmail($order);

        $this->assertEquals('Your TicketBeast Order', $email->build()->subject);
    }
}
