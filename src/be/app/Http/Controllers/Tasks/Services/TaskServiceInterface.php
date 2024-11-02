<?php
namespace App\Http\Controllers\Tasks\Services;

use App\Http\Controllers\Tasks\DTOs\TaskDTO;
use App\Http\Controllers\Tasks\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskServiceInterface
{
    public function getTasks(Request $request, bool $includeTags, int $perPage): LengthAwarePaginator;
    public function createTask(TaskDTO $taskDTO): Task;
    public function getTaskById(int $id): Task;
    public function updateTask(int $id, TaskDTO $taskDTO): Task;
    public function deleteTask(int $id): void;
    public function getTasksReport(Request $request, bool $includeTags, ?int $userId = null): array;
}