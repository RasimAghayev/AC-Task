<?php

namespace App\Http\Controllers\Auth\Requests;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateMeRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . Auth::id(),
            'password' => 'nullable|min:8|confirmed',
        ];
    }
}
