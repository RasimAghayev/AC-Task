<?php

namespace App\Http\Controllers\Tasks\Repositories;

use App\Http\Controllers\Tasks\DTOs\TaskDTO;
use App\Http\Controllers\Tasks\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    /**
     * Get filtered tasks with pagination
     */
    public function getFilteredTasks(array $queryItems, bool $includeTags, int $perPage): LengthAwarePaginator;

    /**
     * Create new task
     */
    public function create(TaskDTO $taskDTO): Task;

    /**
     * Find task by ID
     */
    public function findOrFail(int $id): Task|string;

    /**
     * Update existing task
     */
    public function update(int $id, TaskDTO $taskDTO): Task;

    /**
     * Delete task
     */
    public function delete(int $id): void;

    /**
     * Get task statistics report
     *
     * @return void
     */
    public function getTasksReport(array $queryItems, bool $includeTags, ?int $userId = null): array;
}
