<?php

namespace App\Http\Controllers\Auth\Requests;

use App\Http\Requests\ApiFormRequest;

class RegisterRequest extends ApiFormRequest
{
    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ];
    }
}
