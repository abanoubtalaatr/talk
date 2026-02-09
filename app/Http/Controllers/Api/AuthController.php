<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Resolve referral user
        $referralUserId = null;
        if (!empty($validated['referral_username'])) {
            $referrer = User::where('username', $validated['referral_username'])->first();
            $referralUserId = $referrer?->id;
        }

        $user = User::create([
            'username' => $validated['username'],
            'password' => $validated['password'],
            'referral_user_id' => $referralUserId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'mac_address' => $validated['mac_address'] ?? null,
        ]);

        // Assign default categories (Love, Friendship, Dreams)
        $defaultCategories = Category::whereIn('name', ['Love', 'Friendship', 'Dreams'])->pluck('id');
        $user->categories()->attach($defaultCategories);

        // Log registration activity
        $this->activityLogService->logRegistration($user, $request);

        // Generate API token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user' => new UserResource($user->load('categories')),
            'token' => $token,
        ], 201);
    }

    /**
     * Login an existing user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('username', $validated['username'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Update last login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_login_user_agent' => $request->userAgent(),
        ]);

        // Log login activity
        $this->activityLogService->logLogin($user, $request);

        // Generate API token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => new UserResource($user->load('categories')),
            'token' => $token,
        ]);
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
