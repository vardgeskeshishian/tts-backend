<?php

namespace App\Services;

use Exception;
use App\Models\Tags\Tag;
use App\Models\Tags\Mood;
use App\Models\Tags\Type;
use App\Models\SFX\SFXTag;
use App\Models\Tags\Genre;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use App\Models\Tags\Tagging;
use Illuminate\Http\Request;
use App\Models\SFX\SFXCategory;
use App\Models\Tags\Instrument;
use App\Models\Tags\AbstractTag;
use App\Http\Resources\Api\TagResource;
use App\Repositories\TaggingRepository;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\TagPositionRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaggingService
{
    const SFX_SLUG_CATEGORY = 'sfxcategory';
    const SFX_SLUG_TAG = 'sfxtag';

    const SFX_CATEGORY = 'sfx.category';
    const SFX_TAG = 'sfx.tag';
    const EXCLUDE_FROM_ALL = [self::SFX_CATEGORY, self::SFX_TAG];

    protected $morphTo = [
        'tag' => Tag::class,
        'genre' => Genre::class,
        'instrument' => Instrument::class,
        'type' => Type::class,
        'usage-type' => Type::class,
        'mood' => Mood::class,
        self::SFX_CATEGORY => SFXCategory::class,
        self::SFX_TAG => SFXTag::class,
        self::SFX_SLUG_CATEGORY => SFXCategory::class,
        self::SFX_SLUG_TAG => SFXTag::class,
    ];

    /**
     * @var TaggingRepository
     */
    private $taggingRepository;
    /**
     * @var ImagesService
     */
    private $imagesService;
    /**
     * @var TagPositionRepository
     */
    private $tagPositionRepository;

    public function __construct(
        TaggingRepository $taggingRepository,
        ImagesService $imagesService,
        TagPositionRepository $tagPositionRepository
    ) {
        $this->taggingRepository = $taggingRepository;
        $this->imagesService = $imagesService;
        $this->tagPositionRepository = $tagPositionRepository;
    }

    public function getAllTypes()
    {
        $keys = array_keys($this->morphTo);

        foreach ($keys as $index => $key) {
            if (!in_array($key, self::EXCLUDE_FROM_ALL)) {
                continue;
            }

            unset($keys[$index]);
        }

        sort($keys);
        return $keys;
    }

    /**
     * @param Model $model
     * @param array $taggable
     *
     * @throws Exception
     */
    public function process(Model $model, array $taggable)
    {
        $morphing = [
            'object_type' => $model->getMorphClass(),
            'object_id' => $model->id,
        ];

        foreach ($taggable as $key => $tags) {
            $key = Str::singular($key);

            if (!isset($this->morphTo[$key])) {
                throw new Exception("Tagging key ({$key}) does not exists");
            }

            $morphTo = $this->morphTo[$key];

            $morphData = array_merge($morphing, ['tag_type' => $morphTo]);
            Tagging::where($morphData)->delete();

            foreach ($tags as $tag) {
                $tagId = $this->createTagIfNotExists($morphTo, $tag);

                Tagging::create(array_merge($morphData, ['tag_id' => $tagId]));
            }
        }
    }

    /**
     * Check if tag exists for given Tag Class. If not - create it
     *
     * @param string $class
     * @param string $tagName
     *
     * @return int
     */
    public function createTagIfNotExists(string $class, string $tagName): int
    {
        /**
         * @var $object AbstractTag
         */
        $object = new $class;

        $tagName = trim($tagName);
        $tagSlug = Str::slug($tagName);

        $tagObject = $object::where('slug', $tagSlug)->first();

        if (!$tagObject) {
            $tagObject = $object::create([
                'name' => ucfirst($tagName),
                'slug' => $tagSlug,
            ]);

            $this->tagPositionRepository->updateForModel($object, $tagObject->id, 100);
        }

        $object->flushCache();

        return $tagObject->id;
    }

    public function getAllForModel(Model $model)
    {
        $morphData = [
            'object_type' => $model->getMorphClass(),
            'object_id' => $model->id,
        ];

        return $this->taggingRepository->getWhere($morphData);
    }

    /**
     * @param string $type
     *
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    public function getAllOfType(string $type): AnonymousResourceCollection
    {
        $model = $this->getModel($type);

        $collection = $model->has('pos')->with('pos')->get()->sortBy('pos.position');

        return TagResource::collection($collection);
    }

    public function getAll($withResource = true, $full = false)
    {
        $tagTypes = request('tag', []);

        $types = array_keys($this->morphTo);

        $tags = [];

        foreach ($types as $type) {
            if ($type === 'tag' || (!empty($tagTypes) && !in_array($type, $tagTypes))) {
                continue;
            }

            $model = $this->getModel($type)->disableCache();

            $collection = $model->has('pos')->with('pos')->get()->sortBy('pos.position');

            if (!request()->has('full') && !$full) {
                $collection = $collection->take(10);
            }

            if ($type === 'type') {
                $type = 'Usage Types';
            }

            $type = ucfirst($type);

            $tags[Str::plural($type)] = $withResource ? TagResource::collection($collection) : $collection;
        }

        return $tags;
    }

    public function deleteTag(string $type, int $id): AnonymousResourceCollection
    {
        $model = $this->getModel($type);
        $model = $model->find($id);
        $model->delete();

        return TagResource::collection($model->all());
    }

    /**
     * @param string $type
     *
     * @return mixed
     * @throws Exception
     */
    protected function getModel(string $type)
    {
        $type = Str::singular($type);
        $types = array_keys($this->morphTo);

        if (!in_array($type, $types)) {
            throw new ModelNotFoundException("tag type of {$type} doesn't exist");
        }

        return new $this->morphTo[$type];
    }

    /**
     * @param Request $request
     * @param string $type
     * @param $id
     *
     * @return TagResource
     * @throws Exception
     */
    public function uploadImage(Request $request, string $type, $id)
    {
        /**
         * @var $model AbstractTag
         */
        $model = $this->getModel($type);
        $model = $model->find($id);

        $images = $request->files->get('images');

        $this->imagesService->upload($model, $images);

        return new TagResource($model->fresh());
    }

    public function updatePositions(Request $request, string $type): AnonymousResourceCollection
    {
        /**
         * @var $model AbstractTag
         */
        $model = $this->getModel($type);

        $positions = $request->get('positions') ?? [];

        foreach ($positions as $id => $position) {
            $this->tagPositionRepository->updateForModel($model, $id, $position);
        }

        $model->refresh()->flushCache();

        $collection = $model->with('pos')->get()->sortBy('pos.position');

        return TagResource::collection($collection);
    }
}
