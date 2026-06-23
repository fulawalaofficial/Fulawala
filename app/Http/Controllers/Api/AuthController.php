<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
        
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
            'address' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'mobile' => $data['mobile'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'customer',
            'status' => 'Active',
        ]);

        if (!empty($data['address'])) {
            Address::create([
                'user_id' => $user->id,
                'address' => $data['address'],
                'city' => '',
                'state' => '',
                'pincode' => '',
                'is_default' => true,
            ]);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('mobile')->plainTextToken,
        ], 201);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->where('role', 'customer')->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid customer credentials.']]);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('mobile')->plainTextToken,
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user()->load('addresses'));
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
