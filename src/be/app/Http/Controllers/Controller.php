<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Task Management API Documentation",
 *      description="Task management sistem API endpoints",
 * )
 *
 * @OA\Server(
 *      url="http://localhost",
 *      description="API Server"
 * )
 *
 * @OA\PathItem(
 *      path="/api"
 * )
 *
 * @OA\SecurityScheme(
 *      type="http",
 *      description="Login with email and password to get the authentication token",
 *      name="Token based Based",
 *      in="header",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 *      securityScheme="apiAuth",
 * )
 *
 * @OA\Tag(
 *      name="Tasks",
 *      description="API Endpoints of Task Management System"
 * )
 */
abstract class Controller
{
    //
}
