<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * Creates a new user account and sends an OTP code to their email for verification.
     *
     * @group Authentication
     * @unauthenticated
     *
     * @bodyParam name string required The user's full name. Example: Ahmad Ali
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam phone string required The user's phone number. Example: +963912345678
     * @bodyParam password string required The password, minimum 8 characters. Example: password123
     * @bodyParam password_confirmation string required Confirm password. Example: password123
     *
     * @response 201 {
     *   "message": "User registered successfully. Please verify your email.",
     *   "user": {"id": 1, "name": "Ahmad Ali", "email": "user@example.com"}
     * }
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => 'user',
        ]);

        $code = Str::random(6);

        VerificationCode::create([
            'email'      => $user->email,
            'code'       => $code,
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::raw("Your verification code is: {$code}", function ($message) use ($user) {
            $message->to($user->email)->subject('Verify your email');
        });

        return response()->json([
            'message' => 'User registered successfully. Please verify your email.',
            'user'    => $user,
        ], 201);
    }

    /**
     * Verify Email OTP
     *
     * Verifies the OTP code sent to the user's email address.
     *
     * @group Authentication
     * @unauthenticated
     *
     * @bodyParam email string required The user's email. Example: user@example.com
     * @bodyParam code string required The 6-character OTP code received by email. Example: A1B2C3
     *
     * @response 200 {"message": "Email verified successfully."}
     * @response 422 {"message": "The given data was invalid.", "errors": {"code": ["Invalid or expired verification code."]}}
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string',
        ]);

        $verificationCode = VerificationCode::where('email', $request->email)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verificationCode) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();
        $user->email_verified_at = now();
        $user->save();

        $verificationCode->delete();

        return response()->json([
            'message' => 'Email verified successfully.',
        ]);
    }

    /**
     * Login
     *
     * Authenticates the user and returns a Bearer token for subsequent API calls.
     *
     * @group Authentication
     * @unauthenticated
     *
     * @bodyParam email string required The user's email. Example: user1@app.com
     * @bodyParam password string required The user's password. Example: password
     *
     * @response 200 {
     *   "user": {"id": 1, "name": "Test User 1", "email": "user1@app.com"},
     *   "token": "1|abc123..."
     * }
     * @response 422 {"message": "The given data was invalid.", "errors": {"email": ["The provided credentials are incorrect."]}}
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout
     *
     * Revokes the current user's Bearer access token.
     *
     * @group Authentication
     * @authenticated
     *
     * @response 200 {"message": "Logged out successfully."}
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get Authenticated User Profile
     *
     * Returns the currently authenticated user's profile data.
     *
     * @group Authentication
     * @authenticated
     *
     * @response 200 {"id": 1, "name": "Test User 1", "email": "user1@app.com", "role": "user"}
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
