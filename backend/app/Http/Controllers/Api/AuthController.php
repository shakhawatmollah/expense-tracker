<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            $token = $user->createToken('auth-token')->plainTextToken;

            return ApiResponse::created(
                [
                    'user' => new UserResource($user),
                    'token' => $token,
                ],
                'User registered successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Registration failed',
                ['exception' => $e->getMessage()],
                422
            );
        }
    }

    /**
     * Authenticate user and return token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            if (! $result) {
                return ApiResponse::unauthorized('Invalid credentials');
            }

            $token = $result['user']->createToken('auth-token')->plainTextToken;

            return ApiResponse::success(
                [
                    'user' => new UserResource($result['user']),
                    'token' => $token,
                ],
                'Login successful'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Login failed',
                ['exception' => $e->getMessage()],
                422
            );
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            ['user' => new UserResource($request->user())]
        );
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::message('Logged out successfully');
    }
}
