<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthorApplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'full_name' => 'required|string',
            'email' => [
                'required',
                'string',
                'email',
                'unique:author_applicants,email'
            ],
            'location' => 'required|string',
            'music_description' => 'required|string',
            'portfolio_sample' => 'required|string',
        ];
    }
}
