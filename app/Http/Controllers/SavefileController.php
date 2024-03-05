<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        return Savefile::create($request->all());
    }

    public function update($id, Request $request)
    {
        $savefile = Savefile::findOrFail($id);
        $savefile->update($request->all());

        return $savefile;
    }

    public function delete($id)
    {
        Savefile::find($id)->delete();

        return 204;
    }
}
