<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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

        Log::channel('daily')->info('REGISTER: User with name ' . $request->name . ' and email ' . $request->email . ' requested');

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        // Create a token for the user
        $token = $user->createToken('user_token')->accessToken;

        Log::channel('daily')->info('REGISTER: User ' . $user . ' successful');

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

        Log::channel('daily')->info('LOGIN: User with email ' . $request->email . ' requested');

        // Attempt to log the user in
        if (auth()->attempt($request->only('email', 'password'))) {
            // Return the user
            $user = auth()->user();
            $token = $user->createToken('user_token')->accessToken;

            Log::channel('daily')->info('LOGIN: User ' . $user . ' successful');

            return response(['token' => $token], 200);
        }

        $message = 'User with email ' . $request->email . ' login failed';

        Log::channel('daily')->warning('LOGIN: ' . $message);

        // Return an error
        return response($message, 401);
    }

    public function user(Request $request)
    {
        // Return the user
        Log::channel('daily')->info('USER: User ' . $request->user()->email . ' requested');

        return response($request->user(), 200);
    }

    public function logout(Request $request)
    {
        // Delete the token used for authentication
        $request->user()->token()->revoke();

        $message = 'User ' . $request->user()->email . ' logged out';
        Log::channel('daily')->info('LOGOUT: ' . $message);

        // Return a message
        return response($message, 200);
    }
}
