<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>Database</title>
    </head>
    <body>
        <h1>Database</h1>
        @php
            echo "savefile table";
            echo "<br />";
            $data = DB::select('select * from savefile');
            echo "<pre>";
            print_r($data);
            echo "</pre>";
            echo "<br />";
            echo "console table";
            echo "<br />";
            $data = DB::select('select * from console');
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        @endphp
    </body>
</html>
