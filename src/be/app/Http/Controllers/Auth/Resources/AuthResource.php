<?php

namespace App\Http\Controllers\Auth\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class AuthResource extends JsonResource
{
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray(Request $request): array|JsonSerializable|Arrayable
    {
        return [
            'name'=>$this->resource->name,
            'email'=>$this->resource->email,
            'createdAt' => $this->resource->created_at->format('d.m.Y H:i:s'),
            'updatedAt' => $this->resource->updated_at->format('d.m.Y H:i:s'),
        ];
    }
}



