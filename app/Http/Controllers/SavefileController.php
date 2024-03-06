<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Savefile;

class SavefileController extends Controller
{
    public function index()
    {
        return Savefile::all();
    }

    public function show($id)
    {
        $savefile = Savefile::find($id);

        if (!$savefile) {
            return response('Savefile not found', 404);
        }

        return Storage::download($savefile->file_name);
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

        // Check if the file already exists on the server or in the database
        if (Storage::exists('saves/' . $savefile_name) || Savefile::where('file_name', $savefile_name)->exists()) {
            return response('A file with that name already exists', 400);
        }

        // Save the file to the server
        Storage::putFileAs(
            'saves',
            $request->file('savefile'),
            $savefile_name
        );

        // Save the file to the database
        $savefile = Savefile::create([
            'file_name' => $savefile_name,
            'fk_id_game' => $request->fk_id_game,
        ]);

        // To-do: backup the old savefile before overwriting it

        return $savefile;
    }

    public function update($id, Request $request)
    {
        // Validate the request
        $request->validate([
            'savefile' => 'required|file',
            'fk_id_game' => 'integer'
        ]);

        // Get the savefile name from the database
        $savefile = Savefile::findOrFail($id);
        $savefile_name = $savefile->file_name;

        // Update the file on the server without changing the file name
        Storage::delete('saves/' . $savefile_name);
        Storage::putFileAs(
            'saves',
            $request->file('savefile'),
            $savefile_name
        );

        // Update the game ID if provided
        if ($request->fk_id_game != null) {
            $savefile->update([
                'fk_id_game' => $request->fk_id_game,
            ]);
        }

        // To-do: backup the old savefile before updating it

        return $savefile;
    }

    public function delete($id)
    {
        try {
            $savefile = Savefile::findOrFail($id);
            Storage::delete('saves/' . $savefile->file_name);
            $savefile->delete();
        } catch (\Exception $e) {
            return response('Savefile not found', 404);
        }

        return response('Savefile deleted', 200);
    }
}
