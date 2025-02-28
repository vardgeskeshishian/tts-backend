<?php

namespace App\Http\Resources\Api;

use App\Models\Authors\AuthorProfile;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="AnyAuthorResource",
 *     title="AnyAuthorResource",
 *     @OA\Property(property="id", type="string", example="140391"),
 *     @OA\Property(property="name", type="string", example="Jam Morgan"),
 *     @OA\Property(property="description", type="string", example="Royalty free music"),
 *     @OA\Property(property="user", type="object",
 *          @OA\Property(property="id", type="integer"),
 *          @OA\Property(property="name", type="string"),
 *          @OA\Property(property="email", type="string"),
 *     ),
 *     @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *     @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
 *     @OA\Property(property="slug", type="string", example="isnazarov-roman"),
 *     @OA\Property(property="is_track", type="boolean", example="true"),
 *     @OA\Property(property="is_video", type="boolean", example="true"),
 *     @OA\Property(property="meta", type="object",
 *          @OA\Property(property="title", type="string"),
 *          @OA\Property(property="description", type="string"),
 *          @OA\Property(property="keywords", type="string"),
 *     ),
 *     @OA\Property(property="background", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
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
            'user' => [
                'id' => $res->user?->id,
                'name' => $res->user?->name,
                'email' => $res->user?->email,
            ],
            'slug' => $res->slug,
            'is_track' => $res->is_track,
            'is_video' => $res->is_video,
            'meta' => [
                'title' => $res->metaTitle,
                'description' => $res->metaDescription,
            ],
            'background' => $res->background?->url
        ];
    }
}
