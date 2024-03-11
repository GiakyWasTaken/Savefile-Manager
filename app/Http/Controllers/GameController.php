<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

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

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            return Game::create($request->all());
        });
    }

    public function update($id, Request $request)
    {
        return DB::transaction(function () use ($id, $request) {
            $game = Game::findOrFail($id);
            $game->update($request->all());

            return $game;
        });
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            Game::find($id)->delete();

            return 204;
        });
    }
}
