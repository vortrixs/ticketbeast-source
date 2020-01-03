<div>
    <form action="{{ url('/login') }}" method="post">
        <input type="email" name="email">
        <input type="password" name="password">
        @csrf
        <input type="submit" value="Log in">
    </form>
    @if($errors->any())
        @foreach($errors->get('email') as $error)
            <p>{{ $error }}</p>
        @endforeach
    @endif
</div>
