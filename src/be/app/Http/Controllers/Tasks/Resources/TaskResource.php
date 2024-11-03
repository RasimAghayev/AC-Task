<?php

namespace App\Http\Controllers\Tasks\Resources;

use App\Http\Controllers\Tasks\DTOs\TaskDTO;
use App\Http\Controllers\Tasks\Enums\
{
    TaskPriority,
    TaskRepeatTypeEnum,
    TaskStatus
};
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * @var TaskDTO|null
     */
    private ?TaskDTO $taskDTO = null;

    /**
     * @param TaskDTO $taskDTO
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
                'value' => $repeatType->value,
                'label' => $repeatType->label(),
                'color' => $repeatType->color(),
            ],
            'repeatEndDate' => $this->repeat_end_date?->format('Y-m-d'),
            'tags' => $this->tags,
//            'userId' => $this->user_id,
            'createdAt' => $this->created_at?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}