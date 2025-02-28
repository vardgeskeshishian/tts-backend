<?php

namespace App\Http\Resources\Any\SFX;

use App\Enums\TypeContentEnum;
use App\Models\SFX\SFXTrack;
use App\Models\Structure\TemplateMeta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Schema(
 *     schema="TrackSfxSearchResource",
 *     title="TrackSfxSearchResource",
 *     @OA\Property(property="id", type="string", example="140391"),
 *     @OA\Property(property="slug", type="string", example="beautiful-story"),
 *     @OA\Property(property="name", type="string", example="Beautiful Story"),
 *     @OA\Property(property="extension", type="string", example="wav"),
 *     @OA\Property(property="price", type="string", example="87.00"),
 *     @OA\Property(property="duration", type="string", example="12.00"),
 *     @OA\Property(property="link", type="string", example="/sfx/audio/cosmic-shimmer-emerging-transition.wav"),
 *     @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *     @OA\Property(property="updated_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *     @OA\Property(property="deleted_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *     @OA\Property(property="audio_link", type="string", example="https://static.taketones.com/sfx/mp3/cosmic-shimmer-emerging-transition.mp3"),
 *     @OA\Property(property="waveform_link", type="string", example="https://static.taketones.com/sfx/waveforms/RBY9lB.json"),
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
class TrackSfxSearchResource extends JsonResource
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
         * @var $res SFXTrack
         */
        $res = $this->resource;
        $folders = $res->folders->where('user_id', auth()->user()?->id);

        $template = Cache::remember(TypeContentEnum::SFX->value, Carbon::now()->addDay(), function () {
            return TemplateMeta::where('type', TypeContentEnum::SFX->getClass())->first();
        });

        return [
            'id' => $res->id,
            'name' => $res->name,
            'slug' => $res->slug,
            'sfx_file' => $res->link,
            'waveform' => $res->waveform,
            'categories' => $res->sfxCategories?->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug
            ]),
            'tags' => $res->sfxTags?->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug
            ]),
            'folders' => !is_null($folders)
                ? $folders->map(fn($item) => [
                    'id' => $item->id,
                    'title' => $item->title
                ]) : null,
//            'isNew' => $res->is_new,
            'isPremium' => $res->premium,
            'duration' => $res->duration,
//            'created_at' => $res->created_at,
//            'downloads' => $res->count_downloads,
//            'tmc' => $res->tmc,
//            'trending' => $res->trending,
            'default_picture' => $template?->image
        ];
    }
}