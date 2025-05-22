<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tasks\Services;

use App\Http\Controllers\Tasks\DTOs\TaskDTO;
use App\Http\Controllers\Tasks\Filters\TaskFilters;
use App\Http\Controllers\Tasks\Models\Task;
use App\Http\Controllers\Tasks\Repositories\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TaskService implements TaskServiceInterface
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository
    ) {}

    /**
     * Get filtered tasks with pagination
     *
     * @param Request $request
     * @param bool $includeUser
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getTasks(Request $request, bool $includeUser, int $perPage): LengthAwarePaginator
    {
        $filter = new TaskFilters;
        $queryItems = $filter->transform($request);

        return $this->taskRepository->getFilteredTasks(
            queryItems: $queryItems,
            includeUser: $includeUser,
            perPage: $perPage
        );
    }

    public function createTask(TaskDTO $taskDTO): Task
    {
        return DB::transaction(function () use ($taskDTO) {
            return $this->taskRepository->create($taskDTO);
        });
    }

    public function getTaskById(int $id): Task
    {
        return $this->taskRepository->findOrFail($id);
    }

    public function updateTask(int $id, TaskDTO $taskDTO): Task
    {
        return DB::transaction(function () use ($id, $taskDTO) {
            return $this->taskRepository->update($id, $taskDTO);
        });
    }

    public function deleteTask(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->taskRepository->delete($id);
        });
    }

    /**
     * @param Request $request
     * @param bool $includeUser
     * @param int|null $userId
     * @return array
     */
    public function getTasksReport(Request $request, bool $includeUser, ?int $userId = null): array
    {
        $filter = new TaskFilters;
        $queryItems = $filter->transform($request);

        return $this->taskRepository->getTasksReport(
            queryItems: $queryItems,
            includeUser: $includeUser,
            userId: $userId
        );
    }
}
