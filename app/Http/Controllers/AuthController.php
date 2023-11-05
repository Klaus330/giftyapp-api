<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password']
        ]);

        $authToken = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 200,
            'data' => [
                'user' => $user,
                'token' => $authToken
            ],
            'error' => []
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (!auth()->attempt($validated)) {
            return response()->json([
                'message' => 'The given data was invalid',
                'errors' => [
                    'password' => [
                        'Invalid credentials',
                    ]
                ]
            ], 422);
        }

        $user = User::where('email', $validated['email'])->first();
        $authToken = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 200,
            'data' => [
                'user' => $user,
                'token' => $authToken
            ],
            'error' => []
        ]);
    }
}
