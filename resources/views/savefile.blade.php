<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>Savefiles</title>
    </head>
    <body>
        <h1 style="text-align: center">List of all savefiles and related game name</h1>
        @php
            use App\Models\Savefile;
            use App\Models\Game;

            $savefiles = Savefile::orderBy(
                Game::select('name')
                    ->whereColumn('id', 'savefile.fk_id_game')
            )->get();
        @endphp
        <table style="margin-left: auto; margin-right: auto; text-align: center;">
            <caption> <b> All savefiles </b> </caption>
            <tr>
                <th scope="col"> Savefile ID </td>
                <th scope="col"> Savefile Name </td>
                <th scope="col"> Game Name </td>
            </tr>
            @foreach ($savefiles as $savefile)
                @php
                    $game = Game::find($savefile->fk_id_game);
                @endphp
                <tr>
                    <td> {{ $savefile->id }} </td>
                    <td> {{ $savefile->file_name }} </td>
                    <td> {{ $game->name }} </td>
                </tr>
            @endforeach
        </table>
    </body>
</html>
