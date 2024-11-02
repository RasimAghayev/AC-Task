<?php

namespace Database\Seeders;

use App\Http\Controllers\Tasks\Models\Task;
use App\Models\User;
use App\Http\Controllers\Tasks\Enums\{TaskStatus, TaskPriority};
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        User::all()->each(function ($user) {
            $taskCount = rand(60, 100);

            $statusDistribution = [
                'pending' => 40,
                'in_progress' => 30,
                'completed' => 20,
                'cancelled' => 10
            ];

            $priorityDistribution = [
                'low' => 20,
                'medium' => 50,
                'high' => 20,
                'urgent' => 10
            ];

            foreach ($statusDistribution as $statusValue => $percentage) {
                $count = (int) ($taskCount * $percentage / 100);
                $status = TaskStatus::from($statusValue);

                foreach ($priorityDistribution as $priorityValue => $priorityPercentage) {
                    $priorityCount = (int) ($count * $priorityPercentage / 100);
                    $priority = TaskPriority::from($priorityValue);

                    Task::factory()
                        ->count($priorityCount)
                        ->state(function (array $attributes) use ($status, $priority) {
                            return [
                                'status' => $status->value,
                                'priority' => $priority->value
                            ];
                        })
                        ->create(['user_id' => $user->id]);
                }
            }
        });
    }
}