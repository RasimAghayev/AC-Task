<?php

namespace App\Http\Controllers\Tasks\DTOs;


use DateMalformedIntervalStringException;
use DateMalformedPeriodStringException;
use DateMalformedStringException;
use InvalidArgumentException;
use App\Http\Controllers\Tasks\Enums\
{
    TaskRepeatTypeEnum,
    TaskStatus,
    TaskPriority
};
use DateTime;

readonly class TaskDTO
{
    /**
     * @throws DateMalformedIntervalStringException
     * @throws DateMalformedPeriodStringException
     */
    public function __construct(
        public string                $title,
        public ?string               $description,
        public ?DateTime             $due_date,
        public TaskStatus            $status,
        public TaskPriority          $priority,
        public ?TaskRepeatTypeEnum   $repeat_type,
        public ?DateTime             $repeat_end_date,
        public ?array                $tags,
        public int                   $user_id
    )
    {}

    /**
     * @throws DateMalformedStringException
     */
    public static function fromRequest(array $data): self
    {
        $dueDate = isset($data['due_date']) ? new DateTime($data['due_date']) : null;
        $repeatType = isset($data['repeat_type']) ? TaskRepeatTypeEnum::from($data['repeat_type']) : null;

        $repeatEndDate = null;
        if (isset($data['repeat_end_date'])) {
            $repeatEndDate = new DateTime($data['repeat_end_date']);
        } elseif ($dueDate && $repeatType && $repeatType !== TaskRepeatTypeEnum::CUSTOM) {
            $repeatEndDate = $repeatType->getNextExecutionDate($dueDate);
        }

        if ($repeatEndDate && $dueDate && $repeatEndDate <= $dueDate) {
            throw new InvalidArgumentException('Repeat end date must be after due date');
        }

        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            due_date: $dueDate,
            status: TaskStatus::from($data['status']),
            priority: TaskPriority::from($data['priority']),
            repeat_type: $repeatType,
            repeat_end_date: $repeatEndDate,
            tags: $data['tags'] ?? null,
            user_id:  auth('api')->id(),
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'repeat_type' => $this->repeat_type?->value,
            'repeat_end_date' => $this->repeat_end_date?->format('Y-m-d'),
            'tags' => $this->tags,
            'user_id' => $this->user_id,
        ];
    }
}