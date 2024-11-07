<?php
declare(strict_types=1);

namespace App\Http\Controllers\Tasks;

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
     * @param Request $request
     * @return TaskCollection|ErrorApiResponse|SuccessApiResponse
     */

    /**
     * @OA\Get(
     *      path="/api/tasks",
     *      operationId="getTasksList",
     *      tags={"Tasks"},
     *      summary="Get list of tasks",
     *      description="Returns list of tasks",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="title", type="string"),
     *                  @OA\Property(property="description", type="string"),
     *                  @OA\Property(property="status", type="string"),
     *                  @OA\Property(property="created_at", type="string", format="datetime"),
     *                  @OA\Property(property="updated_at", type="string", format="datetime")
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request): TaskCollection|SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $this->authorize('viewAny', Task::class);
            $tasks = $this->taskService->getTasks(
                request: $request,
                includeTags: $request->boolean('include_tags'),
                perPage: $request->integer('per_page', 15)
            );
            return [
                'message' => 'Task successfully full list.',
                'data' => new TaskCollection($tasks)
            ];
        });
    }

    /**
     * Create new task
     *
     * @param StoreTaskRequest $request
     * @return ErrorApiResponse|ErrorValidationResponse|SuccessApiResponse
     */
    public function store(StoreTaskRequest $request): SuccessApiResponse|ErrorApiResponse|ErrorValidationResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $this->authorize('update', Task::class);
            $task = $this->taskService->createTask($request->toDTO());

            return [
                'message' => 'Task successfully created',
                'data' => new TaskResource($task)
            ];
        });
    }

    /**
     * Get task by ID
     *
     * @param int $id
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function show(int $id): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $this->authorize('view', Task::class);
            $task = $this->taskService->getTaskById($id);
            return [
                'message' => 'Task successfully show: '.$id,
                'data' => new TaskResource($task)
            ];
        });
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
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $this->authorize('update', Task::class);
            $task = $this->taskService->updateTask($id, $request->toDTO());

            return [
                'message' => 'Task successfully updated',
                'data' => new TaskResource($task)
            ];
        });
    }

    /**
     * Delete task
     *
     * @param int $id
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function destroy(int $id): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $this->authorize('delete', Task::class);
            $task = $this->taskService->getTaskById($id);
            $this->taskService->deleteTask($id);

            return [
                'message' => 'Task successfully deleted'
            ];
        });
    }

    /**
     * Get task statistics report
     *
     * @param Request $request
     * @return SuccessApiResponse|ErrorApiResponse
     */
    public function getTasksReport(Request $request): SuccessApiResponse|ErrorApiResponse
    {
        return TransactionHelper::handleWithTransaction(function () use ($request) {
            $this->authorize('viewAny', Task::class);
            $userId = $request->boolean('all') ? null : auth('api')->id();
            $report = $this->taskService->getTasksReport(
                request:$request,
                includeTags: $request->boolean('include_tags'),
                userId: $userId
            );

            return [
                'message' => 'Task report generated successfully',
                'data' => $report
            ];
        });
    }

}