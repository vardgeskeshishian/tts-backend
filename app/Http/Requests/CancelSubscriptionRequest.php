<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelSubscriptionRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'subscription_id' => [
                'required',
                'string'
            ],
            'cancel_at' => [
                'required',
                'date'
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getSubscriptionId(): mixed
    {
        return $this->input('subscription_id');
    }

    /**
     * @return mixed
     */
    public function getCancelAt(): mixed
    {
        return $this->input('cancel_at');
    }
}