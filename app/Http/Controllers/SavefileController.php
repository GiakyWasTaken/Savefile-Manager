<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Savefile;
use App\Models\Console;
use Illuminate\Support\Facades\DB;

class SavefileController extends Controller
{
    public function index()
    {
        // Return all savefiles with the console name
        return Savefile::join('console', 'savefile.fk_id_console', '=', 'console.id')
            ->select('savefile.*', 'console.console_name as console_name')
            ->get();
    }

    public function show($id)
    {
        $savefile = Savefile::find($id);

        if (!$savefile) {
            return response('Savefile not found', 404);
        }

        // Get the directory from the console
        $console = Console::find($savefile->fk_id_console);
        if ($console) {
            $savefile_dir = 'saves/' . $console->console_name . '/' . $savefile->file_path;
        } else {
            $savefile_dir = 'saves/null/' . $savefile->file_path;
        }

        return Storage::download($savefile_dir . $savefile->file_name);
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'savefile' => 'required|file',
            'file_name' => 'string',
            'file_path' => 'string',
            'fk_id_console' => 'required|integer'
        ]);

        // Set the file name
        if ($request->has('file_name')) {
            $savefile_name = $request->file_name;
        } else {
            // If no file name is provided, use the original file name
            $savefile_name = $request->file('savefile')->getClientOriginalName();
            $request->merge(['file_name' => $savefile_name]);
        }

        // Set the file path
        if ($request->has('file_path')) {
            $savefile_path = $request->file_path;
        } else {
            // If no file path is provided, use the root path
            $savefile_path = '/';
        }

        $fk_id_console = $request->fk_id_console;

        // Get the directory from the console
        $console = Console::find($fk_id_console);
        if ($console) {
            $savefile_dir = 'saves/' . $console->console_name . '/' . $savefile_path;
        } else {
            $savefile_dir = 'saves/.no_console/' . $savefile_path;
        }

        // Check if the file with the same id console already exists on the server or in the database
        if (Savefile::where('fk_id_console', $fk_id_console)->where('file_name', $savefile_name)->exists()) {
            return response('A file with that name for that console already exists in the database', 400);
        }

        if (Storage::exists($savefile_dir . $savefile_name)) {
            return response('A file with that name already exists on the server', 400);
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Save the file to the temporary directory
            Storage::putFileAs(
                'tmp',
                $request->file('savefile'),
                $savefile_name
            );

            // Save the file to the database within the transaction
            $savefile = Savefile::create([
                'file_name' => $savefile_name,
                'file_path' => $savefile_path,
                'fk_id_console' => $fk_id_console,
            ]);

            // Commit the transaction
            DB::commit();

            // Move the file to the console directory
            Storage::move('tmp/' . $savefile_name, $savefile_dir . $savefile_name);

            // Create backup file
            Storage::copy($savefile_dir . $savefile_name, $savefile_dir . 'backups/' . $savefile_name . '_' . date('Y_m_d_His') . '.bak');
            return response($savefile, 201);

        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            return response($e->getMessage(), 500);
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
        $savefile_path = $savefile->file_path;

        // Get the directory from the console
        $console = Console::find($savefile->fk_id_console);
        if ($console) {
            $savefile_dir = 'saves/' . $console->console_name . '/' . $savefile_path;
        } else {
            $savefile_dir = 'saves/.no_console/' . $savefile_path;
        }

        // Start a database transaction
        DB::beginTransaction();

        // Update the file on the server without changing the file name
        try {
            // Save the file to the temporary directory
            Storage::putFileAs(
                'tmp',
                $request->file('savefile'),
                $savefile_name
            );


            // Commit the transaction
            DB::commit();

            // Move the file to the console directory
            Storage::move('tmp/' . $savefile_name, $savefile_dir . $savefile_name);

            // Create backup file
            Storage::copy($savefile_dir . $savefile_name, $savefile_dir . 'backups/' . $savefile_name . '_' . date('Y_m_d_His') . '.bak');

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

            // Get the directory from the console
            $console = Console::find($savefile->fk_id_console);
            if ($console) {
                $savefile_dir = 'saves/' . $console->console_name . '/' . $savefile->file_path;
            } else {
                $savefile_dir = 'saves/.no_console/' . $savefile->file_path;
            }

            $savefile->delete();

            // Commit the transaction
            DB::commit();
            Storage::delete($savefile_dir . $savefile->file_name);

            return response('Savefile deleted', 200);
        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            return response('An error occurred while deleting the savefile', 500);
        }
    }
}
