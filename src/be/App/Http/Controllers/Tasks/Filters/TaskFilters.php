<?php
namespace App\Http\Controllers\Tasks\Filters;

use App\Filters\ApiFilter;
use App\Http\Controllers\Tasks\Enums\
{
    TaskPriority,
    TaskStatus,
    TaskRepeatTypeEnum
};
use Illuminate\Http\Request;
use InvalidArgumentException;

class TaskFilters extends ApiFilter
{
    protected array $safeParms = [
        'title' => ['eq', 'lk', 'nlk'],
        'description' => ['lk', 'nlk'],
        'due_date' => ['eq', 'lt', 'lte', 'gt', 'gte', 'bt'],
        'status' => ['eq', 'in'],
        'priority' => ['eq', 'in'],
        'repeat_type' => ['eq', 'in'],
        'repeat_end_date' => ['eq', 'lt', 'lte', 'gt', 'gte', 'bt'],
        'tags' => ['json'],
        'user_id' => ['eq', 'in'],
        'created_at' => ['eq', 'lt', 'lte', 'gt', 'gte', 'bt'],
        'updated_at' => ['eq', 'lt', 'lte', 'gt', 'gte', 'bt']
    ];

    protected array $columnMap = [
        'dueDate' => 'due_date',
        'repeatType' => 'repeat_type',
        'repeatEndDate' => 'repeat_end_date',
        'userId' => 'user_id',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    ];

    /**
     * @param Request $request
     * @return array
     */
    public function transform(Request $request): array
    {
        $queries = parent::transform($request);

        // Enum validations
        if (isset($request->status)) {
            $this->validateEnum($request->status, TaskStatus::class);
        }
        if (isset($request->priority)) {
            $this->validateEnum($request->priority, TaskPriority::class);
        }
        if (isset($request->repeat_type)) {
            $this->validateEnum($request->repeat_type, TaskRepeatTypeEnum::class);
        }

        return $queries;
    }

    /**
     * @param $value
     * @param string $enumClass
     * @return void
     */
    protected function validateEnum($value, string $enumClass): void
    {
        if (is_array($value)) {
            foreach ($value as $v) {
                if (!$enumClass::tryFrom($v)) {
                    throw new InvalidArgumentException("Invalid enum value: {$v}");
                }
            }
        } else {
            if (!$enumClass::tryFrom($value)) {
                throw new InvalidArgumentException("Invalid enum value: {$value}");
            }
        }
    }
}