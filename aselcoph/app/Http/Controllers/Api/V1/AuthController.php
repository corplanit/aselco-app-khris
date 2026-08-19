<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Member registration — same fields and CreateNewUser action as the web app.
     */
    public function register(RegisterRequest $request, CreateNewUser $creator): JsonResponse
    {
        // CreateNewUser re-validates with password "confirmed"; include confirmation + terms.
        $user = $creator->create([
            ...$request->validated(),
            'password_confirmation' => $request->input('password_confirmation'),
            'terms' => $request->input('terms'),
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Registration successful. Please verify your email before logging in.',
            'email_verified' => false,
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Email/password login. Issues a Sanctum personal access token when verified.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        /** @var User|null $user */
        $user = User::query()->where('email', $request->string('email')->toString())->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('These credentials do not match our records.')],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email address is not verified.',
                'email_verified' => false,
                'user' => new UserResource($user),
            ], 403);
        }

        $deviceName = $request->string('device_name')->toString() ?: 'mobile';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Revoke the current access token (logout this device).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Revoke all personal access tokens for the authenticated user.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully.',
        ]);
    }

    /**
     * Current authenticated member profile.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json((new UserResource($request->user()))->resolve($request));
    }

    /**
     * Resend the email verification notification (public; identify by email).
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        /** @var User|null $user */
        $user = User::query()->where('email', $validated['email'])->first();

        // Always return the same message to avoid email enumeration.
        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'message' => 'If that email exists and is unverified, a verification link has been sent.',
        ]);
    }
}
