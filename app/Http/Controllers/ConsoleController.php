<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Console;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsoleController extends Controller
{
    public function index()
    {
        Log::channel('daily')->info('INDEX: Consoles requested');

        return Console::all();
    }

    public function show($id)
    {
        Log::channel('daily')->info('SHOW: Console with id ' . $id . ' requested');

        $console = Console::find($id);
        
        if (!$console) {
            $message = 'Console with id ' . $id . ' not found';
            Log::channel('daily')->warning('SHOW: ' . $message);

            return response($message, 404);
        }

        Log::channel('daily')->info('SHOW: Console ' . $console . ' successful');

        return response()->json($console);
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'console_name' => 'required|string|unique:console'
        ]);

        Log::channel('daily')->info('STORE: Console ' . $request->console_name . ' requested');

        $existingConsole = Console::where('console_name', $request->console_name)->first();

        if ($existingConsole) {
            $message = 'Console with name ' . $request->console_name . ' already exists';

            Log::channel('daily')->warning('STORE: ' . $message);

            return response($message, 409);
        }

        DB::beginTransaction();

        try {
            $console = Console::create([
                'console_name' => $request->console_name,
            ]);

            DB::commit();

            Log::channel('daily')->info('STORE: Console ' . $console . ' successful');

            return response($console, 201);

        } catch (\Exception $e) {

            DB::rollback();

            $message = 'Console ' . $request->console_name . ' failed';
            Log::channel('daily')->error('STORE: ' . $message, ['error' => $e->getMessage()]);

            return response($message, 500);
        }
    }

    public function update($id, Request $request)
    {
        // Validate the request
        $request->validate([
            'console_name' => 'required|string',
        ]);

        Log::channel('daily')->info('UPDATE: Console with id ' . $id . ' to ' . $request->console_name . ' requested');

        $console = Console::find($id);

        if (!$console) {
            $message = 'Console with id ' . $id . ' not found';
            Log::channel('daily')->warning('UPDATE: ' . $message);

            return response($message, 404);
        }

        $existingConsole = Console::where('console_name', $request->console_name)->first();
        
        if ($existingConsole && $existingConsole->id !== $console->id) {
            $message = 'Console with name ' . $request->console_name . ' already exists';
            Log::channel('daily')->warning('UPDATE: ' . $message);

            return response($message, 409);
        }

        DB::beginTransaction();

        try {

            $console->console_name = $request->console_name;
            $console->save();

            DB::commit();

            Log::channel('daily')->info('UPDATE: Console ' . $console . ' successful');

            return response($console, 200);

        } catch (\Exception $e) {

            DB::rollback();

            $message = 'Console with id ' . $id . ' to ' . $request->console_name . ' failed';
            Log::channel('daily')->error('UPDATE: ' . $message, ['error' => $e->getMessage()]);

            return response($message, 500);
        }

    }

    public function destroy($id)
    {
        Log::channel('daily')->info('DESTROY: Console with id ' . $id . ' requested');

        DB::beginTransaction();

        try {
            $console = Console::find($id);

            if (!$console) {
                $message = 'Console with id ' . $id . ' not found';
                Log::channel('daily')->warning('DESTROY: ' . $message);

                return response($message, 404);
            }

            $console->delete();

            DB::commit();

            $message = 'Console with id ' . $id . ' deleted';
            Log::channel('daily')->info('DESTROY: ' . $message);

            return response($message, 200);

        } catch (\Exception $e) {

            DB::rollback();

            $message = 'Console with id ' . $id . ' failed';
            Log::channel('daily')->error('DESTROY: ' . $message, ['error' => $e->getMessage()]);

            return response($message, 500);
        }
    }
}
