<?php

namespace App\Http\Controllers\Tasks\Resources;

use App\Http\Controllers\Tasks\Traits\TaskPaginationLinksTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="TaskCollection",
 *     type="object",
 *     description="A page of the tasks of the user.",
 *
 * @OA\Property(property="message", type="string", example="Task successfully full list."),
 * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TaskResource")),
 * @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
 * @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 * )
 */
class TaskCollection extends ResourceCollection
{
    use TaskPaginationLinksTrait;

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = TaskResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => 'Task successfully full list.',
            'data' => $this->collection,
            'meta' => $this->getMetaData(),
            'links' => [
                'self' => $this->getSelfLink(),
                'first' => $this->getFirstLink(),
                'last' => $this->getLastLink(),
                'prev' => $this->getPrevLink(),
                'next' => $this->getNextLink(),
            ],
        ];
    }
}
