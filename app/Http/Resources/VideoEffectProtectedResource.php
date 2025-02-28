<?php

namespace App\Http\Resources;

use JsonSerializable;
use Illuminate\Http\Request;
use App\Models\VideoEffects\VideoEffect;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="VideoEffectProtectedResource",
 *     title="VideoEffectProtectedResource",
 *     @OA\Property(property="id", type="string", example="140391"),
 *     @OA\Property(property="name", type="string", example="MyVideo"),
 *     @OA\Property(property="slug", type="string", example="myvideo"),
 *     @OA\Property(property="description", type="string", example="asdasdas"),
 *     @OA\Property(property="zip_id", type="integer", example="5"),
 *     @OA\Property(property="preview_photo_id", type="integer", example="5"),
 *     @OA\Property(property="preview_video_id", type="integer", example="5"),
 *     @OA\Property(property="associated_music_name", type="string", example="sdfsd"),
 *     @OA\Property(property="exclusivity", type="integer", example="0"),
 *     @OA\Property(property="author", type="object",
 *          ref="/components/schemas/Author"
 *     ),
 *     @OA\Property(property="status", type="string", example="NEW"),
 *     @OA\Property(property="application", type="object",
 *          ref="/components/schemas/VideoEffectApplicationResource"
 *     ),
 *     @OA\Property(property="versions", type="array", @OA\Items(
 *          @OA\Property(property="id", type="integer", example="7"),
 *          @OA\Property(property="name", type="string", example="After Effects CS6+"),
 *     )),
 * )
 */
class VideoEffectProtectedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     *
     * @return array
     */
    public function toArray($request)
    {
        /**
         * @var $res VideoEffect
         */
        $res = $this;
        $data = parent::toArray($request);
        return [
            $this->merge($data),
            'exclusivity' => $res->exclusivity,
            'zip' => $res->zip,
            'image' => $res->preview_photo,
            'video' => $res->preview_video,
            'author' => $res->author,
            'status' => $res->status,
            'application' => $res->application ? new VideoEffectApplicationResource($res->application) : null,
            'plugin' => $res->plugin,
            'version' => $res->version,
            'category' => $res->category,
            'resolution' => $res->resolution,
            'categories' => $res->getCategories(),
            'plugins' => $res->getPlugins(),
            'resolutions' => $res->getResolutions(),
            'tags' => $res->tags,
            'comments' => $res->comments,
            'prices' => $res->getPricesAttribute(),
//            'tags2' => $res->getSlugTags(),
            'created_at' => $res->created_at->format('U'),
            'updated_at' => $res->updated_at ? $res->updated_at->format('U') : null,
            'associated_music' => $res->associated_music,
        ];
    }
}
