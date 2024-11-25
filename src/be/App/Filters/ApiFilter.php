<?php 
declare(strict_types=1);

namespace App\Filters;

use Illuminate\Http\Request;

class ApiFilter
{
    protected array $safeParms = [];
    protected array $columnMap = [];
    protected array $operatorMap = [
        'eq' => '=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
        'ne' => '!=',
        'lk' => 'LIKE',
        'nlk' => 'NOT LIKE',
        'ilk' => 'ILIKE',
        'nilk' => 'NOT ILIKE',
        'bt' => 'BETWEEN',
        'nbt' => 'NOT BETWEEN',
        'in' => 'IN',
        'nin' => 'NOT IN',
        'json' => 'JSON_CONTAINS',
        'concat' => 'CONCAT',
    ];

    public function transform(Request $request): array
    {
        $eloQuery = [];

        foreach ($this->safeParms as $parm => $details) {
            $query = $request->query($parm);
            if (!$query) {
                continue;
            }

            if (is_array($details) && !isset($details[0])) {
                foreach ($details as $operator) {
                    if (isset($query[$operator])) {
                        $column = $this->columnMap[$parm] ?? $parm;
                        if ($result = $this->buildQuery($column, $operator, $query[$operator])) {
                            $eloQuery[] = $result;
                        }
                    }
                }
            } else {
                $relation = $details['relation'] ?? null;
                $column = $details['column'] ?? $parm;
                $type = $details['type'] ?? null;
                $fields = $details['fields'] ?? [];

                if ($type === 'concat' && !empty($fields)) {
                    $eloQuery[] = $this->applyConcatFilter($relation, $fields, $query[$type]);
                } elseif ($type && isset($query[$type])) {
                    if ($result = $this->buildQuery($relation ? $relation . '.' . $column : $column, $type, $query[$type])) {
                        $eloQuery[] = $result;
                    }
                }
            }
        }

        return $eloQuery;
    }

    protected function buildQuery($column, $operator, $value, $relation = null, $fields = []): ?array
    {
        $mappedOperator = $this->operatorMap[$operator] ?? null;

        if ($operator === 'concat') {
            return $this->applyConcatFilter($relation, $fields, $value);
        }

        if (!$mappedOperator) {
            return null;
        }

        return match ($operator) {
            'lk', 'nlk' => [$column, $mappedOperator, '%' . $value . '%'],
            'bt', 'nbt' => is_array($value) && count($value) === 2
                ? [$column, $mappedOperator, $value]
                : null,
            'in', 'nin' => is_array($value)
                ? [$column, $mappedOperator, $value]
                : null,
            'json' => ['JSON_CONTAINS', $column, $value],
            default => [$column, $mappedOperator, $value],
        };
    }

    protected function applyConcatFilter($relation, array $fields, string $value): ?array
    {
        if (empty($fields)) {
            return null;
        }
        
        $concatenatedColumns = "CONCAT(" . implode(", ' ', ", $fields) . ")";
        
        return function ($query) use ($relation, $fields, $value) {
            if ($relation) {
                $query->whereHas($relation, function ($q) use ($fields, $value) {
                    $q->whereRaw("$concatenatedColumns LIKE ?", ["%{$value}%"]);
                });
            } else {
                $query->whereRaw("$concatenatedColumns LIKE ?", ["%{$value}%"]);
            }
        };
    }
}
