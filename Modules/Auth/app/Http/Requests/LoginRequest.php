<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized to make this request.
     *
     * Login is a public endpoint, so any client can submit
     * login credentials for authentication.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
            ],

            'password' => [ //we don`t need to determine a minimum or maximum here because  this endpoint is checking an existing password, not creating a new password.
                'required',
                'string',

            ],
        ];
    }
}
