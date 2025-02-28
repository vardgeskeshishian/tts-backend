<?php

namespace App\Http\Resources;

use App\Enums\TypeContentEnum;
use App\Models\Structure\TemplateMeta;
use App\Models\VideoEffects\VideoEffect;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Schema(
 *     schema="VideoEffectSearchResource",
 *     title="VideoEffectSearchResource",
 *     @OA\Property(property="id", type="string", example="140391"),
 *     @OA\Property(property="slug", type="string", example="beautiful-story"),
 *     @OA\Property(property="name", type="string", example="Beautiful Story"),
 *     @OA\Property(property="description", type="string", example="It’s a positive tropical pop/hip-hop track for travel and summer videos."),
 *     @OA\Property(property="zip_id", type="string", example="87"),
 *     @OA\Property(property="preview_photo_id", type="string", example="12"),
 *     @OA\Property(property="preview_video_id", type="string", example="2023"),
 *     @OA\Property(property="associated_music_name", type="string", example="Dark Matter"),
 *     @OA\Property(property="downloads_by_period", type="integer"),
 *     @OA\Property(property="downloads_sum_by_period", type="integer"),
 *     @OA\Property(property="cloud", type="string"),
 *     @OA\Property(property="emc", type="integer"),
 *     @OA\Property(property="tmc", type="integer"),
 *     @OA\Property(property="n", type="integer"),
 *     @OA\Property(property="trend", type="integer"),
 *     @OA\Property(property="w_emc", type="integer"),
 *     @OA\Property(property="w_tmc", type="integer"),
 *     @OA\Property(property="w_n", type="integer"),
 *     @OA\Property(property="w_trend", type="integer"),
 *     @OA\Property(property="trending", type="integer"),
 * )
 */
class VideoEffectSearchResource extends JsonResource
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
        $folders = $res->folders->where('user_id', auth()->user()?->id);
        $template = Cache::remember(TypeContentEnum::VIDEO_EFFECT->value, Carbon::now()->addDay(), function () {
            return TemplateMeta::where('type', TypeContentEnum::VIDEO_EFFECT->getClass())->first();
        });

        return [
            'id' => $res->id,
            'name' => $res->name,
            'slug' => $res->slug,
//            'description' => $res->description,
//            'status' => $res->status,
            'author' => $res->author?->name,
            'author_id' => $res->author?->id,
            'author_slug' => $res->author?->slug,
            'zip' => $res->zip,
            'video_preview' => $res->preview,
            'picture' => !is_null($res->preview_photo)
            && file_exists(base_path().'/public_html'.$res->preview_photo)
                ? $res->preview_photo
                : $template->image,
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
//            'resolutions' => $res->resolutions?->map(fn($item) => [
//                'id' => $item->id,
//                'name' => $item->name,
//                'slug' => $item->slug
//            ]),
//            'plugin' => $res->plugins?->map(fn($item) => [
//                'id' => $item->id,
//                'name' => $item->name,
//                'slug' => $item->slug
//            ]),
            'version' => [
                'id' => $res->version?->id,
                'name' => $res->version?->name,
            ],
//            'tags' => $res->tags?->map(fn($item) => [
//                'id' => $item->id,
//                'name' => $item->name,
//                'slug' => $item->slug
//            ]),
            'folders' => !is_null($folders)
                ? $folders->map(fn($item) => [
                    'id' => $item->id,
                    'title' => $item->title
                ]) : null,
//            'meta_title' => $res->meta_title,
//            'meta_description' => $res->meta_description,
//            'created_at' => $this->created_at->timestamp,
//            'updated_at' => $this->updated_at->timestamp,
//            'isFeatured' => $res->featured,
//            'isHidden' => $res->hidden,
//            'isContentId' => $res->has_content_id,
//            'isExclusive' => $res->exclusive,
            'isNew' => $res->new,
//            'tmc' => $res->tmc,
//            'downloads' => $res->count_downloads,
//            'trending' => $res->trending,
        ];
    }
}
