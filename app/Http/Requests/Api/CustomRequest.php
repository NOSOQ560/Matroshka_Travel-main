<?php

namespace App\Http\Requests\Api;

use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class CustomRequest extends FormRequest
{
    /**
     * @param Validator $validator
     * @return void
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        if ($this->is(['api/*', 'admin/*'])) {
            $response = ResponseHelper::validationErrorResponse($validator->errors());
            throw new ValidationException($validator, $response);
        }
    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
