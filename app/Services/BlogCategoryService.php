<?php

namespace App\Services;

use App\Http\Resources\Api\BlogCategoryResource;
use App\Models\BlogCategory;
use App\Models\BlogToCategories;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogCategoryService extends AbstractModelService
{
    protected $modelClass = BlogCategory::class;

    protected $validationRules = [
        'name' => 'required|unique:blog_categories'
    ];

    public function __construct(
        ImagesService  $imagesService,
        MetaService    $metaService,
        TaggingService $taggingService,
    ) {
        parent::__construct($imagesService, $metaService, $taggingService);
    }

    public function process(Model $model, array $categories)
    {
        BlogToCategories::where(['blog_id' => $model])->delete();

        foreach ($categories as $category) {
            BlogToCategories::create([
                'blog_id' => $model->id,
                'category_id' => $category
            ]);
        }
    }

    protected function fillInModel($model, $builtData)
    {
        [$data, $meta] = $builtData;

        foreach ($this->excludedUpdateFields as $fieldName) {
            if (isset($data[$fieldName])) {
                unset($data[$fieldName]);
            }
        }

        if (!$model->slug) {
            $data['slug'] = Str::slug($data['name']);
        }

        /**
         * if model already have deletable status and its equals to false - don't change this param
         */
        $data['deletable'] = !(isset($model->deletable) && !$model->deletable);

        $model->fill($data);
        $model->save();

        $this->metaService->fillInForObject($model, $meta);

        return new BlogCategoryResource($model);
    }
}
