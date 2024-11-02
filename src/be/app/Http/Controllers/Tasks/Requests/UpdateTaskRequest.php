<?php
declare(strict_types=1);

namespace App\Http\Controllers\Tasks\Requests;

use App\Http\Controllers\Tasks\DTOs\TaskDTO;
use App\Http\Controllers\Tasks\Models\Task;
use App\Http\Controllers\Tasks\Traits\TaskRequestTrait;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Validator;

class UpdateTaskRequest extends ApiFormRequest
{
    use TaskRequestTrait;

    private ?Task $task = null;

    /**
     * Get task instance
     *
     * @return Task
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
     *
     * @return bool
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
                if (!is_array($fieldRules)) {
                    $fieldRules = [$fieldRules];
                }

                if ($field === 'description' || $field === 'tags' ||
                    $field === 'repeat_type' || $field === 'repeat_end_date') {
                    continue;
                }

                if (!in_array('required', $fieldRules)) {
                    array_unshift($fieldRules, 'required');
                }
            }
        } else {
            foreach ($rules as $field => &$fieldRules) {
                if (!is_array($fieldRules)) {
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
     * @return TaskDTO
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

        if (!isset($validatedData['user_id'])) {
            $validatedData['user_id'] = auth('api')->id();
        }

        return TaskDTO::fromRequest($validatedData);
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('repeat_type') === 'custom' && !$this->input('repeat_end_date')) {
                $validator->errors()->add('repeat_end_date', 'Xüsusi təkrarlanma seçildikdə bitmə tarixi mütləq daxil edilməlidir.');
            }
        });
    }
}