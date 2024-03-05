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
        return Savefile::find($id);
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'savefile' => 'required|file',
            'fk_id_game' => 'integer'
        ]);

        $savefile_name = $request->file('savefile')->getClientOriginalName();

        // Check if the file already exists on the server
        if (Storage::exists('saves/' . $savefile_name)) {
            return response('The file already exists on the server', 409);
        }

        // Save the file to the server
        $path = Storage::putFileAs(
            'saves',
            $request->file('savefile'),
            $savefile_name
        );

        // Save the file to the database
        $savefile = Savefile::create([
            'file_name' => $path,
            'fk_id_game' => $request->fk_id_game,
        ]);

        // To-do: backup the old savefile before overwriting it

        return $savefile;
    }

    public function update($id, Request $request)
    {
        $savefile = Savefile::findOrFail($id);
        $savefile->update($request->all());

        // To-do: backup the old savefile before updating it

        return $savefile;
    }

    public function delete($id)
    {
        Savefile::find($id)->delete();

        return 204;
    }
}
