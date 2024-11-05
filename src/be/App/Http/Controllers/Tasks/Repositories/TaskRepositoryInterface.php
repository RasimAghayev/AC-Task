<?php

namespace App\Http\Controllers\Tasks\Repositories;

use App\Http\Controllers\Tasks\
{
    DTOs\TaskDTO,
    Models\Task
};
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    /**
     * Get filtered tasks with pagination
     *
     * @param array $queryItems
     * @param bool $includeTags
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFilteredTasks(array $queryItems, bool $includeTags, int $perPage): LengthAwarePaginator;

    /**
     * Create new task
     *
     * @param TaskDTO $taskDTO
     * @return Task
     */
    public function create(TaskDTO $taskDTO): Task;

    /**
     * Find task by ID
     *
     * @param int $id
     * @return Task|string
     */
    public function findOrFail(int $id): Task|string;

    /**
     * Update existing task
     *
     * @param int $id
     * @param TaskDTO $taskDTO
     * @return Task
     */
    public function update(int $id, TaskDTO $taskDTO): Task;

    /**
     * Delete task
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
    /**
     * Get task statistics report
     *
     * @param int $userId
     * @return void
     */
    public function getTasksReport(array $queryItems, bool $includeTags, ?int $userId = null): array;
}