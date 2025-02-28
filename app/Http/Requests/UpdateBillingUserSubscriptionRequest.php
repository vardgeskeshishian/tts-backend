<?php

namespace App\Http\Requests;

use App\Enums\ProrationBillingModeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBillingUserSubscriptionRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'price_id' => [
                'required',
                'exists:\\App\\Models\\Paddle\\BillingPrice,id'
            ],
            'proration_billing_mode' => [
                'required',
                Rule::in(array_column(ProrationBillingModeEnum::cases(), 'value')),
            ]
        ];
    }
}