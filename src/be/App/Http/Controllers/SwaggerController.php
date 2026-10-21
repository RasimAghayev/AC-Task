<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Task API Documentation",
 *     version="1.0.0",
 *     description="API documentation for Task Management System"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST ?: "http://localhost/api/v1",
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

/**
 * Error response schemas
 */

/**
 * @OA\Schema(
 *     schema="ErrorApiResponse",
 *     type="object",
 *     required={"error"},
 *
 *     @OA\Property(property="error", type="string", example="Error message"),
 *     @OA\Property(property="message", type="string", example="Additional error details"),
 *     @OA\Property(property="code", type="integer", example=500)
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorUnauthenticatedResponse",
 *     type="object",
 *     required={"error"},
 *
 *     @OA\Property(property="error", type="string", example="Unauthenticated"),
 *     @OA\Property(property="message", type="string", example="User not authenticated"),
 *     @OA\Property(property="code", type="integer", example=401)
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorValidationResponse",
 *     type="object",
 *     required={"error"},
 *
 *     @OA\Property(property="error", type="string", example="Validation failed"),
 *     @OA\Property(property="message", type="string", example="The given data was invalid"),
 *     @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}}),
 *     @OA\Property(property="code", type="integer", example=422)
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorNotFoundResponse",
 *     type="object",
 *     required={"error"},
 *
 *     @OA\Property(property="error", type="string", example="Not found"),
 *     @OA\Property(property="message", type="string", example="Resource not found"),
 *     @OA\Property(property="code", type="integer", example=404)
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorInternalServerErrorResponse",
 *     type="object",
 *     required={"error"},
 *
 *     @OA\Property(property="error", type="string", example="Internal server error"),
 *     @OA\Property(property="message", type="string", example="An unexpected error occurred"),
 *     @OA\Property(property="code", type="integer", example=500)
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorMethodNotAllowedResponse",
 *     type="object",
 *     required={"error"},
 *
 *     @OA\Property(property="error", type="string", example="Method not allowed"),
 *     @OA\Property(property="message", type="string", example="The requested method is not allowed for this resource"),
 *     @OA\Property(property="code", type="integer", example=405)
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorTooManyAttemptsResponse",
 *     type="object",
 *     required={"error"},
 *
 *     @OA\Property(property="error", type="string", example="Too many attempts"),
 *     @OA\Property(property="message", type="string", example="Too many login attempts. Please try again later."),
 *     @OA\Property(property="code", type="integer", example=429)
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorTokenExpiredResponse",
 *     type="object",
 *     required={"error"},
 *
 *     @OA\Property(property="error", type="string", example="Token expired"),
 *     @OA\Property(property="message", type="string", example="The provided token has expired"),
 *     @OA\Property(property="code", type="integer", example=401)
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorTokenInvalidResponse",
 *     type="object",
 *     required={"error"},
 *
 *     @OA\Property(property="error", type="string", example="Token invalid"),
 *     @OA\Property(property="message", type="string", example="The provided token is invalid"),
 *     @OA\Property(property="code", type="integer", example=401)
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorTokenBlacklistedResponse",
 *     type="object",
 *     required={"error"},
 *
 *     @OA\Property(property="error", type="string", example="Token blacklisted"),
 *     @OA\Property(property="message", type="string", example="The provided token has been blacklisted"),
 *     @OA\Property(property="code", type="integer", example=401)
 * )
 */

/**
 * Success response schemas
 */

/**
 * @OA\Schema(
 *     schema="SuccessApiResponse",
 *     type="object",
 *
 *     @OA\Property(property="message", type="string", example="Operation successful"),
 *     @OA\Property(property="data", type="object")
 * )
 */

/**
 * Auth schemas
 */

/**
 * @OA\Schema(
 *     schema="AuthResource",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="AuthCollection",
 *     type="object",
 *
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AuthResource")),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
 * )
 */

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="secret123")
 * )
 */

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     required={"name", "email", "password", "password_confirmation"},
 *
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="secret123"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="secret123")
 * )
 */

/**
 * @OA\Schema(
 *     schema="UpdateMeRequest",
 *     type="object",
 *
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com")
 * )
 */

/**
 * Task schemas
 */

/**
 * @OA\Schema(
 *     schema="TaskResource",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Complete project"),
 *     @OA\Property(property="description", type="string", example="Finish the Laravel project"),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="priority", type="string", example="high"),
 *     @OA\Property(property="due_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="tags", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="TaskCollection",
 *     type="object",
 *
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TaskResource")),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
 * )
 */

/**
 * @OA\Schema(
 *     schema="StoreTaskRequest",
 *     type="object",
 *     required={"title"},
 *
 *     @OA\Property(property="title", type="string", example="New task"),
 *     @OA\Property(property="description", type="string", example="Task description"),
 *     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="pending"),
 *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}, example="medium"),
 *     @OA\Property(property="due_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="tags", type="array", @OA\Items(type="string"))
 * )
 */

/**
 * @OA\Schema(
 *     schema="UpdateTaskRequest",
 *     type="object",
 *
 *     @OA\Property(property="title", type="string", example="Updated task"),
 *     @OA\Property(property="description", type="string", example="Updated description"),
 *     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="in_progress"),
 *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}, example="high"),
 *     @OA\Property(property="due_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="tags", type="array", @OA\Items(type="string"))
 * )
 */

/**
 * Pagination schemas
 */

/**
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *
 *     @OA\Property(property="first", type="string", format="uri", nullable=true),
 *     @OA\Property(property="last", type="string", format="uri", nullable=true),
 *     @OA\Property(property="prev", type="string", format="uri", nullable=true),
 *     @OA\Property(property="next", type="string", format="uri", nullable=true)
 * )
 */

/**
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="from", type="integer", nullable=true, example=1),
 *     @OA\Property(property="last_page", type="integer", example=10),
 *     @OA\Property(property="path", type="string", format="uri", example="http://localhost/api/v1/tasks"),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="to", type="integer", nullable=true, example=15),
 *     @OA\Property(property="total", type="integer", example=150)
 * )
 */

/**
 * Report schema
 */

/**
 * @OA\Schema(
 *     schema="TaskReport",
 *     type="object",
 *
 *     @OA\Property(property="total_tasks", type="integer", example=150),
 *     @OA\Property(property="completed_tasks", type="integer", example=45),
 *     @OA\Property(property="pending_tasks", type="integer", example=80),
 *     @OA\Property(property="in_progress_tasks", type="integer", example=20),
 *     @OA\Property(property="cancelled_tasks", type="integer", example=5),
 *     @OA\Property(property="completion_rate", type="number", format="float", example=30.0)
 * )
 */
class SwaggerController extends Controller
{
    //
}
