<?php
declare(strict_types=1);

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Tasks\{
    Requests\StoreTaskRequest,
    Requests\UpdateTaskRequest,
    Resources\TaskCollection,
    Resources\TaskResource,
    Services\TaskServiceInterface
};
use App\Http\Responses\{ErrorApiResponse, ErrorValidationResponse, SuccessApiResponse};
use Illuminate\Http\Request;
use Throwable;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskServiceInterface $taskService
    ){}

    /**
     * Get list of tasks
     *
     * @param Request $request
     * @return TaskCollection|ErrorApiResponse|SuccessApiResponse
     */
    public function index(Request $request): TaskCollection|SuccessApiResponse|ErrorApiResponse
    {
        try {
            $tasks = $this->taskService->getTasks(
                request: $request,
                includeTags: $request->boolean('include_tags'),
                perPage: $request->integer('per_page', 15)
            );

            return SuccessApiResponse::make([
                new TaskCollection($tasks)
            ]);
        } catch (Throwable $e) {
            return ErrorApiResponse::make($e->getMessage());
        }
    }

    /**
     * Create new task
     *
     * @param StoreTaskRequest $request
     * @return ErrorApiResponse|ErrorValidationResponse|SuccessApiResponse
     */
    public function store(StoreTaskRequest $request): SuccessApiResponse|ErrorApiResponse|ErrorValidationResponse
    {
        try {
            $task = $this->taskService->createTask($request->toDTO());

            return SuccessApiResponse::make([
                'message' => 'Task successfully created',
                'data' => new TaskResource($task)
            ]);
        } catch (ValidationException $e) {
            return ErrorValidationResponse::make($e->errors());
        } catch (Throwable $e) {
            return ErrorApiResponse::make($e->getMessage());
        }
    }

    /**
     * Get task by ID
     *
     * @param int $id
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function show(int $id): SuccessApiResponse|ErrorApiResponse
    {
        try {
            $task = $this->taskService->getTaskById($id);

            return SuccessApiResponse::make([
                'data' => new TaskResource($task)
            ]);
        } catch (Throwable $e) {
            return ErrorApiResponse::make($e->getMessage());
        }
    }

    /**
     * Update existing task
     *
     * @param UpdateTaskRequest $request
     * @param int $id
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function update(UpdateTaskRequest $request, int $id): SuccessApiResponse|ErrorApiResponse
    {
        try {
            $task = $this->taskService->updateTask($id, $request->toDTO());

            return SuccessApiResponse::make([
                'message' => 'Task successfully updated',
                'data' => new TaskResource($task)
            ]);
        } catch (Throwable $e) {
            return ErrorApiResponse::make($e->getMessage());
        }
    }

    /**
     * Delete task
     *
     * @param int $id
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function destroy(int $id): SuccessApiResponse|ErrorApiResponse
    {
        try {
            $this->taskService->deleteTask($id);

            return SuccessApiResponse::make([
                'message' => 'Task successfully deleted'
            ]);
        } catch (Throwable $e) {
            return ErrorApiResponse::make($e->getMessage());
        }
    }

    /**
     * Get task statistics report
     *
     * @param Request $request
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function getTasksReport(Request $request): SuccessApiResponse|ErrorApiResponse
    {
        try {
            $userId = $request->boolean('all') ? null : auth('api')->id();
            $report = $this->taskService->getTasksReport(
                request:$request,
                includeTags: $request->boolean('include_tags'),
                userId: $userId
            );

            return SuccessApiResponse::make([
                'message' => 'Task report generated successfully',
                'data' => $report
            ]);
        } catch (Throwable $e) {
            return ErrorApiResponse::make([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }

}