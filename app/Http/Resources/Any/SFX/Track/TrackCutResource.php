<?php

namespace App\Http\Resources\Any\SFX\Track;

use Illuminate\Http\Request;
use App\Models\SFX\SFXTrack;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TrackCutResource",
 *     title="TrackCutResource",
 *     @OA\Property(property="id_H", type="string", example="EjOZo5"),
 *     @OA\Property(property="audio_link", type="string", example="https://static.taketones.com/sfx/mp3/accelerating-spinning-whoosh.mp3"),
 * )
 */
class TrackCutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /**
         * @var $resource SFXTrack
         */
        $resource = $this->resource;

        if (!$resource) {
            return null;
        }

        return [
            'id_H' => $resource->getHashedKey(),
            'audio_link' => $resource->audio_link,
        ];
    }
}
