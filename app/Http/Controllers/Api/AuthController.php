<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'phone' => 'required|string|unique:users',
                'password' => 'required|min:6',
                'password_confirmation' => 'required|same:password',
                'user_priviliages' => 'required|string',
            ]);

            Log::info('Registration attempt', ['email' => $request->email]);

            // Generate OTP
            $otp = rand(100000, 999999);
            Log::info('OTP generated', ['otp' => $otp, 'email' => $request->email]);

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => bcrypt($validated['password']),
                'user_priviliages' => $validated['user_priviliages'],
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10),
            ]);

            Log::info('User created successfully', ['user_id' => $user->id]);

            // Send OTP notification
            try {
                $user->notify(new \App\Notifications\SendOtpNotification($otp));
                Log::info('OTP notification sent', ['user_id' => $user->id, 'email' => $user->email]);
            } catch (Exception $e) {
                Log::error('Failed to send OTP notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'message' => 'User registered but failed to send OTP email. Please contact support.',
                    'error' => 'Email sending failed: ' . $e->getMessage(),
                    'user_id' => $user->id
                ], 500);
            }

            return response()->json([
                'message' => 'User registered successfully. Please check your email for OTP.',
                'user_id' => $user->id,
                'email' => $user->email
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Registration validation failed', [
                'errors' => $e->errors(),
                'input' => $request->except('password', 'password_confirmation')
            ]);
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except('password', 'password_confirmation')
            ]);

            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            Log::info('Login attempt', ['email' => $request->email]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                Log::warning('Login failed - user not found', ['email' => $request->email]);
                return response()->json([
                    'message' => 'Invalid credentials',
                    'error' => 'User not found'
                ], 401);
            }

            if (!Hash::check($validated['password'], $user->password)) {
                Log::warning('Login failed - incorrect password', ['email' => $request->email]);
                return response()->json([
                    'message' => 'Invalid credentials',
                    'error' => 'Incorrect password'
                ], 401);
            }

            // Check if email is verified
            if (is_null($user->email_verified_at)) {
                Log::warning('Login failed - email not verified', ['email' => $request->email]);
                return response()->json([
                    'message' => 'Please verify your email first',
                    'error' => 'Email not verified'
                ], 403);
            }

            $token = $user->createToken('api_token')->plainTextToken;

            Log::info('Login successful', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Login validation failed', ['errors' => $e->errors()]);
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                Log::warning('Logout failed - no authenticated user');
                return response()->json([
                    'message' => 'No authenticated user found'
                ], 401);
            }

            Log::info('Logout attempt', ['user_id' => $user->id]);

            $request->user()->currentAccessToken()->delete();

            Log::info('Logout successful', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Logged out successfully'
            ], 200);

        } catch (Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'otp' => 'required|string|size:6'
            ]);

            Log::info('OTP verification attempt', [
                'email' => $request->email,
                'otp' => $request->otp
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                Log::warning('OTP verification failed - user not found', ['email' => $request->email]);
                return response()->json([
                    'message' => 'User not found',
                    'error' => 'No user exists with this email'
                ], 404);
            }

            if (is_null($user->otp)) {
                Log::warning('OTP verification failed - no OTP set', ['email' => $request->email]);
                return response()->json([
                    'message' => 'No OTP found for this user',
                    'error' => 'OTP may have already been used or never generated'
                ], 400);
            }

            if ($user->otp !== $validated['otp']) {
                Log::warning('OTP verification failed - incorrect OTP', [
                    'email' => $request->email,
                    'provided_otp' => $request->otp,
                    'stored_otp' => $user->otp
                ]);
                return response()->json([
                    'message' => 'Invalid OTP',
                    'error' => 'The OTP you entered is incorrect'
                ], 400);
            }

            if ($user->otp_expires_at < now()) {
                Log::warning('OTP verification failed - expired OTP', [
                    'email' => $request->email,
                    'expired_at' => $user->otp_expires_at
                ]);
                return response()->json([
                    'message' => 'Expired OTP',
                    'error' => 'The OTP has expired. Please request a new one.'
                ], 400);
            }

            // All checks passed - verify the user
            $user->update([
                'otp' => null,
                'otp_expires_at' => null,
                'email_verified_at' => now()
            ]);

            Log::info('OTP verified successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'OTP verified successfully',
                'email_verified' => true
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('OTP verification validation failed', ['errors' => $e->errors()]);
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('OTP verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'OTP verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Helper method to resend OTP (bonus feature)
    public function resendOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);

            Log::info('Resend OTP attempt', ['email' => $request->email]);

            $user = User::where('email', $validated['email'])->first();

            if ($user->email_verified_at) {
                return response()->json([
                    'message' => 'Email already verified',
                    'error' => 'This email is already verified'
                ], 400);
            }

            // Generate new OTP
            $otp = rand(100000, 999999);
            
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10)
            ]);

            // Send OTP
            try {
                $user->notify(new \App\Notifications\SendOtpNotification($otp));
                Log::info('OTP resent successfully', ['user_id' => $user->id]);
            } catch (Exception $e) {
                Log::error('Failed to resend OTP', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                
                return response()->json([
                    'message' => 'Failed to send OTP email',
                    'error' => $e->getMessage()
                ], 500);
            }

            return response()->json([
                'message' => 'OTP resent successfully. Please check your email.',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Resend OTP failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to resend OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function forgotPassword(Request $request)
{
    try {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        Log::info('Forgot password attempt', ['email' => $request->email]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            Log::warning('Forgot password - user not found', ['email' => $request->email]);
            return response()->json([
                'message' => 'User not found',
                'error' => 'No account exists with this email'
            ], 404);
        }

        // Generate OTP for password reset
        $otp = rand(100000, 999999);
        
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        Log::info('Password reset OTP generated', [
            'user_id' => $user->id,
            'otp' => $otp
        ]);

        // Send OTP via email
        try {
            $user->notify(new \App\Notifications\SendOtpNotification($otp, 'password_reset'));
            Log::info('Password reset OTP sent', ['user_id' => $user->id]);
        } catch (Exception $e) {
            Log::error('Failed to send password reset OTP', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Failed to send OTP email',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'OTP sent to your email. Please check your inbox.',
            'email' => $user->email
        ], 200);

    } catch (ValidationException $e) {
        Log::warning('Forgot password validation failed', ['errors' => $e->errors()]);
        
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);

    } catch (Exception $e) {
        Log::error('Forgot password failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'message' => 'Failed to process forgot password request',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function resetPassword(Request $request)
{
    try {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password'
        ]);

        Log::info('Reset password attempt', ['email' => $request->email]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            Log::warning('Reset password - user not found', ['email' => $request->email]);
            return response()->json([
                'message' => 'User not found',
                'error' => 'No account exists with this email'
            ], 404);
        }

        if (is_null($user->otp)) {
            Log::warning('Reset password - no OTP set', ['email' => $request->email]);
            return response()->json([
                'message' => 'No OTP found',
                'error' => 'Please request a password reset first'
            ], 400);
        }

        if ($user->otp !== $validated['otp']) {
            Log::warning('Reset password - incorrect OTP', [
                'email' => $request->email,
                'provided_otp' => $request->otp,
                'stored_otp' => $user->otp
            ]);
            return response()->json([
                'message' => 'Invalid OTP',
                'error' => 'The OTP you entered is incorrect'
            ], 400);
        }

        if ($user->otp_expires_at < now()) {
            Log::warning('Reset password - expired OTP', [
                'email' => $request->email,
                'expired_at' => $user->otp_expires_at
            ]);
            return response()->json([
                'message' => 'Expired OTP',
                'error' => 'The OTP has expired. Please request a new one.'
            ], 400);
        }

        // All checks passed - reset password
        $user->update([
            'password' => bcrypt($validated['password']),
            'otp' => null,
            'otp_expires_at' => null
        ]);

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        Log::info('Password reset successful', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'Password reset successfully. Please login with your new password.',
            'success' => true
        ], 200);

    } catch (ValidationException $e) {
        Log::warning('Reset password validation failed', ['errors' => $e->errors()]);
        
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);

    } catch (Exception $e) {
        Log::error('Reset password failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'message' => 'Failed to reset password',
            'error' => $e->getMessage()
        ], 500);
    }
}
}