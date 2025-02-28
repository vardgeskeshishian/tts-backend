<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ForgetPasswordRequest extends FormRequest
{
    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
                'email' => [
                    'required',
                    'email',
                    Rule::exists(User::class, 'email'),
                ]
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'email' => 'User with this email address not found'
        ];
    }
}