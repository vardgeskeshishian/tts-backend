<?php

namespace App\Http\Requests;

use App\Enums\EffectiveFromEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PauseBillingUserSubscriptionsRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'effective_from' => [
                'required',
                Rule::in(array_column(EffectiveFromEnum::cases(), 'value')),
            ],
            'resume_at' => [
                'required',
            ]
        ];
    }
}