<?php
declare(strict_types=1);

namespace App\Http\Controllers\Tasks;

use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
use App\Http\Controllers\Tasks\{Models\Task,
    Requests\StoreTaskRequest,
    Requests\UpdateTaskRequest,
    Resources\TaskCollection,
    Resources\TaskResource,
    Services\TaskServiceInterface};
use App\Http\Responses\{ErrorApiResponse, ErrorValidationResponse, SuccessApiResponse};
use Illuminate\Http\Request;
use Throwable;

class TaskController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private readonly TaskServiceInterface $taskService
    ){}

    /**
     * Get list of tasks
     *
     * @OA\Get(
     *     path="/api/v1/tasks",
     *     operationId="getTasksList",
     *     tags={"Tasks"},
     *     summary="Get list of tasks",
     *     description="Returns paginated list of tasks with optional filtering",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="include_tags",
     *         in="query",
     *         description="Include tags in response",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TaskCollection")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticatedResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @param Request $request
     * @return TaskCollection|ErrorApiResponse|SuccessApiResponse
     */
    public function index(Request $request): TaskCollection|SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $this->authorize("viewAny", Task::class);
            $tasks = $this->taskService->getTasks(
                request: $request,
                includeTags: $request->boolean("include_tags"),
                perPage: $request->integer("per_page", 15)
            );
            return new TaskCollection($tasks);
        });
    }

    /**
     * Create new task
     *
     * @OA\Post(
     *     path="/api/v1/tasks",
     *     operationId="createTask",
     *     tags={"Tasks"},
     *     summary="Create a new task",
     *     description="Create a new task with the provided data",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreTaskRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task successfully created"),
     *             @OA\Property(property="data", ref="#/components/schemas/TaskResource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticatedResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorValidationResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @param StoreTaskRequest $request
     * @return ErrorApiResponse|ErrorValidationResponse|SuccessApiResponse
     */
    public function store(StoreTaskRequest $request): SuccessApiResponse|ErrorApiResponse|ErrorValidationResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $this->authorize("createAny", Task::class);
            $task = $this->taskService->createTask($request->toDTO());

            return [
                "message" => "Task successfully created",
                "data" => new TaskResource($task)
            ];
        });
    }

    /**
     * Get task by ID
     *
     * @OA\Get(
     *     path="/api/v1/tasks/{id}",
     *     operationId="getTaskById",
     *     tags={"Tasks"},
     *     summary="Get task by ID",
     *     description="Returns a single task by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task successfully show: 1"),
     *             @OA\Property(property="data", ref="#/components/schemas/TaskResource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticatedResponse")),
     *     @OA\Response(response=404, description="Task not found", @OA\JsonContent(ref="#/components/schemas/ErrorNotFoundResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @param int $id
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function show(int $id): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($id) {
            $task = $this->taskService->getTaskById($id);
            $this->authorize("view", Task::class);
            return [
                "message" => "Task successfully show: ".$id,
                "data" => new TaskResource($task)
            ];
        });
    }

    /**
     * Update existing task
     *
     * @OA\Put(
     *     path="/api/v1/tasks/{id}",
     *     operationId="updateTask",
     *     tags={"Tasks"},
     *     summary="Update an existing task",
     *     description="Update a task with the provided data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateTaskRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task successfully updated"),
     *             @OA\Property(property="data", ref="#/components/schemas/TaskResource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticatedResponse")),
     *     @OA\Response(response=404, description="Task not found", @OA\JsonContent(ref="#/components/schemas/ErrorNotFoundResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorValidationResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @param UpdateTaskRequest $request
     * @param int $id
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function update(UpdateTaskRequest $request, int $id): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request,$id) {
            $task = $this->taskService->getTaskById($id);
            $this->authorize("update", $task);
            $updatedTask = $this->taskService->updateTask($id, $request->toDTO());

            return [
                "message" => "Task successfully updated",
                "data" => new TaskResource($updatedTask)
            ];
        });
    }

    /**
     * Delete task
     *
     * @OA\Delete(
     *     path="/api/v1/tasks/{id}",
     *     operationId="deleteTask",
     *     tags={"Tasks"},
     *     summary="Delete a task",
     *     description="Delete a task by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task successfully deleted")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticatedResponse")),
     *     @OA\Response(response=404, description="Task not found", @OA\JsonContent(ref="#/components/schemas/ErrorNotFoundResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @param int $id
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function destroy(int $id): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($id) {
            $task = $this->taskService->getTaskById($id);
            $this->authorize("delete", $task);
            $this->taskService->deleteTask($id);

            return [
                "message" => "Task successfully deleted"
            ];
        });
    }

    /**
     * Get task statistics report
     *
     * @OA\Get(
     *     path="/api/v1/tasks/reports",
     *     operationId="getTasksReport",
     *     tags={"Tasks"},
     *     summary="Get task statistics report",
     *     description="Returns statistics report for tasks",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="all",
     *         in="query",
     *         description="Include all users tasks (admin only)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="include_tags",
     *         in="query",
     *         description="Include tags in report",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Report generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task report generated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticatedResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServerErrorResponse"))
     * )
     *
     * @param Request $request
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function getTasksReport(Request $request): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $this->authorize("viewAny", Task::class);
            $userId = $request->boolean("all") ? null : auth("api")->id();
            $report = $this->taskService->getTasksReport(
                request:$request,
                includeTags: $request->boolean("include_tags"),
                userId: $userId
            );

            return [
                "message" => "Task report generated successfully",
                "data" => $report
            ];
        });
    }

}
