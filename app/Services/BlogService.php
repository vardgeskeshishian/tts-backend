<?php

namespace App\Services;

use App\Http\Resources\Api\BlogResource;
use App\Models\Structure\Blog;
use App\Repositories\BlogRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogService extends AbstractModelService
{
    protected $modelClass = Blog::class;
    protected $validationRules = [
        'title' => 'required',
    ];

    /**
     * @var BlogCategoryService
     */
    private $blogCategoryService;

    public function __construct(
        ImagesService $imagesService,
        MetaService $metaService,
        TaggingService $taggingService,
        BlogCategoryService $blogCategoryService
    ) {
        parent::__construct($imagesService, $metaService, $taggingService);

        $this->blogCategoryService = $blogCategoryService;
    }

    protected function buildDataFromRequest(Request $request)
    {
        $data = parent::buildDataFromRequest($request);

        $categories = $request->get('categories', []);

        $data[] = $categories;

        return $data;
    }

    /**
     * @param Model $model
     * @param $builtData
     *
     * @return mixed
     * @throws Exception
     */
    public function fillInModel($model, $builtData)
    {
        /**
         * @var $model Blog
         */
        [$data, $meta, $images, $tags, $categories] = $builtData;

        foreach ($this->excludedUpdateFields as $fieldName) {
            if (isset($data[$fieldName])) {
                unset($data[$fieldName]);
            }
        }

        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (isset($data['featured']) && $data['featured'] && !$model->isFeatured()) {
            Blog::where('featured', true)->update(['featured' => false]);
        }

        $model->fill($data);
        $model->save();

        $this->metaService->fillInForObject($model, $meta);
        $this->imagesService->upload($model, $images);
        $this->taggingService->process($model, $tags);
        $this->blogCategoryService->process($model, $categories);

        return new BlogResource($model);
    }
}
