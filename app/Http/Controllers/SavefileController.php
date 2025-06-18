<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Savefile;
use App\Models\Console;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SavefileController extends Controller
{
    public function index()
    {
        Log::channel('daily')->info('INDEX: Savefiles requested');

        // Return all savefiles with the console name
        return Savefile::join('console', 'savefile.fk_id_console', '=', 'console.id')
            ->select('savefile.*', 'console.console_name as console_name')
            ->get();
    }

    public function show($id, Request $request)
    {
        Log::channel('daily')->info("SHOW: Savefile with id {$id} requested");

        $savefile = Savefile::find($id);

        if (!$savefile) {
            $message = "Savefile with id {$id} not found";
            Log::channel('daily')->warning("SHOW: {$message}");

            return response($message, 404);
        }

        // Get the directory from the console
        $console = Console::find($savefile->fk_id_console);

        $savefile_dir = $console
            ? "saves/{$console->console_name}/{$savefile->file_path}"
            : "saves/null/{$savefile->file_path}";

        // Check if the request wants a JSON response
        if ($request->wantsJson()) {
            // Return the savefile as JSON
            Log::channel('daily')->info("SHOW: Json for savefile with id {$id} successful");
            return response()->json($savefile);
        } else {
            // Return the file as a download
            Log::channel('daily')->info("SHOW: Download for savefile with id {$id} successful");
            return Storage::download("{$savefile_dir}{$savefile->file_name}", $savefile->file_name);
        }
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'savefile' => 'required|file',
            'file_name' => 'string',
            'file_path' => 'string',
            'updated_at' => 'date',
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

            // Check if the savefile path ends with a slash and add it
            if (substr($savefile_path, -1) !== '/') {
                $savefile_path .= '/';
            }
        } else {
            // If no file path is provided, use the root path
            $savefile_path = '/';
        }

        $fk_id_console = $request->fk_id_console;

        // Get the directory from the console
        $console = Console::find($fk_id_console);

        if ($console) {
            $savefile_dir = "saves/{$console->console_name}/{$savefile_path}";
        } else {
            $savefile_dir = "saves/.no_console/{$savefile_path}";
        }

        // Set the updated_at field
        if ($request->has('updated_at')) {
            $updated_at = $request->updated_at;
        } else {
            $updated_at = now();
            $request->merge(['updated_at' => $updated_at]);
        }

        Log::channel('daily')->info("STORE: Savefile with name {$savefile_name} and path {$savefile_path} for console with id {$fk_id_console} updated at {$updated_at} requested");

        // Check if a file with the same path and console already exists in the database
        $existingSavefile = Savefile::where('fk_id_console', $fk_id_console)
                ->where('file_name', $savefile_name)
                ->where('file_path', $savefile_path)
                ->first();

        if ($existingSavefile) {
            $message = "File with path {$savefile_path} and name {$savefile_name} for console with id {$fk_id_console} updated at {$updated_at} already exists in the database updated at {$existingSavefile->updated_at}";
            Log::channel('daily')->warning("STORE: {$message}");
            return response($message, 409);
        }

        // Check if a file with the same path and console already exists on the server
        if (Storage::exists("{$savefile_dir}{$savefile_name}")) {
            $message = "File with path {$savefile_path} and name {$savefile_name} for console with id {$fk_id_console} updated at {$updated_at} already exists on the server";
            Log::channel('daily')->warning("STORE: {$message}");
            return response($message, 409);
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Save the file to the temporary directory
            Storage::putFileAs(
                "tmp/{$savefile_dir}",
                $request->file('savefile'),
                $savefile_name
            );

            // Save the file to the database within the transaction
            $savefile = Savefile::create([
                'file_name' => $savefile_name,
                'file_path' => $savefile_path,
                'updated_at' => $updated_at,
                'fk_id_console' => $fk_id_console,
            ]);

            // Commit the transaction
            DB::commit();

            // Move the file to the console directory
            Storage::move("tmp/{$savefile_dir}{$savefile_name}", "{$savefile_dir}{$savefile_name}");

            // Create backup file
            Storage::copy("{$savefile_dir}{$savefile_name}", "{$savefile_dir}backups/{$savefile_name}_" . date('Y_m_d_His', strtotime($updated_at)) . ".bak");

            Log::channel('daily')->info("STORE: Savefile {$savefile} created successfully");

            return response($savefile, 201);

        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();

            $message = "File with path {$savefile_path} and name {$savefile_name} for console with id {$fk_id_console} failed";
            Log::channel('daily')->error("STORE: {$message}", ['error' => $e->getMessage()]);

            return response($message, 500);
        }
    }

    public function update($id, Request $request)
    {
        // Validate the request
        $request->validate([
            'savefile' => 'required|file',
            'updated_at' => 'date'
        ]);

        // Get the savefile name from the database
        $savefile = Savefile::find($id);

        if (!$savefile) {
            $message = "Savefile with id {$id} not found";
            Log::channel('daily')->warning("UPDATE: {$message}");

            return response($message, 404);
        }

        $savefile_name = $savefile->file_name;
        $savefile_path = $savefile->file_path;

        // Get the directory from the console
        $console = Console::find($savefile->fk_id_console);

        if ($console) {
            $savefile_dir = 'saves/' . $console->console_name . '/' . $savefile_path;
        } else {
            $savefile_dir = 'saves/.no_console/' . $savefile_path;
        }

        // Get the updated_at field
        if ($request->has('updated_at')) {
            $updated_at = $request->updated_at;
        } else {
            $updated_at = now();
            $request->merge(['updated_at' => $updated_at]);
        }

        Log::channel('daily')->info("UPDATE: Savefile with id {$id} updated at {$updated_at} requested");

        // Start a database transaction
        DB::beginTransaction();

        // Update the file on the server without changing the file name
        try {
            // Save the file to the temporary directory
            Storage::putFileAs('tmp', $request->file('savefile'), $savefile_name);

            // Update the update date of the file in the database
            $savefile->updated_at = $updated_at;
            $savefile->save();

            // Commit the transaction
            DB::commit();

            // Move the file to the console directory
            Storage::move("tmp/{$savefile_name}", "{$savefile_dir}{$savefile_name}");

            // Create backup file
            Storage::copy("{$savefile_dir}{$savefile_name}", "{$savefile_dir}backups/{$savefile_name}_" . date('Y_m_d_His', strtotime($updated_at)) . ".bak");

            Log::channel('daily')->info("UPDATE: Savefile {$savefile} updated successfully");

            return response($savefile, 200);

        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();

            $message = "File with path {$savefile_path} and name {$savefile_name} failed";
            Log::channel('daily')->error("UPDATE: {$message}", ['error' => $e->getMessage()]);

            return response($message, 500);
        }
    }

    public function destroy($id)
    {
        Log::channel('daily')->info("DESTROY: Savefile with id {$id} requested");

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Get the savefile from the database
            $savefile = Savefile::find($id);

            if (!$savefile) {
                $message = "Savefile with id {$id} not found";
                Log::channel('daily')->warning("DESTROY: {$message}");

                return response($message, 404);
            }

            // Get the directory from the console
            $console = Console::find($savefile->fk_id_console);

            $savefile_dir = $console
                ? "saves/{$console->console_name}/{$savefile->file_path}"
                : "saves/.no_console/{$savefile->file_path}";

            $savefile->delete();

            // Commit the transaction
            DB::commit();
            Storage::delete("{$savefile_dir}{$savefile->file_name}");

            $message = "Savefile with id {$id} deleted successfully";
            Log::channel('daily')->info("DESTROY: {$message}");

            return response($message, 200);

        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();

            $message = "Savefile with id {$id} failed";
            Log::channel('daily')->error("DESTROY: {$message}", ['error' => $e->getMessage()]);

            return response($message, 500);
        }
    }
}
