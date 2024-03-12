<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Savefile;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class SavefileController extends Controller
{
    public function index()
    {
        // Return all savefiles with the game name
        return Savefile::join('game', 'savefile.fk_id_game', '=', 'game.id')
            ->select('savefile.*', 'game.name as game_name')
            ->get();
    }

    public function show($id)
    {
        $savefile = Savefile::find($id);

        if (!$savefile) {
            return response('Savefile not found', 404);
        }

        // Get the directory from the game
        $game = Game::find($savefile->fk_id_game);
        if ($game) {
            $savefile_dir = 'saves/' . $game->name . '/';
        } else {
            $savefile_dir = 'saves/null/';
        }

        return Storage::download($savefile_dir . $savefile->file_name);
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'savefile' => 'required|file',
            'file_name' => 'string',
            'fk_id_game' => 'integer'
        ]);

        // Set the file name
        if ($request->has('file_name')) {
            $savefile_name = $request->file_name;
        } else {
            // If no file name is provided, use the original file name
            $savefile_name = $request->file('savefile')->getClientOriginalName();
            $request->merge(['file_name' => $savefile_name]);
        }

        $fk_id_game = $request->fk_id_game;

        // Get the directory from the game
        $game = Game::find($fk_id_game);
        if ($game) {
            $savefile_dir = 'saves/' . $game->name . '/';
        } else {
            $savefile_dir = 'saves/null/';
        }

        // Check if the file with the same id game already exists on the server or in the database
        if (Savefile::where('fk_id_game', $fk_id_game)->where('file_name', $savefile_name)->exists()) {
            return response('A file with that name for that game already exists in the database', 400);
        }

        if (Storage::exists($savefile_dir . $savefile_name)) {
            return response('A file with that name already exists on the server', 400);
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Save the file to the server
            Storage::putFileAs(
                $savefile_dir,
                $request->file('savefile'),
                $savefile_name
            );

            // Create backup file
            Storage::copy($savefile_dir . $savefile_name, $savefile_dir . 'backups/' . $savefile_name . '_' . date('Y_m_d_His') . '.bak');

            // Save the file to the database within the transaction
            $savefile = Savefile::create([
                'file_name' => $savefile_name,
                'fk_id_game' => $fk_id_game,
            ]);

            // Commit the transaction
            DB::commit();

            return response($savefile, 201);

        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            return response('An error occurred while saving the file', 500);
        }
    }

    public function update($id, Request $request)
    {
        // Validate the request
        $request->validate([
            'savefile' => 'required|file'
        ]);

        // Get the savefile name from the database
        $savefile = Savefile::findOrFail($id);
        $savefile_name = $savefile->file_name;

        // Get the directory from the game
        $game = Game::find($savefile->fk_id_game);
        if ($game) {
            $savefile_dir = 'saves/' . $game->name . '/';
        } else {
            $savefile_dir = 'saves/null/';
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the file on the server without changing the file name

            // Overwrite the file
            Storage::putFileAs(
                $savefile_dir,
                $request->file('savefile'),
                $savefile_name
            );

            // Create backup file
            Storage::copy($savefile_dir . $savefile_name, $savefile_dir . 'backups/' . $savefile_name . '_' . date('Y_m_d_His') . '.bak');

            // Commit the transaction
            DB::commit();

            return response($savefile, 200);

        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            return response('An error occurred while updating the file', 500);
        }
    }

    public function destroy($id)
    {
        // Start a database transaction
        DB::beginTransaction();

        try {
            // Get the savefile from the database
            $savefile = Savefile::findOrFail($id);

            // Get the directory from the game
            $game = Game::find($savefile->fk_id_game);
            if ($game) {
                $savefile_dir = 'saves/' . $game->name . '/';
            } else {
                $savefile_dir = 'saves/null/';
            }

            Storage::delete($savefile_dir . $savefile->file_name);
            $savefile->delete();

            // Commit the transaction
            DB::commit();

            return response('Savefile deleted', 200);
        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            return response('An error occurred while deleting the savefile', 500);
        }
    }
}
