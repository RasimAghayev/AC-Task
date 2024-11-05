<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 *  @OA\PathItem(path="app"),
 *
 * @OA\Schema(
 *        schema="403Error",
 *        type="object",
 *        @OA\Property(property="result", type="array", @OA\Items(), example={}),
 *        @OA\Property(property="error", type="string", example="User does not have the right roles.")
 *    )
 *
 * @OA\Schema(
 *       schema="401Error",
 *       type="object",
 *       @OA\Property(property="result", type="array", @OA\Items(), example={}),
 *       @OA\Property(property="error", type="string", example="Authentication error.")
 *   )
 *
 * @OA\Schema(
 *        schema="500Error",
 *        type="object",
 *        @OA\Property(property="result", type="array", @OA\Items(), example={}),
 *        @OA\Property(property="error", type="string", example="Error string")
 *    )
 *
 *
 * @OA\Schema(
 *     description="Response with authorized user data",
 *     schema="authData",
 *     type="object",
 *     allOf = {
 *         @OA\Schema(
 *             @OA\Property(
 *              property="result",
 *              type="object",
 *              @OA\Property(
 *                  property="access_token",
 *                  type="string",
 *                  example="eyJ0eXAiOiJKV1QiLCJI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.dF2UutsiZaLlPB6Mo8wk3Ix3y4oXFMz8GeFX1RM53c8",
 *                  description="JWT access token for user authorization."
 *              ),
 *              @OA\Property(
 *                  property="token_type",
 *                  type="string",
 *                  example="bearer",
 *                  description="Token type, usually 'bearer'."
 *              ),
 *              @OA\Property(
 *                  property="expires_in",
 *                  type="integer",
 *                  example=2073600,
 *                  description="Token expiration time in seconds."
 *              )
 *          ),
 *          @OA\Property(
 *              property="error",
 *              type="string",
 *              example=null,
 *              nullable=true,
 *              description="Error field, will be null in case of successful response."
 *          )
 *         ),
 *     }
 * )
 *
 */

class SwaggerController extends Controller
{
    //
}