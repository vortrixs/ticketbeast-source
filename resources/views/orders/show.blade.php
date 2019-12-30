@foreach($order->tickets()->get() as $ticket)
    <div>
        <p>{{ $order->confirmation_number }}</p>
        <p>${{ number_format($order->amount / 100, 2) }}</p>
        <p>**** **** **** {{ $order->card_last_four }}</p>
        <p>{{ $ticket->code }}</p>
    </div>
@endforeach
