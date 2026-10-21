<?php

namespace App\Http\Requests;

use App\Http\Responses\ErrorValidationResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The answer of a request that does not pass the validation.
     *
     * The method returned the errors of the validator before, and the request
     * went on with nothing. The controller then failed on a missing value, so
     * a request with a wrong value was answered with the status 500 instead of
     * the status 422.
     *
     *
     * @throws HttpResponseException
     */
    public function failedValidation(Validator $validator): mixed
    {
        throw new HttpResponseException(
            ErrorValidationResponse::make($validator->errors())->toResponse($this)
        );
    }
}
