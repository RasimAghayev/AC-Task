<?php

namespace App\Http\Controllers\Tasks\Repositories;

use App\Http\Controllers\Tasks\
{
    DTOs\TaskDTO,
    Enums\TaskPriority,
    Enums\TaskRepeatTypeEnum,
    Enums\TaskStatus,
    Models\Task
};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TaskRepository implements TaskRepositoryInterface
{
    public function __construct(
        protected Task $taskModel
    ){}

    /**
     * Get filtered tasks with pagination
     *
     * @param array $queryItems
     * @param bool $includeTags
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFilteredTasks(array $queryItems, bool $includeTags, int $perPage): LengthAwarePaginator
    {
        $queryItems['user_id'] = auth()->id();
        $query = $this->taskModel->where($queryItems);

        if ($includeTags) {
            $query->with(['user']);
        }

        $maxPerPage = 100;
        $perPage = min($perPage, $maxPerPage);

        return $query->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->appends(request()->query());
    }

    /**
     * Create new task
     *
     * @param TaskDTO $taskDTO
     * @return Task
     */
    public function create(TaskDTO $taskDTO): Task
    {
        return $this->taskModel->create($taskDTO->toArray());
    }

    /**
     * Find task by ID
     *
     * @param int $id
     * @return Task|string
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): Task|string
    {
        $task = $this->taskModel->find($id);

        if (!$task) {
            return "Task not found with ID: {$id}";
        }

        return $task;
    }

    /**
     * Update existing task
     *
     * @param int $id
     * @param TaskDTO $taskDTO
     * @return Task
     */
    public function update(int $id, TaskDTO $taskDTO): Task
    {
        $task = $this->findOrFail($id);
        $task->update($taskDTO->toArray());

        return $task->fresh();
    }

    /**
     * Delete task
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        $task = $this->findOrFail($id);
        $task->delete();
    }

    /**
     * Get task statistics report
     *
     * @param array $queryItems
     * @param bool $includeTags
     * @param int|null $userId
     * @return array
     */
    public function getTasksReport(array $queryItems, bool $includeTags, ?int $userId = null): array
    {

        $query = $this->taskModel->query();

        if (!empty($queryItems)) {
            $query->where($queryItems);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($includeTags) {
            $query->with(['user']);
        }


        $statusStats = $query->clone()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusReport = [];
        foreach (TaskStatus::cases() as $status) {
            $statusReport[$status->value] = [
                'count' => $statusStats[$status->value] ?? 0,
                'label' => $status->label(),
                'color' => $status->color()
            ];
        }

        $priorityStats = $query->clone()
            ->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        $priorityReport = [];
        foreach (TaskPriority::cases() as $priority) {
            $priorityReport[$priority->value] = [
                'count' => $priorityStats[$priority->value] ?? 0,
                'label' => $priority->label(),
                'color' => $priority->color()
            ];
        }

        $repeatStats = $query->clone()
            ->select('repeat_type', DB::raw('count(*) as count'))
            ->whereNotNull('repeat_type')
            ->groupBy('repeat_type')
            ->pluck('count', 'repeat_type')
            ->toArray();

        $repeatReport = [];
        foreach (TaskRepeatTypeEnum::cases() as $repeatType) {
            $repeatReport[$repeatType->value] = [
                'count' => $repeatStats[$repeatType->value] ?? 0,
                'label' => $repeatType->label(),
                'color' => $repeatType->color()
            ];
        }

        $totalTasks = $query->clone()->count();
        $completedTasks = $statusStats[TaskStatus::COMPLETED->value] ?? 0;
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

        $report = [
            'total_tasks' => $totalTasks,
            'completion_rate' => $completionRate,
            'by_status' => $statusReport,
            'by_priority' => $priorityReport,
            'by_repeat_type' => $repeatReport,
            'generated_at' => now()->toIso8601String(),
            'user_id' => $userId
        ];


        return $report;
    }
}