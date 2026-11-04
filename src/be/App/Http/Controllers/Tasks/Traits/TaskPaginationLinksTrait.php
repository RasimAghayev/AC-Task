<?php

namespace App\Http\Controllers\Tasks\Traits;

use App\Traits\PaginationLinksTrait;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     description="The state of the page of the listing.",
 *
 * @OA\Property(property="current_page", type="integer", example=1),
 * @OA\Property(property="from", type="integer", nullable=true, example=1),
 * @OA\Property(property="last_page", type="integer", example=3),
 * @OA\Property(property="per_page", type="integer", example=15),
 * @OA\Property(property="to", type="integer", nullable=true, example=15),
 * @OA\Property(property="total", type="integer", example=42)
 * )
 */
trait TaskPaginationLinksTrait
{
    use PaginationLinksTrait;

    protected function getMetaData(): array
    {
        return [
            'current_page' => $this->resource->currentPage(),
            'from' => $this->resource->firstItem(),
            'last_page' => $this->resource->lastPage(),
            'per_page' => $this->resource->perPage(),
            'to' => $this->resource->lastItem(),
            'total' => $this->resource->total(),
        ];
    }
}
