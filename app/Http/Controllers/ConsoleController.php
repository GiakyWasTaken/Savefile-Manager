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

        return Console::find($id);
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'console_name' => 'required|string|unique:console'
        ]);

        Log::channel('daily')->info('STORE: Console ' . $request->console_name . ' requested');

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

            Log::channel('daily')->error('STORE: Console ' . $request->console_name . ' failed', ['error' => $e->getMessage()]);

            return response($e->getMessage(), 500);
        }
    }

    public function update($id, Request $request)
    {
        // Validate the request
        $request->validate([
            'console_name' => 'required|string',
        ]);

        Log::channel('daily')->info('UPDATE: Console with id ' . $id . ' to ' . $request->console_name . ' requested');

        DB::beginTransaction();

        try {
            $console = Console::find($id);
            $console->console_name = $request->console_name;
            $console->save();

            DB::commit();

            Log::channel('daily')->info('UPDATE: Console ' . $console . ' successful');

            return response($console, 200);

        } catch (\Exception $e) {

            DB::rollback();

            $message = 'Console with id ' . $id . ' to ' . $request->console_name . ' failed';
            Log::channel('daily')->error('UPDATE: ' . $message, ['error' => $e->getMessage()]);

            return response($message, ['error' => $e->getMessage()], 500);
        }

    }

    public function destroy($id)
    {
        Log::channel('daily')->info('DESTROY: Console with id ' . $id . ' requested');

        DB::beginTransaction();

        try {
            $console = Console::find($id);
            $console->delete();

            DB::commit();

            Log::channel('daily')->info('DESTROY: Console ' . $console . ' successful');

            return response('Console ' . $console . ' deleted', 200);

        } catch (\Exception $e) {

            DB::rollback();

            $message = 'Console with id ' . $id . ' failed';
            Log::channel('daily')->error('DESTROY: ' . $message, ['error' => $e->getMessage()]);

            return response($message, ['error' => $e->getMessage()], 500);
        }
    }
}
