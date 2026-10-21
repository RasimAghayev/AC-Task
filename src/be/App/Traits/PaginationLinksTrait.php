<?php

namespace App\Traits;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *     description="The addresses of the pages of the listing.",
 *
 * @OA\Property(property="first", type="string", nullable=true, example="http://localhost/api/v1/tasks?page=1"),
 * @OA\Property(property="last", type="string", nullable=true, example="http://localhost/api/v1/tasks?page=3"),
 * @OA\Property(property="prev", type="string", nullable=true, example="http://localhost/api/v1/tasks?page=1"),
 * @OA\Property(property="next", type="string", nullable=true, example="http://localhost/api/v1/tasks?page=3")
 * )
 */
trait PaginationLinksTrait
{
    protected function getSelfLink(): ?string
    {
        return $this->resource->url($this->resource->currentPage());
    }

    protected function getFirstLink(): ?string
    {
        return $this->resource->url(1);
    }

    protected function getLastLink(): ?string
    {
        return $this->resource->url($this->resource->lastPage());
    }

    protected function getPrevLink(): ?string
    {
        return $this->resource->previousPageUrl();
    }

    protected function getNextLink(): ?string
    {
        return $this->resource->nextPageUrl();
    }
}
