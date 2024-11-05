<?php

namespace App\Http\Controllers\Tasks\Resources;

use App\Http\Controllers\Tasks\Traits\TaskPaginationLinksTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

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
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
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