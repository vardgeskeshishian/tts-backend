<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class UserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => [
                'string',
                Rule::unique(User::class, 'email')->ignore(auth()->user())
            ],
            'payout_email' => [
                'string',
                Rule::unique(User::class, 'payout_email')->ignore(auth()->user())
            ],
            'customer_id' => [
                'string',
                Rule::unique(User::class, 'customer_id')->ignore(auth()->user())
            ]
        ];
    }
}