<?php

namespace App\Http\Requests\SFX\Pack;

use App\Http\Requests\ApiRequest;

class BuyPackRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids' => 'required|array|size:10',
            'name' => 'sometimes',
            'licenseId' => 'required|exists:licenses,id',
        ];
    }
}
