<h1>{{ $concert->title }}</h1>
<h2>{{ $concert->subtitle }}</h2>
<p>{{ $concert->getDate() }}</p>
<p>Doors at {{ $concert->getTime() }}</p>
<p>Price: {{ $concert->getTicketPrice() }}</p>
<p>{{ $concert->venue }}</p>
<p>{{ $concert->venue_address }}</p>
<p>{{ $concert->city }}, {{ $concert->state }} {{ $concert->zip }}</p>
<p>{{ $concert->additional_information }}</p>
