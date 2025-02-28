<?php

namespace App\Http\Resources\Any\SFX;

use App\Models\SFX\SFXPack;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="PackResource",
 *     title="PackResource",
 *     @OA\Property(property="id", type="integer", example="12"),
 *     @OA\Property(property="name", type="string", example="Shine Pack"),
 *     @OA\Property(property="description", type="string", example="Easy and positive sounds for fun projects"),
 *     @OA\Property(property="tracks", type="array", @OA\Items(
 *          @OA\Property(property="id", type="integer", example="7"),
 *          @OA\Property(property="name", type="string", example="Shine"),
 *          @OA\Property(property="category", type="object",
 *              @OA\Property(property="name", type="string", example="Whooshes"),
 *              @OA\Property(property="slug", type="string", example="whooshes"),
 *          ),
 *          @OA\Property(property="tags", type="array", @OA\Items(
 *              @OA\Property(property="name", type="string", example="Percussion"),
 *              @OA\Property(property="slug", type="string", example="percussion"),
 *          )),
 *          @OA\Property(property="audio", type="string", example="https://static.taketones.com/sfx/mp3/shine.mp3"),
 *          @OA\Property(property="waveform", type="string", example="[1, 3, 7, 8]"),
 *          @OA\Property(property="waveform_link", type="string", example="https://static.taketones.com/sfx/waveforms/0AKJ0j.json"),
 *          @OA\Property(property="duration", type="float", example="1,26"),
 *          @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *     )),
 *     @OA\Property(property="images", type="object",
 *          @OA\Property(property="background", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
 *          @OA\Property(property="thumbnail", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
 *     ),
 * )
 */
class PackResource extends JsonResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        if (!$this->resource) {
            return [];
        }

        /**
         * @var $res SFXPack
         */
        $res = $this->resource;

        return [
            'id' => $res->id,
            'name' => $res->name,
            'description' => $res->description,
            'tracks' => TrackResource::collection($res->tracks),
            'tracks_count' => $res->tracks->count(),
            'images' => $res->getImages(),
        ];
    }
}
