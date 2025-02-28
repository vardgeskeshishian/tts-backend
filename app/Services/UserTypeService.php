<?php

namespace App\Services;

use App\Http\Resources\Api\UserTypeResource;
use App\Models\UserType;
use Illuminate\Support\Str;

class UserTypeService extends AbstractModelService
{
    protected $modelClass = UserType::class;

    protected $validationRules = [
        'name' => 'required|unique:user_types'
    ];

    protected function fillInModel($model, $builtData)
    {
        [$data] = $builtData;

        if (!$model->slug) {
            $data['slug'] = Str::slug($data['name']);
        }

        $model->fill($data);
        $model->save();

        return new UserTypeResource($model);
    }
}
