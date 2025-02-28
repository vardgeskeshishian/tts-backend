<?php

namespace App\Http\Resources\Authorized;

use App\Models\Authors\AuthorProfile;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="AuthorizedAuthorResource",
 *     title="AuthorizedAuthorResource",
 *     @OA\Property(property="id", type="string", example="140391"),
 *     @OA\Property(property="name", type="string", example="Jam Morgan"),
 *     @OA\Property(property="description", type="string", example="Royalty free music"),
 *     @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *     @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
 *     @OA\Property(property="slug", type="string", example="isnazarov-roman"),
 *     @OA\Property(property="is_track", type="boolean", example="true"),
 *     @OA\Property(property="is_video", type="boolean", example="true"),
 *     @OA\Property(property="meta", type="object",
 *          @OA\Property(property="title", type="string"),
 *          @OA\Property(property="description", type="string"),
 *     ),
 *     @OA\Property(property="images", type="object",
 *          @OA\Property(property="background", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
 *          @OA\Property(property="thumbnail", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
 *     ),
 * )
 */
class AuthorResource extends JsonResource
{
    public function toArray($request): array
    {
        /**
         * @var $res AuthorProfile
         */
        $res = $this->resource;

        return [
            'id' => $res->id,
            'name' => $res->name,
            'description' => $res->description,
            'slug' => $res->slug,
            'is_track' => $res->is_track,
            'is_video' => $res->is_video,
            'created_at' => $res->created_at?->toDateTimeString(),
            'updated_at' => $res->updated_at?->toDateTimeString(),
            'meta' => [
                'title' => $res->metaTitle,
                'description' => $res->metaDescription,
            ],
            'images' => $res->getImages()
        ];
    }
}
