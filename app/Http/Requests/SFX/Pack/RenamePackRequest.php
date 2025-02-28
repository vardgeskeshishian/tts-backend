<?php


namespace App\Http\Requests\SFX\Pack;

use App\Http\Requests\ApiRequest;

class RenamePackRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'packId' => 'required|exists:sfx_packs,id',
            'name' => 'required',
        ];
    }
}
