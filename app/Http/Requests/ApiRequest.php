<?php


namespace App\Http\Requests;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Http\FormRequest;

class ApiRequest extends FormRequest
{
    const USER_TOKEN_HEADER = 'Authorization';

    public function rules()
    {
        return [];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function user($guard = null)
    {
        $userToken = $this->headers->get(self::USER_TOKEN_HEADER);
        $userToken = str_replace('Bearer ', '', $userToken);

        if (!$userToken) {
            return null;
        }

        return JWTAuth::setToken($userToken)->toUser();
    }
}
