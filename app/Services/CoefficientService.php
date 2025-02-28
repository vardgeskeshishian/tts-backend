<?php

namespace App\Services;

use App\Http\Resources\Api\CoefficientResource;
use App\Models\Coefficient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CoefficientService extends AbstractModelService
{
    protected $resource = CoefficientResource::class;
    protected $validationRules = [
        'coefficient' => 'required'
    ];

    protected $excludedUpdateFields = [
        'name',
        'short_name'
    ];

    protected $modelClass = Coefficient::class;

    /**
     * @param Request $request
     * @param Model $model
     *
     * @return Model|array
     */
    public function update(Request $request, Model $model)
    {
        $data = $this->buildDataFromRequest($request);

        $result = $this->fillInModel($model, $data);

        \Cache::forget('search:coefficients');
        \Artisan::call('recalculate-periodic');

        return $result;
    }
}
