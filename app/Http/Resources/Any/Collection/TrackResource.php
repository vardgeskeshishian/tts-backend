<?php

namespace App\Http\Resources\Any\Collection;

use App\Models\Track;
use App\Models\TrackPrice;
use CacheServiceFacade;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TrackResource",
 *     title="TrackResource",
 *     @OA\Property(property="id", type="string", example="140391"),
 *     @OA\Property(property="slug", type="string", example="beautiful-story"),
 *     @OA\Property(property="name", type="string", example="Beautiful Story"),
 *     @OA\Property(property="author_name", type="string", example="Mike Cosmo"),
 *     @OA\Property(property="description", type="string", example="It’s a positive tropical pop/hip-hop track for travel and summer videos."),
 *     @OA\Property(property="tempo", type="string", example="87"),
 *     @OA\Property(property="duration", type="string", example="12.0123"),
 *     @OA\Property(property="downloads", type="string", example="2023"),
 *     @OA\Property(property="price", type="string", example="12.21"),
 *     @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *     @OA\Property(property="updated_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *     @OA\Property(property="sales_count", type="string", example="57"),
 *     @OA\Property(property="premium", type="string", example="false"),
 *     @OA\Property(property="has_content_id", type="string", example="false"),
 *     @OA\Property(property="full_name", type="string", example="Beautiful Story by Mike Cosmo"),
 * )
 *
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
    public function toArray($request)
    {
        /**
         * @var $res Track
         */
        $res = $this->resource;

        $list = $res->getTagsOfType('tags');
        $lastTags = array_slice($list, 0, 4);

        $licenses = CacheServiceFacade::getFreeLicenses();

        $prices = [];

        foreach ($licenses as $licens) {
            $isLocalPrice = false;
            foreach ($res->prices as $itemPrice) {
                if ($itemPrice->license_id === $licens->id) {
                    $prices[] = $itemPrice;
                    $isLocalPrice = true;
                }
            }

            if ($isLocalPrice) {
                continue;
            }

            if ($licens->standard->discount > 0) {
                $price = new TrackPrice();
                $price->track_id = $res->id;
                $price->license_id = $licens->id;
                $price->price = $licens->info->discount;

                $prices[] = $price;
            }
        }

        $showRel = auth()->check() && auth()->user()->isAdmin;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $res->description,
            'author_name' => optional($res->author)->name ?? "",
            'images' => $res->getImages(),
            'tags' => $lastTags,
            'audio' => $res->getAudioListWithFullWaveForm('wav'),
            'preview' => $res->getAudioListWithFullWaveForm('mp3'),
            'is_favorite' => $res->isFavored(),
            'prices' => $prices,
            'tempo' => $res->tempo,
            'extra' => [
                'premium' => $res->premium,
                'has_content_id' => $res->has_content_id,
            ],
            'created_at' => $res->created_at,
            'r' => $showRel ? $res->getAttribute('relevancy') : [],
            'fr' => $showRel ? $res->getAttribute('full-track-rel') : []
        ];
    }
}
