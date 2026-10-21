<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tasks\Requests;

use App\Http\Controllers\Tasks\DTOs\TaskDTO;
use App\Http\Controllers\Tasks\Models\Task;
use App\Http\Controllers\Tasks\Traits\TaskRequestTrait;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateTaskRequest",
 *     type="object",
 *     description="The values of a task that changes.",
 *
 * @OA\Property(property="title", type="string", example="Write the report"),
 * @OA\Property(property="description", type="string", nullable=true, example="The report of the quarter."),
 * @OA\Property(property="due_date", type="string", format="date", example="2026-12-01"),
 * @OA\Property(property="status", type="string", enum={"pending", "in_progress", "under_review", "completed", "cancelled"}, example="pending"),
 * @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}, example="medium"),
 * @OA\Property(property="repeat_type", type="string", nullable=true, enum={"daily", "weekly", "monthly", "custom"}, example="daily"),
 * @OA\Property(property="repeat_end_date", type="string", format="date", nullable=true, example="2026-12-31"),
 * @OA\Property(property="tags", type="array", nullable=true, @OA\Items(type="string"))
 * )
 */
class UpdateTaskRequest extends ApiFormRequest
{
    use TaskRequestTrait;

    private ?Task $task = null;

    /**
     * Get task instance
     *
     * @throws ModelNotFoundException
     */
    protected function getTask(): Task
    {
        if ($this->task === null) {
            $taskId = $this->route('id');
            $this->task = Task::findOrFail($taskId);
        }

        return $this->task;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        try {
            $task = $this->getTask();

            return $task->user_id === auth('api')->id();
        } catch (ModelNotFoundException) {
            return false;
        }
    }

    /**
     * Get validation rules
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = $this->getCommonRules();

        if ($this->method() === 'PUT') {
            foreach ($rules as $field => &$fieldRules) {
                if (! is_array($fieldRules)) {
                    $fieldRules = [$fieldRules];
                }

                if ($field === 'description' || $field === 'tags' ||
                    $field === 'repeat_type' || $field === 'repeat_end_date') {
                    continue;
                }

                if (! in_array('required', $fieldRules)) {
                    array_unshift($fieldRules, 'required');
                }
            }
        } else {
            foreach ($rules as $field => &$fieldRules) {
                if (! is_array($fieldRules)) {
                    $fieldRules = [$fieldRules];
                }
                array_unshift($fieldRules, 'sometimes');
            }
        }

        return $rules;
    }

    /**
     * Get custom validation messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->getCommonMessages();
    }

    /**
     * Convert validated data to DTO
     *
     * @throws ModelNotFoundException
     */
    public function toDTO(): TaskDTO
    {
        $validatedData = $this->validated();

        if ($this->method() === 'PATCH') {
            try {
                $task = $this->getTask();
                $existingData = $task->toArray();

                foreach ($validatedData as $key => $value) {
                    if (isset($value)) {
                        $existingData[$key] = $value;
                    }
                }
                $validatedData = $existingData;
            } catch (ModelNotFoundException $e) {
                throw new ModelNotFoundException('Task not found');
            }
        }

        if (! isset($validatedData['user_id'])) {
            $validatedData['user_id'] = auth('api')->id();
        }

        return TaskDTO::fromRequest($validatedData);
    }

    /**
     * Configure the validator instance.
     *
     * @param  Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('repeat_type') === 'custom' && ! $this->input('repeat_end_date')) {
                $validator->errors()->add('repeat_end_date', 'Xüsusi təkrarlanma seçildikdə bitmə tarixi mütləq daxil edilməlidir.');
            }
        });
    }
}
