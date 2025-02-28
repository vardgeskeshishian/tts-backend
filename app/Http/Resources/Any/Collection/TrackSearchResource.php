<?php

namespace App\Http\Resources\Any\Collection;

use App\Actions\GetAllCategoryTags;
use App\Enums\TypeContentEnum;
use App\Models\Structure\TemplateMeta;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 *  * @OA\Schema(
 *     schema="TrackSearchResource",
 *     title="TrackSearchResource",
 *     @OA\Property(property="id", type="string", example="140391"),
 *     @OA\Property(property="slug", type="string", example="beautiful-story"),
 *     @OA\Property(property="name", type="string", example="Beautiful Story"),
 *     @OA\Property(property="author_name", type="string", example="Mike Cosmo"),
 *     @OA\Property(property="description", type="string", example="It’s a positive tropical pop/hip-hop track for travel and summer videos."),
 *     @OA\Property(property="images", type="array",
 *          @OA\Items(type="string")
 *     ),
 *     @OA\Property(property="audio", type="array",
 *          @OA\Items(type="object",
 *              @OA\Property(property="id", type="integer"),
 *              @OA\Property(property="url", type="string"),
 *              @OA\Property(property="name", type="string"),
 *              @OA\Property(property="duration", type="string"),
 *              @OA\Property(property="format", type="string"),
 *              @OA\Property(property="type", type="string")
 *          )
 *     ),
 *     @OA\Property(property="preview", type="array",
 *          @OA\Items(type="object",
 *              @OA\Property(property="id", type="integer"),
 *              @OA\Property(property="url", type="string"),
 *              @OA\Property(property="name", type="string"),
 *              @OA\Property(property="duration", type="string"),
 *              @OA\Property(property="format", type="string"),
 *              @OA\Property(property="type", type="string")
 *          )
 *     ),
 *     @OA\Property(property="is_favorite", type="boolean"),
 *     @OA\Property(property="position", type="integer"),
 *     @OA\Property(property="tempo", type="string", example="87"),
 *     @OA\Property(property="duration", type="string", example="12.0123"),
 *     @OA\Property(property="downloads", type="string", example="2023"),
 *     @OA\Property(property="price", type="string", example="12.21"),
 *     @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
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
class TrackSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /**
         * @var $res Track
         */
        $res = $this->resource;

        $hqFile = $res->audio?->where('type','mp3')
            ->where('is_hq', '=', 1)->first();
        $lossFile = $res->audio?->where('type','mp3')
            ->where('is_hq', 0)->first();
        $wav = $res->audio?->where('type', 'wav')->first();

        if ($hqFile) {
            $waveform = $hqFile->waveform;
        } else if ($lossFile) {
            $waveform = $lossFile->waveform;
        } else {
            $waveform = $wav?->waveform;
        }
		
		$tagExcludeList = (new GetAllCategoryTags())->handle();
        $folders = $res->folders->where('user_id', auth()->user()?->id);

        $template = Cache::remember(TypeContentEnum::TRACK->value, Carbon::now()->addDay(), function () {
            return TemplateMeta::where('type', TypeContentEnum::TRACK->getClass())->first();
        });

        return [
            'id' => $res->id,
            'name' => $res->name,
            'description' => $res->description,
            'slug' => $res->slug,
            'author' => $res->author?->name,
            'author_id' => $res->author?->id,
            'author_slug' => $res->author?->slug,
            'waveform' => $waveform,
            'zip' => is_null($res->archive?->path) ? null : '/storage'.$res->archive?->path,
            'picture' => !is_null($res->background?->url)
            && file_exists(base_path().'/public_html'.$res->background->url)
                ? $res->background->url
                : $template->image,
//            'bpm' => $res->tempo,
            'loss_mp3' => $lossFile?->url,
            'hq_mp3' => $hqFile?->url,
            'wav' => $wav?->url,
            'duration' => $lossFile?->duration,
            'genres' => $res->genres?->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug
            ]),
            'moods' => $res->moods?->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug
            ]),
            'instruments' => $res->instruments?->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug
            ]),
            'usageTypes' => $res->types?->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug
            ]),
            'tags' => $res->tags?->where('is_category', 0)
                ->filter(fn($item) => !in_array($item->slug, $tagExcludeList))->map(fn($item) => [
				'id' => $item->id,
				'name' => $item->name,
				'slug' => $item->slug
			])->values(),
            'folders' => !is_null($folders)
                ? $folders->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->title
            ]) : null,
//            'meta_title' => $res->metaTitle,
//            'meta_description' => $res->metaDescription,
//            'created_at' => $this->created_at->timestamp,
//            'updated_at' => $this->updated_at->timestamp,
            'isPremium' => $res->premium,
//            'isFeatured' => $res->featured,
//            'isHidden' => $res->hidden,
//            'isContentId' => $res->has_content_id,
//            'isExclusive' => $res->exclusive,
//            'isNew' => $res->new,
//            'tmc' => $res->tmc,
//            'downloads' => $res->count_downloads,
//            'trending' => $res->trending,
//			'avatar_name' => $res->avatar_name,
//			'isCommercial' => $res->is_commercial,
//			'isOrfium' => $res->is_orfium,
        ];
    }
}
