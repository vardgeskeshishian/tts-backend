<?php


namespace App\Http\Resources\Api;

use App\Models\Structure\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CollectionsResource",
 *     title="CollectionsResource",
 *     @OA\Property(property="id", type="integer", example="12"),
 *     @OA\Property(property="name", type="string", example="Trendy Lofi Beats"),
 *     @OA\Property(property="description", type="string", example="Royalty Free LoFi Beats Music Collection."),
 *     @OA\Property(property="price", type="float", example="10.00"),
 *     @OA\Property(property="url", type="string", example="trendy-lofi-beats"),
 *     @OA\Property(property="meta", type="object",
 *          @OA\Property(property="title", type="string", example="Computer for Adobe Premiere Pro: Requirements and Specs"),
 *          @OA\Property(property="description", type="string", example="Adobe Premiere Pro is the best software for editing movies"),
 *     ),
 *     @OA\Property(property="images", type="object",
 *          @OA\Property(property="background", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
 *          @OA\Property(property="thumbnail", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
 *     ),
 *     @OA\Property(property="tags", type="object",
 *          @OA\Property(property="tags", type="array", @OA\Items(
 *              @OA\Property(property="name", type="string", example="Soft"),
 *              @OA\Property(property="slug", type="string", example="soft"),
 *          )),
 *     ),
 *     @OA\Property(property="track", type="array", @OA\Items(
 *          @OA\Property(property="id", type="integer", example="820"),
 *          @OA\Property(property="slug", type="string", example="mirage"),
 *          @OA\Property(property="name", type="string", example="Mirage"),
 *          @OA\Property(property="author_name", type="string", example="EdRecords"),
 *          @OA\Property(property="description", type="string", example="Chill hiphop for YouTube content"),
 *          @OA\Property(property="tempo", type="string", example="91"),
 *          @OA\Property(property="duration", type="string", example="12.01"),
 *          @OA\Property(property="downloads", type="integer", example="123"),
 *          @OA\Property(property="price", type="float", example="10.01"),
 *          @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *          @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
 *          @OA\Property(property="sales_count", type="integer", example="123"),
 *          @OA\Property(property="is_free", type="boolean", example="false"),
 *          @OA\Property(property="has_content_id", type="boolean", example="false"),
 *          @OA\Property(property="full_name", type="string", example="Santa is Coming by StudioKolomna"),
 *     )),
 *     @OA\Property(property="hidden", type="boolean", example="false"),
 * )
 */
class CollectionsResource extends JsonResource
{
    public function toArray($request): array
    {
        /**
         * @var $res Collection
         */
        $res = $this->resource;

        return [
            'id' => $res->id,
            'name' => $res->name,
            'description' => $res->description,
            'price' => $res->price,
            'url' => $res->url,
            'meta' => $res->getMeta(),
            'images' => $res->getImages(),
            'tags' => $res->getAllTags(),
            'tracks' => $res->tracks,
            'hidden' => $res->hidden,
        ];
    }
}
