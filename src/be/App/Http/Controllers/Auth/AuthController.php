<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\TransactionHelper;
use App\Http\Responses\
{
    ErrorApiResponse,
    ErrorUnauthenticatedResponse,
    SuccessApiResponse
};
use App\Http\Controllers\Auth\
{
    Requests\LoginRequest,
    Requests\RegisterRequest,
    Requests\UpdateMeRequest,
    Resources\AuthResource,
    Services\AuthService,
};
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{

    /**
     * @param AuthService $authService
     */
    public function __construct(
        private readonly AuthService $authService
    ){}

    /**
     * Register a User.
     *
     * @param RegisterRequest $request
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function register(RegisterRequest $request): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $user = $this->authService->register($request->validated());
            $token = auth('api')->login($user);
            return [
                "user" =>new AuthResource($user),
                "authorization" => $this->respondWithToken($token)->original
            ];
        });
    }


    /**
     * Get a JWT via given credentials.
     *
     * @param LoginRequest $request
     * @return SuccessApiResponse|ErrorUnauthenticatedResponse|ErrorApiResponse
     */
    public function login(LoginRequest $request): SuccessApiResponse|ErrorUnauthenticatedResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            try{
                if (!$token = auth('api')->attempt(credentials: $request->all())) {
                    return ErrorUnauthenticatedResponse::make();
                }
            }catch (Exception $ex) {
                return ErrorApiResponse::make($ex->getMessage());
            }
            return $this->respondWithToken($token);
        });
    }

    /**
     * Get the authenticated User.
     *
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function me(): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $user = auth('api')->user();
            if (!$user) {
                return ErrorApiResponse::make('Unauthorized', 401);
            }
            return new AuthResource($user);
        });
    }

    /**
     * Update the authenticated User's profile.
     *
     * @param UpdateMeRequest $request
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function updateMe(UpdateMeRequest $request): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $user = $this->authService->updateUser(
                    user: auth('api')->user(),
                    data: $request->validated()
                );
            return ["user" =>new AuthResource($user)];
        });
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function logout(): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            auth('api')->logout();
            return 'Successfully logged out';
        });
    }

    /**
     * Refresh a token.
     *
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function refresh(): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            auth('api')->logout();
            return $this->respondWithToken(auth('api')->refresh());
        });
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
