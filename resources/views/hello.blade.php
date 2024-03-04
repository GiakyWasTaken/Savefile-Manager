<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>Hello World</title>
    </head>
    <body>
        <h1>Hello, World!</h1>
        @php
            $num = (int) rand(0, 100);
        @endphp
        @if ($num % 2 == 0)
            <p>The number is even and it's {{ $num }}</p>
        @else
            <p>The number is odd and it's {{ $num }}</p>
        @endif
        <button onclick="window.location.reload();">Reload</button>
        <form action="/calculate" method="post">
            @csrf
            <input type="number" name="num1" value="{{ $num }}">
            <input type="number" name="num2" value="{{ rand (0, 100) }}">
            <input type="text" name="name" value="John Doe">
            <input type="submit" value="Submit">
    </body>
</html>
