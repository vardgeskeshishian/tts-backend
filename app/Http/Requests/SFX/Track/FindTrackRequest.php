<?php

namespace App\Http\Requests\SFX\Track;


use App\Http\Requests\ApiRequest;

class FindTrackRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'trackId' => 'required|exists:sfx_tracks,id',
        ];
    }
}
