<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Savefile;
use Illuminate\Support\Facades\DB;

class SavefileController extends Controller
{
    public function list()
    {
        return Savefile::all();
    }

    public function get($id)
    {
        $savefile = Savefile::find($id);

        if (!$savefile) {
            return response('Savefile not found', 404);
        }

        return Storage::download('saves/' . $savefile->file_name);
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

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Save the file to the server
            Storage::putFileAs(
                'saves',
                $request->file('savefile'),
                $savefile_name
            );

            // Save the file to the database within the transaction
            $savefile = Savefile::create([
                'file_name' => $savefile_name,
                'fk_id_game' => $request->fk_id_game,
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
            'savefile' => 'required|file',
            'fk_id_game' => 'integer'
        ]);

        // Get the savefile name from the database
        $savefile = Savefile::findOrFail($id);
        $savefile_name = $savefile->file_name;

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the file on the server without changing the file name

            // Check if the file already exists
            $old_savefile = 'saves/' . $savefile_name;
            if (Storage::exists($old_savefile)) {
                // Backup the old savefile before overwriting it
                $backupPath = 'backups/' . $savefile_name . '_' . date('Y_m_d_His') . '.bak';
                Storage::move($old_savefile, $backupPath);
            }

            // Overwrite the file
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

            // Commit the transaction
            DB::commit();

            return response($savefile, 200);

        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            return response('An error occurred while updating the file', 500);
        }
    }

    public function delete($id)
    {
        // Start a database transaction
        DB::beginTransaction();

        try {
            $savefile = Savefile::findOrFail($id);
            Storage::delete('saves/' . $savefile->file_name);
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
