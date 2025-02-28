<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Traits\CanFormatImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Validation\ValidatesRequests;

class AbstractModelService
{
    use ValidatesRequests, CanFormatImages;

    protected $validationRules = [
        'name' => 'required',
    ];

    protected $excludedUpdateFields = [];

    protected $model = "";
    protected $modelClass = null;
    protected $imagesService;
    protected $metaService;
    protected $resource;

    protected $unset = [];
    protected $taggable = ['tags'];
    /**
     * @var TaggingService
     */
    protected $taggingService;

    /**
     * AbstractModelService constructor.
     *
     * @param ImagesService $imagesService
     * @param MetaService $metaService
     * @param TaggingService $taggingService
     */
    public function __construct(
        ImagesService $imagesService,
        MetaService $metaService,
        TaggingService $taggingService
    ) {
        $this->model = resolve($this->modelClass);

        $this->imagesService = $imagesService;
        $this->metaService = $metaService;
        $this->taggingService = $taggingService;
    }

    /**
     * @param Request $request
     *
     * @return Model|array
     * @throws ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, $this->validationRules);

        $data = $this->buildDataFromRequest($request);

        return $this->fillInModel($this->model, $data);
    }

    protected function buildDataFromRequest(Request $request)
    {
        $unset = array_merge($this->unset, ['meta', 'images'], $this->taggable);

        $data = $request->except($unset);

        $meta = $request->get('meta') ?? [];
        $images = $request->files->get('images') ?? [];
        $taggable = $request->only($this->taggable);

        return [$data, $meta, $images, $taggable];
    }

    /**
     * @param Request $request
     * @param Model $model
     *
     * @return Model|array
     */
    public function update(Request $request, Model $model)
    {
        $data = $this->buildDataFromRequest($request);

        return $this->fillInModel($model, $data);
    }

    public function delete(Model $model)
    {
        return $model->delete();
    }

    /**
     * @param Model $model
     * @param $builtData
     *
     * @return mixed
     */
    protected function fillInModel($model, $builtData)
    {
        [$data] = $builtData;

        foreach ($this->excludedUpdateFields as $fieldName) {
            if (isset($data[$fieldName])) {
                unset($data[$fieldName]);
            }
        }

        $model->fill($data);
        $model->save();

        return $this->resource ? new $this->resource($model) : $model;
    }
}
