<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>Calculate</title>
    </head>
    <body>
        <p> Name: {{ $name }} </p>
        <br />
        <h1>Calculate</h1>
        <p> {{ $_SERVER['REQUEST_METHOD'] }} request detected </p>
        <p>Number 1: {{ $num1 }}</p>
        <p>Number 2: {{ $num2 }}</p>
        <button onclick="window.location.reload();">Reload</button>
        </form>
    </body>
</html>
