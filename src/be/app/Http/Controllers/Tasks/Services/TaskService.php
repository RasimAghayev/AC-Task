<?php
declare(strict_types = 1);
namespace App\Http\Controllers\Tasks\Services;

use App\Http\Controllers\Tasks\{
    DTOs\TaskDTO,
    Filters\TaskFilters,
    Repositories\TaskRepositoryInterface,
    Models\Task
};
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService implements TaskServiceInterface
{
    /**
     * @param TaskRepositoryInterface $taskRepository
     */
    public function __construct(
        protected TaskRepositoryInterface $taskRepository
    ){}

    /**
     * Get filtered tasks with pagination
     *
     * @param Request $request
     * @param bool $includeTags
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getTasks(Request $request, bool $includeTags, int $perPage): LengthAwarePaginator
    {
        $filter = new TaskFilters();
        $queryItems = $filter->transform($request);

        return $this->taskRepository->getFilteredTasks(
            queryItems: $queryItems,
            includeTags: $includeTags,
            perPage: $perPage
        );
    }

    /**
     * @param TaskDTO $taskDTO
     * @return Task
     */
    public function createTask(TaskDTO $taskDTO): Task
    {
        return $this->taskRepository->create($taskDTO);
    }

    /**
     * @param int $id
     * @return Task
     */
    public function getTaskById(int $id): Task
    {
        return $this->taskRepository->findOrFail($id);
    }

    /**
     * @param int $id
     * @param TaskDTO $taskDTO
     * @return Task
     */
    public function updateTask(int $id, TaskDTO $taskDTO): Task
    {
        return $this->taskRepository->update($id, $taskDTO);
    }

    /**
     * @param int $id
     * @return void
     */
    public function deleteTask(int $id): void
    {
        $this->taskRepository->delete($id);
    }

    /**
     * @param Request $request
     * @param bool $includeTags
     * @param int|null $userId
     * @return array
     */
    public function getTasksReport(Request $request, bool $includeTags, ?int $userId = null): array
    {
        $filter = new TaskFilters();
        $queryItems = $filter->transform($request);

        return $this->taskRepository->getTasksReport(
            queryItems: $queryItems,
            includeTags: $includeTags,
            userId: $userId
        );
    }
}