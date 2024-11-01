<?php

namespace App\Http\Controllers\Auth;

use App\Http\Responses\ErrorApiResponse;
use App\Http\Responses\ErrorUnauthenticatedResponse;
use App\Http\Responses\SuccessApiResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\{
    Requests\LoginRequest,
    Requests\RegisterRequest,
    Requests\UpdateMeRequest,
    Resources\AuthResource,
    Services\AuthService,
};
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

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
        try{
            $user = $this->authService->register($request->validated());
            $token = auth('api')->login($user);
            return SuccessApiResponse::make([
                "user" =>new AuthResource($user),
                "authorization" => $this->respondWithToken($token)->original
            ]);
        }catch (Exception $ex) {
            return ErrorApiResponse::make($ex->getMessage());
        }
    }


    /**
     * Get a JWT via given credentials.
     *
     * @param LoginRequest $request
     * @return SuccessApiResponse|ErrorUnauthenticatedResponse|ErrorApiResponse
     */
    public function login(LoginRequest $request): SuccessApiResponse|ErrorUnauthenticatedResponse|ErrorApiResponse
    {
        try{
            if (!$token = auth('api')->attempt(credentials: $request->all())) {
                return ErrorUnauthenticatedResponse::make();
            }
        }catch (Exception $ex) {
            return ErrorApiResponse::make($ex->getMessage());
        }
        return SuccessApiResponse::make($this->respondWithToken($token));
    }

    /**
     * Get the authenticated User.
     *
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function me(): SuccessApiResponse|ErrorApiResponse
    {
        try{
            return SuccessApiResponse::make(
                new AuthResource(auth('api')->user())
            );
        }catch (Exception $ex) {
            return ErrorApiResponse::make($ex->getMessage());
        }
    }

    /**
     * Update the authenticated User's profile.
     *
     * @param UpdateMeRequest $request
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function updateMe(UpdateMeRequest $request): SuccessApiResponse|ErrorApiResponse
    {
        try{
            $user = $this->authService->updateUser(
                    user: auth('api')->user(),
                    data: $request->validated()
                );
            return SuccessApiResponse::make(["user" =>new AuthResource($user)]);
        }catch (Exception $ex) {
            DB::rollBack();
            return ErrorApiResponse::make($ex->getMessage());
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function logout(): SuccessApiResponse|ErrorApiResponse
    {
        try{
            auth('api')->logout();
            return SuccessApiResponse::make('Successfully logged out');
        }catch (Exception $ex) {
            return ErrorApiResponse::make($ex->getMessage());
        }
    }

    /**
     * Refresh a token.
     *
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function refresh(): SuccessApiResponse|ErrorApiResponse
    {
        try{
            auth('api')->logout();
            return SuccessApiResponse::make($this->respondWithToken(auth('api')->refresh()));
        }catch (Exception $ex) {
            return ErrorApiResponse::make($ex->getMessage());
        }
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
