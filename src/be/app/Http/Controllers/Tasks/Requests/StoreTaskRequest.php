<?php
declare(strict_types=1);

namespace App\Http\Controllers\Tasks\Requests;

use App\Http\Controllers\Tasks\DTOs\TaskDTO;
use App\Http\Controllers\Tasks\Traits\TaskRequestTrait;
use App\Http\Requests\ApiFormRequest;
use DateMalformedStringException;
use Illuminate\Validation\Validator;

class StoreTaskRequest extends ApiFormRequest
{
    use TaskRequestTrait;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = $this->getCommonRules();

        // Add required rules for specific fields
        $requiredFields = ['title', 'status', 'priority'];
        foreach ($requiredFields as $field) {
            if (isset($rules[$field]) && is_array($rules[$field])) {
                array_unshift($rules[$field], 'required');
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return $this->getCommonMessages();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function toDTO(): TaskDTO
    {
        return TaskDTO::fromRequest($this->validated());
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