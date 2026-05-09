<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * Creates a new user account and returns an authentication token immediately.
     * No email verification required.
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
     *   "message": "تم إنشاء الحساب بنجاح.",
     *   "user": {"id": 1, "name": "Ahmad Ali", "email": "user@example.com"},
     *   "token": "1|abc123..."
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
            'name'              => $request->name,
            'email'             => $request->email,
            'phone'             => $request->phone,
            'password'          => Hash::make($request->password),
            'role'              => 'user',
            'email_verified_at' => now(), // Auto-verify — no OTP needed
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'تم إنشاء الحساب بنجاح.',
            'user'    => $user,
            'token'   => $token,
        ], 201);
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
     * @response 422 {"message": "البريد الإلكتروني أو كلمة المرور غير صحيحة."}
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
                'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة.'],
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
     * @response 200 {"message": "تم تسجيل الخروج بنجاح."}
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح.',
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
        $user = $request->user();

        return response()->json([
            'data' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'phone'  => $user->phone,
                'role'   => $user->role,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ],
        ]);
    }

    /**
     * Update User Profile
     *
     * Updates the currently authenticated user's profile information.
     *
     * @group Authentication
     * @authenticated
     *
     * @bodyParam name string Optional. The user's new name.
     * @bodyParam email string Optional. The user's new email.
     * @bodyParam phone string Optional. The user's new phone.
     * @bodyParam password string Optional. New password.
     * @bodyParam password_confirmation string Optional. Required if password is provided.
     * @bodyParam avatar file Optional. The user's new avatar image.
     *
     * @response 200 {"message": "تم تحديث الملف الشخصي بنجاح.", "data": {...}}
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'phone'    => 'sometimes|required|string|unique:users,phone,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'avatar'   => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return response()->json([
            'message' => 'تم تحديث الملف الشخصي بنجاح.',
            'data'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'phone'  => $user->phone,
                'role'   => $user->role,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ],
        ]);
    }
}
