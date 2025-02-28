<?php

namespace App\Services;

use App\Http\Resources\Api\TestimonialsResource;
use App\Models\Structure\Testimonial;
use Exception;
use Illuminate\Database\Eloquent\Model;

class TestimonialsService extends AbstractModelService
{
    protected $modelClass = Testimonial::class;

    protected $validationRules = [
        'header' => 'required',
        'text' => 'required',
        'images' => 'array'
    ];

    /**
     * @param Model $model
     * @param $builtData
     *
     * @return mixed
     * @throws Exception
     */
    protected function fillInModel($model, $builtData)
    {
        [$data, $meta, $images] = $builtData;

        unset($meta);

        $model->fill($data);
        $model->save();

        $this->imagesService->upload($model, $images);

        return new TestimonialsResource($model);
    }
}
