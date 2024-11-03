<?php

namespace App\Http\Controllers\Auth\Requests;

use App\Http\Requests\ApiFormRequest;

class UpdateMeRequest extends ApiFormRequest
{
    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . auth('api')->id(),
            'password' => 'nullable|min:8|confirmed',
        ];
    }
}
