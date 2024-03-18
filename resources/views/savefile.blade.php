<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>Savefiles</title>
    </head>
    <body>
        <h1 style="text-align: center">List of all savefiles and related console</h1>
        @php
            use App\Models\Savefile;
            use App\Models\Console;

            $savefiles = Savefile::orderBy(
                Console::select('console_name')
                    ->whereColumn('id', 'savefile.fk_id_console')
            )->get();
        @endphp
        <table style="margin-left: auto; margin-right: auto; text-align: center;">
            <caption> <b> All savefiles </b> </caption>
            <tr>
                <th scope="col"> Savefile ID </td>
                <th scope="col"> Savefile Path and Name </td>
                <th scope="col"> Console Name </td>
            </tr>
            @foreach ($savefiles as $savefile)
                @php
                    $console = Console::find($savefile->fk_id_console);
                @endphp
                <tr>
                    <td> {{ $savefile->id }} </td>
                    <td> {{ $savefile->file_path . $savefile->file_name }} </td>
                    <td> {{ $console->console_name }} </td>
                </tr>
            @endforeach
        </table>
    </body>
</html>
