<?php
namespace App\Http\Controllers\Tasks\Services;

use App\Http\Controllers\Tasks\DTOs\TaskDTO;
use App\Http\Controllers\Tasks\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskServiceInterface
{
    /**
     * @param Request $request
     * @param bool $includeTags
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getTasks(Request $request, bool $includeTags, int $perPage): LengthAwarePaginator;

    /**
     * @param TaskDTO $taskDTO
     * @return Task
     */
    public function createTask(TaskDTO $taskDTO): Task;

    /**
     * @param int $id
     * @return Task
     */
    public function getTaskById(int $id): Task;

    /**
     * @param int $id
     * @param TaskDTO $taskDTO
     * @return Task
     */
    public function updateTask(int $id, TaskDTO $taskDTO): Task;

    /**
     * @param int $id
     * @return void
     */
    public function deleteTask(int $id): void;

    /**
     * @param Request $request
     * @param bool $includeTags
     * @param int|null $userId
     * @return array
     */
    public function getTasksReport(Request $request, bool $includeTags, ?int $userId = null): array;
}