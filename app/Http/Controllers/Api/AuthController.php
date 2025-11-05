<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
         'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'password' => bcrypt($request->password),
         ]);

        $otp = rand(100000, 999999);
        $user->update([
        'otp' => $otp,
        'otp_expires_at' => now()->addMinutes(10),
        ]);

    // Send OTP via email or SMS here
       return response()->json(['message' => 'User registered. OTP sent.']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
    public function verifyOtp(Request $request) {
    $request->validate(['email' => 'required|email', 'otp' => 'required']);
    $user = User::where('email', $request->email)
        ->where('otp', $request->otp)
        ->where('otp_expires_at', '>', now())
        ->first();

    if (!$user) return response()->json(['message' => 'Invalid or expired OTP'], 400);

        $user->update(['otp' => null, 'otp_expires_at' => null, 'email_verified_at' => now()]);
        return response()->json(['message' => 'OTP verified successfully']);
    }
    
}
