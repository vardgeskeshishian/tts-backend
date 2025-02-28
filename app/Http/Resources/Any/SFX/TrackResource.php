<?php


namespace App\Http\Resources\Any\SFX;

use Exception;
use Illuminate\Http\Request;
use App\Models\SFX\SFXTrack;
use App\Services\TaggingService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SFXTrackResource",
 *     title="SFXTrackResource",
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
 *     @OA\Property(property="params", type="object",
            @OA\Property(property="id", type="string", example="140391"),
 *          @OA\Property(property="sfx_track_id", type="string", example="102"),
 *          @OA\Property(property="bought", type="string", example="9"),
 *          @OA\Property(property="downloads_by_subscription", type="string", example="6"),
 *          @OA\Property(property="bought_in_packs", type="string", example="6"),
 *          @OA\Property(property="album", type="string", example="FXPro"),
 *          @OA\Property(property="artist", type="string", example="TakeTones"),
 *          @OA\Property(property="bit_rate", type="string", example="1417499"),
 *          @OA\Property(property="in_pack", type="string", example="0"),
 *          @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *          @OA\Property(property="updated_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *          @OA\Property(property="synced_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *          @OA\Property(property="ftp_link", type="string", example="https://static.taketones.com/sfx/mp3/cosmic-shimmer-emerging-transition.mp3"),
 *     ),
 * )
 */
class TrackResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     * @throws Exception
     */
    public function toArray($request): array
    {
        /**
         * @var $res SFXTrack
         */
        $res = $this->resource;

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
            'isPremium' => $res->premium,
            'created_at' => $res->created_at,
        ];
    }
}
