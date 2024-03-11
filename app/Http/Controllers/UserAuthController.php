<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        // Create a token for the user
        $token = $user->createToken('user_token')->accessToken;

        // Return the user
        return response([ 'user' => $user, 'token' => $token ], 201);
    }

    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Attempt to log the user in
        if (auth()->attempt($request->only('email', 'password'))) {
            // Return the user
            $user = auth()->user();
            $token = $user->createToken('user_token')->accessToken;
            return response(['token' => $token], 200);
        }

        // Return an error
        return response('Invalid login details', 401);
    }

    public function user(Request $request)
    {
        // Return the user
        return response($request->user(), 200);
    }

    public function logout()
    {
        // Log the user out
        auth()->user()->tokens()->delete();

        // Return a message
        return response('Logged out', 200);
    }
}
