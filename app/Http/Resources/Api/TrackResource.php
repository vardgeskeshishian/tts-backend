<?php

namespace App\Http\Resources\Api;

use App\Enums\TypeContentEnum;
use App\Models\Structure\TemplateMeta;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="TrackApiResource",
 *     title="TrackApiResource",
 *     @OA\Property(property="id", type="integer", example="12"),
 *     @OA\Property(property="name", type="string", example="Dark Urban Souls"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="waveform", type="string"),
 *     @OA\Property(property="author", type="object",
 *          @OA\Property(property="id", type="integer"),
 *          @OA\Property(property="name", type="string"),
 *     ),
 *     @OA\Property(property="zip", type="string"),
 *     @OA\Property(property="image", type="object",
 *          @OA\Property(property="background", type="string"),
 *          @OA\Property(property="thumbnail", type="string"),
 *     ),
 *     @OA\Property(property="bpm", type="integer"),
 *     @OA\Property(property="loss_mp3", type="object",
 *          @OA\Property(property="url", type="string"),
 *          @OA\Property(property="duration", type="string"),
 *     ),
 *     @OA\Property(property="hq_mp3", type="object",
 *          @OA\Property(property="url", type="string"),
 *          @OA\Property(property="duration", type="string"),
 *     ),
 *     @OA\Property(property="wav", type="object",
 *          @OA\Property(property="url", type="string"),
 *          @OA\Property(property="duration", type="string"),
 *     ),
 *     @OA\Property(property="genres", type="object",
 *          @OA\Property(property="id", type="integer"),
 *          @OA\Property(property="name", type="string"),
 *          @OA\Property(property="slug", type="string"),
 *     ),
 *     @OA\Property(property="moods", type="object",
 *          @OA\Property(property="id", type="integer"),
 *          @OA\Property(property="name", type="string"),
 *          @OA\Property(property="slug", type="string"),
 *     ),
 *     @OA\Property(property="instruments", type="object",
 *          @OA\Property(property="id", type="integer"),
 *          @OA\Property(property="name", type="string"),
 *          @OA\Property(property="slug", type="string"),
 *     ),
 *     @OA\Property(property="usage_types", type="object",
 *          @OA\Property(property="id", type="integer"),
 *          @OA\Property(property="name", type="string"),
 *          @OA\Property(property="slug", type="string"),
 *     ),
 *     @OA\Property(property="tags", type="object",
 *          @OA\Property(property="id", type="integer"),
 *          @OA\Property(property="name", type="string"),
 *          @OA\Property(property="slug", type="string"),
 *     ),
 *     @OA\Property(property="meta", type="object",
 *          @OA\Property(property="title", type="string"),
 *          @OA\Property(property="description", type="string"),
 *     ),
 *     @OA\Property(property="hidden", type="boolean", example="true"),
 *     @OA\Property(property="featured", type="boolean", example="true"),
 *     @OA\Property(property="has_content_id", type="boolean", example="true"),
 *     @OA\Property(property="exclusive", type="boolean", example="true"),
 *     @OA\Property(property="premium", type="boolean", example="true"),
 *     @OA\Property(property="created_at", type="integer", example="1626864449"),
 *     @OA\Property(property="updated_at", type="integer", example="1658051311"),
 * )
 */
class TrackResource extends JsonResource
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
            'bpm' => $res->tempo,
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
            'tags' => $res->tags?->where('is_category', 0)->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug
            ]),
            'folders' => count($res->folders) > 0 ? $res->folders->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->title
            ]) : null,
            'meta_title' => $res->metaTitle ?? str_replace('%Track_Name%', $res->name, $template->metaTitle),
            'meta_description' => $res->metaDescription ?? str_replace('%Track_Name%', $res->name, $template->metaDescription),
//            'created_at' => $this->created_at->timestamp,
//            'updated_at' => $this->updated_at->timestamp,
            'isPremium' => $res->premium,
//            'isFeatured' => $res->featured,
//            'isHidden' => $res->hidden,
//            'isContentId' => $res->has_content_id,
//            'isExclusive' => $res->exclusive,
            'isNew' => $res->new,
//			'avatar_name' => $res->avatar_name,
//			'isCommercial' => $res->is_commercial,
//			'isOrfium' => $res->is_orfium,
        ];
    }
}
