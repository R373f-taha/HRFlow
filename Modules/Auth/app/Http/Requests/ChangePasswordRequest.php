<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Password changes require an authenticated user.
     *
     * The route is already protected by auth:sanctum.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }
}
