<?php

namespace App\Http\Controllers\Auth\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AuthResource",
 *     type="object",
 *     description="The profile of a user.",
 *
 * @OA\Property(property="name", type="string", example="John Doe"),
 * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 * @OA\Property(property="createdAt", type="string", example="01.01.2026 12:00:00"),
 * @OA\Property(property="updatedAt", type="string", example="01.01.2026 12:00:00")
 * )
 */
class AuthResource extends JsonResource
{
    public $resource;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array|JsonSerializable|Arrayable
    {
        return [
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'createdAt' => $this->resource->created_at->format('d.m.Y H:i:s'),
            'updatedAt' => $this->resource->updated_at->format('d.m.Y H:i:s'),
        ];
    }
}
