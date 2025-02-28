<?php

namespace App\Http\Resources\Api;

use App\Enums\TypeContentEnum;
use App\Models\Structure\TemplateMeta;
use App\Models\VideoEffects\VideoEffect;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class VideoEffectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        /**
         * @var $res VideoEffect
         */
        $res = $this;

        return [
            'id' => $res->id,
            'name' => $res->name,
            'slug' => $res->slug,
            'description' => $res->description,
            'status' => $res->status,
            'author' => $res->author?->name,
            'author_id' => $res->author?->id,
            'zip' => $res->zip,
            'video_preview' => $res->preview_video,
            //'picture' => $res->preview_photo,
			'picture' => !is_null($res->preview_photo)
			&& file_exists(base_path().'/public_html'.$res->preview_photo)
				? $res->preview_photo
				: Cache::remember(TypeContentEnum::VIDEO_EFFECT->value, Carbon::now()->addDay(), function () {
					return TemplateMeta::where('type', TypeContentEnum::VIDEO_EFFECT->getClass())->first()?->image;
				}),
            'application' => [
                'id' => $res->application?->id,
                'name' => $res->application?->name,
                'slug' => $res->application?->slug
            ],
            'categories' => $res->categories?->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug
            ]),
            'resolutions' => $res->resolutions?->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug
            ]),
            'plugin' => $res->plugins?->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug
            ]),
            'version' => [
                'id' => $res->version?->id,
                'name' => $res->version?->name,
            ],
            'tags' => $res->tags?->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug
            ]),
            'folders' => count($res->folders) > 0 ? $res->folders->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->title
            ]) : null,
            'meta_title' => $res->metaTitle,
            'meta_description' => $res->metaDescription,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
            'isPremium' => $res->premium,
            'isFeatured' => $res->featured,
            'isHidden' => $res->hidden,
            'isContentId' => $res->has_content_id,
            'isExclusive' => $res->exclusive,
            'isNew' => $res->new,
        ];
    }
}
