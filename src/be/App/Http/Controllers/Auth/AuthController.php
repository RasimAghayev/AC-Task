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
use OpenApi\Annotations as OA;

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
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     operationId="authRegister",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     description="Register a new user and return JWT token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/AuthResource"),
     *             @OA\Property(property="authorization", type="object",
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorValidationResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @param RegisterRequest $request
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function register(RegisterRequest $request): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $user = $this->authService->register($request->validated());
            $token = auth("api")->login($user);
            return [
                "user" =>new AuthResource($user),
                "authorization" => $this->respondWithToken($token)->original
            ];
        });
    }


    /**
     * Get a JWT via given credentials.
     *
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     operationId="authLogin",
     *     tags={"Auth"},
     *     summary="Login user and get JWT token",
     *     description="Authenticate user with email and password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticatedResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorValidationResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @param LoginRequest $request
     * @return SuccessApiResponse|ErrorUnauthenticatedResponse|ErrorApiResponse
     */
    public function login(LoginRequest $request): SuccessApiResponse|ErrorUnauthenticatedResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            try{
                if (!$token = auth("api")->attempt(credentials: $request->all())) {
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
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     operationId="authMe",
     *     tags={"Auth"},
     *     summary="Get authenticated user",
     *     description="Get the currently authenticated user'"'"'s profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated user",
     *         @OA\JsonContent(ref="#/components/schemas/AuthResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticatedResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function me(): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function (){
            $user = auth("api")->user();
            if (!$user) {
                return ErrorApiResponse::make("Unauthorized", 401);
            }
            return new AuthResource($user);
        });
    }

    /**
     * Update the authenticated User'"'"'s profile.
     *
     * @OA\Put(
     *     path="/api/v1/auth/me",
     *     operationId="authUpdateMe",
     *     tags={"Auth"},
     *     summary="Update authenticated user profile",
     *     description="Update the currently authenticated user'"'"'s profile information",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/AuthResource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticatedResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorValidationResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @param UpdateMeRequest $request
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function updateMe(UpdateMeRequest $request): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $user = $this->authService->updateUser(
                    user: auth("api")->user(),
                    data: $request->validated()
                );
            return ["user" =>new AuthResource($user)];
        });
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     operationId="authLogout",
     *     tags={"Auth"},
     *     summary="Logout user",
     *     description="Invalidate the current JWT token",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticatedResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function logout(): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () {
            auth("api")->logout();
            return "Successfully logged out";
        });
    }

    /**
     * Refresh a token.
     *
     * @OA\Get(
     *     path="/api/v1/auth/refresh",
     *     operationId="authRefresh",
     *     tags={"Auth"},
     *     summary="Refresh JWT token",
     *     description="Get a new JWT token using the current valid token",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticatedResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @return ErrorApiResponse|SuccessApiResponse
     */
    public function refresh(): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function (){
            auth("api")->logout();
            return $this->respondWithToken(auth("api")->refresh());
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
            "access_token" => $token,
            "token_type" => "Bearer",
            "expires_in" => auth("api")->factory()->getTTL() * 60,
        ]);
    }
}
