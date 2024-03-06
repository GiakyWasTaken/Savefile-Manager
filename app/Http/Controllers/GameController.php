<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;

class GameController extends Controller
{
    public function index()
    {
        return Game::all();
    }

    public function show($id)
    {
        return Game::find($id);
    }

    public function create(Request $request)
    {
        return Game::create($request->all());
    }

    public function update($id, Request $request)
    {
        $game = Game::findOrFail($id);
        $game->update($request->all());

        return $game;
    }

    public function delete($id)
    {
        Game::find($id)->delete();

        return 204;
    }
}
