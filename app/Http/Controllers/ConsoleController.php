<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Console;
use Illuminate\Support\Facades\DB;

class ConsoleController extends Controller
{
    public function index()
    {
        return Console::all();
    }

    public function show($id)
    {
        return Console::find($id);
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'console_name' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $console = Console::create([
                'console_name' => $request->console_name,
            ]);

            DB::commit();

            return response($console, 201);

        } catch (\Exception $e) {

            DB::rollback();

            return response($e->getMessage(), 500);
        }
    }

    public function update($id, Request $request)
    {
        // Validate the request
        $request->validate([
            'console_name' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $console = Console::find($id);
            $console->console_name = $request->console_name;
            $console->save();

            DB::commit();

            return response($console, 200);

        } catch (\Exception $e) {

            DB::rollback();

            return response($e->getMessage(), 500);
        }

    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $console = Console::find($id);
            $console->delete();

            DB::commit();

            return response('Console deleted', 200);

        } catch (\Exception $e) {

            DB::rollback();

            return response($e->getMessage(), 500);
        }
    }
}
