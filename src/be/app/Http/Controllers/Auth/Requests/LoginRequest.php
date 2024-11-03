<?php

namespace App\Http\Controllers\Auth\Requests;

use App\Http\Requests\ApiFormRequest;

class LoginRequest  extends ApiFormRequest
{
    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];
    }
}
