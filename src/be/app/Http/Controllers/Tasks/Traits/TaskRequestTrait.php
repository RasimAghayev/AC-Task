<?php
declare(strict_types=1);

namespace App\Http\Controllers\Tasks\Traits;

use App\Http\Controllers\Tasks\Enums\{
    TaskStatus,
    TaskPriority,
    TaskRepeatTypeEnum,
};
use Illuminate\Validation\Rule;

trait TaskRequestTrait
{
    /**
     * Get common validation rules
     *
     * @return array<string, mixed>
     */
    protected function getCommonRules(): array
    {
        return [
            'title' => ['string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['date'],
            'status' => ['required', Rule::in(TaskStatus::values())],
            'priority' => ['required', Rule::in(TaskPriority::values())],
            'repeat_type' => ['nullable', Rule::in(TaskRepeatTypeEnum::values())],
            'repeat_end_date' => [
                'nullable',
                'date',
                'after:due_date',
                function ($attribute, $value, $fail) {
                    if ($this->input('repeat_type') === TaskRepeatTypeEnum::CUSTOM->value && empty($value)) {
                        $fail('Xüsusi təkrarlanma seçildikdə bitmə tarixi mütləq daxil edilməlidir.');
                    }
                }
            ],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'user_id' => ['exists:users,id']
        ];
    }

    /**
     * Get common validation messages
     *
     * @return array<string, string>
     */
    protected function getCommonMessages(): array
    {
        return [
            'title.required' => 'Başlıq mütləq daxil edilməlidir',
            'title.min' => 'Başlıq minimum 3 simvol olmalıdır',
            'title.max' => 'Başlıq maksimum 255 simvol olmalıdır',
            'description.string' => 'Təsvir mətn formatında olmalıdır',
            'due_date.date' => 'Bitmə tarixi düzgün formatda deyil',
            'status.required' => 'Status mütləq daxil edilməlidir',
            'status.in' => 'Status düzgün deyil',
            'priority.required' => 'Prioritet mütləq daxil edilməlidir',
            'priority.in' => 'Prioritet düzgün deyil',
            'repeat_type.in' => 'Təkrarlanma tipi düzgün deyil',
            'repeat_end_date.date' => 'Təkrarlanma bitmə tarixi düzgün formatda deyil',
            'repeat_end_date.after' => 'Təkrarlanma bitmə tarixi, bitmə tarixindən sonra olmalıdır',
            'repeat_end_date.required_if' => 'Custom təkrarlanma seçildikdə bitmə tarixi mütləq daxil edilməlidir',
            'tags.array' => 'Teqlər array formatında olmalıdır',
            'tags.*.string' => 'Teqlər mətn formatında olmalıdır',
            'user_id.required' => 'İstifadəçi ID-si mütləq daxil edilməlidir',
            'user_id.exists' => 'Belə bir istifadəçi mövcud deyil',
        ];
    }

    /**
     * Frontend'dən gələn camelCase formatını snake_case formatına çeviririk
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        if (isset($this->title)) $data['title'] = $this->title;
        if (isset($this->description)) $data['description'] = $this->description;
        if (isset($this->dueDate)) $data['due_date'] = $this->dueDate;
        if (isset($this->status)) $data['status'] = $this->status;
        if (isset($this->priority)) $data['priority'] = $this->priority;
        if (isset($this->repeatType)) $data['repeat_type'] = $this->repeatType;
        if (isset($this->repeatEndDate)) $data['repeat_end_date'] = $this->repeatEndDate;
        if (isset($this->tags)) $data['tags'] = $this->tags;
        $data['user_id'] = auth('api')->id();

        $this->merge($data);
    }
}