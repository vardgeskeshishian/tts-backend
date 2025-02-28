<?php

namespace App\Services;

use App\Http\Resources\Any\Collection\TrackResource;
use App\Http\Resources\CollectionsResource;
use App\Models\Structure\Collection;
use App\Models\Structure\CollectionTrack;
use App\Models\Tags\Tag;
use App\Models\Tags\Tagging;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\ResponseCache\Facades\ResponseCache;

class CollectionsService extends AbstractModelService
{
    protected $modelClass = Collection::class;
    protected $taggable = ['tags'];

    public function __construct(
        ImagesService $imagesService,
        MetaService $metaService,
        TaggingService $taggingService
    ) {
        parent::__construct($imagesService, $metaService, $taggingService);
    }

    protected function fillInModel($model, $builtData)
    {
        [$data, $meta, $images, $taggable] = $builtData;

        $trackIds = $data['tracks_ids'] ?? [];
        unset($data['tracks_ids']);

        $model->fill($data);
        $model->save();

        if (!empty($trackIds)) {
            CollectionTrack::where('collection_id', $model->id)->delete();
            
            foreach ($trackIds as $track_id) {
                CollectionTrack::updateOrCreate([
                    'collection_id' => $model->id,
                    'track_id' => $track_id
                ]);
            }
        }

        $this->metaService->fillInForObject($model, $meta);
        $this->imagesService->upload($model, $images);
        $this->taggingService->process($model, $taggable);

        $model->refresh();

        ResponseCache::clear();

        Cache::forget("meta:" . $this->modelClass . ":{$model->id}");

        return [
            'collection' => new CollectionsResource($model),
            'tracks' => TrackResource::collection($model->tracks)
        ];
    }

    public function collectionsGet()
    {
        $tag = request('tag');

        $collections = Collection::where('hidden', false);

        if ($tag) {
            $tag = Tag::where('slug', Str::slug($tag))->first();
            if ($tag) {
                $collectionsIds = Tagging::where([
                    'object_type' => Collection::class,
                    'tags_type' => Tag::class,
                    'tags_id' => $tag->id
                ])->get()->pluck('object_id');

                $collections = $collections->whereIn('id', $collectionsIds);
            }
        }

        $collections = $collections->orderByDesc('created_at');

        return $this->returnPagination($collections);
    }

    protected function returnPagination($collections)
    {
        $page = request('page') ?: 1;
        $perPage = request('perpage', 10);
        $total = $collections->count();
        $options = [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
            'query' => request()->all()
        ];

        /**
         * @var $collections Builder|\Illuminate\Database\Eloquent\Collection
         * @var $forPage Builder|Collection
         */
        $forPage = $collections->forPage($page, $perPage);

        $collections = new LengthAwarePaginator(
            $forPage instanceof Collection ? $forPage :
                $forPage->get(),
            $total,
            $perPage,
            $page,
            $options
        );

        return CollectionsResource::collection($collections);
    }
}
