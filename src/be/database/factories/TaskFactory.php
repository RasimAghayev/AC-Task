<?php

namespace Database\Factories;

use App\Http\Controllers\Tasks\Models\Task;
use App\Http\Controllers\Tasks\Enums\{
    TaskRepeatTypeEnum,
    TaskStatus,
    TaskPriority
};
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * @throws \DateMalformedStringException
     */
    public function definition(): array
    {
        $dueDate = fake()->dateTimeBetween('now', '+3 months');
        $repeatTypes = TaskRepeatTypeEnum::cases();
        $repeatType = fake()->randomElement($repeatTypes);

        $repeatEndDate = null;
        if ($repeatType !== null && $repeatType !== TaskRepeatTypeEnum::CUSTOM) {
            $repeatEndDate = $repeatType->getNextExecutionDate(new DateTime($dueDate->format('Y-m-d')));
        }

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'due_date' => $dueDate,
            'status' => fake()->randomElement(TaskStatus::cases())->value,
            'priority' => fake()->randomElement(TaskPriority::cases())->value,
            'repeat_type' => $repeatType?->value,
            'repeat_end_date' => $repeatEndDate,
            'tags' => fake()->randomElements(['work', 'personal', 'shopping', 'health', 'family', 'study'], rand(1, 3)),
            'user_id' => null,
        ];
    }
}