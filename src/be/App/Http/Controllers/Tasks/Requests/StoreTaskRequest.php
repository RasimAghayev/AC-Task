<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tasks\Requests;

use App\Http\Controllers\Tasks\DTOs\TaskDTO;
use App\Http\Controllers\Tasks\Traits\TaskRequestTrait;
use App\Http\Requests\ApiFormRequest;
use DateMalformedStringException;
use Illuminate\Validation\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreTaskRequest",
 *     type="object",
 *     description="The values of a new task.",
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
