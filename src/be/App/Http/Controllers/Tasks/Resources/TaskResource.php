<?php

namespace App\Http\Controllers\Tasks\Resources;

use App\Http\Controllers\Tasks\DTOs\TaskDTO;
use App\Http\Controllers\Tasks\Enums\TaskPriority;
use App\Http\Controllers\Tasks\Enums\TaskRepeatTypeEnum;
use App\Http\Controllers\Tasks\Enums\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="TaskResource",
 *     type="object",
 *     description="One task of the list of the user.",
 *
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="title", type="string", example="Write the report"),
 * @OA\Property(property="description", type="string", nullable=true, example="The report of the quarter."),
 * @OA\Property(property="dueDate", type="string", format="date", example="2026-12-01"),
 * @OA\Property(property="status", type="object", @OA\Property(property="value", type="string", example="pending")),
 * @OA\Property(property="priority", type="object", @OA\Property(property="value", type="string", example="medium")),
 * @OA\Property(property="repeatType", type="object", nullable=true, @OA\Property(property="value", type="string", example="daily")),
 * @OA\Property(property="repeatEndDate", type="string", format="date", nullable=true, example="2026-12-31"),
 * @OA\Property(property="tags", type="array", nullable=true, @OA\Items(type="string")),
 * @OA\Property(property="createdAt", type="string", example="2026-01-01 12:00:00"),
 * @OA\Property(property="updatedAt", type="string", example="2026-01-01 12:00:00")
 * )
 */
class TaskResource extends JsonResource
{
    private ?TaskDTO $taskDTO = null;

    /**
     * @return $this
     */
    public function setDTO(TaskDTO $taskDTO): self
    {
        $this->taskDTO = $taskDTO;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = is_string($this->status) ? TaskStatus::from($this->status) : $this->status;
        $priority = is_string($this->priority) ? TaskPriority::from($this->priority) : $this->priority;
        $repeatType = $this->repeat_type ? (is_string($this->repeat_type) ? TaskRepeatTypeEnum::from($this->repeat_type) : $this->repeat_type) : null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'dueDate' => $this->due_date?->format('Y-m-d'),
            'status' => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ],
            'priority' => [
                'value' => $priority->value,
                'label' => $priority->label(),
                'color' => $priority->color(),
            ],
            'repeatType' => [
                // A task does not have to repeat itself, so the three values
                // are empty when the task does not repeat. Before, the answer
                // of a task without a repetition ended in an error, because
                // the name of the repetition was read although there was
                // none.
                'value' => $repeatType?->value,
                'label' => $repeatType?->label(),
                'color' => $repeatType?->color(),
            ],
            'repeatEndDate' => $this->repeat_end_date?->format('Y-m-d'),
            'tags' => $this->tags,
            //            'userId' => $this->user_id,
            'createdAt' => $this->created_at?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
