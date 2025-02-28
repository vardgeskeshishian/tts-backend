<?php

namespace App\Http\Resources\Api;

use App\Models\Structure\Blog;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="BlogResource",
 *     title="BlogResource",
 *     @OA\Property(property="id", type="integer", example="12"),
 *     @OA\Property(property="slug", type="string", example="computer-for-adobe-premiere-pro-requirements-and-specs"),
 *     @OA\Property(property="title", type="string", example="Computer for Adobe Premiere Pro: Requirements and Specs"),
 *     @OA\Property(property="short_description", type="string", example="Adobe Premiere Pro is the best software for editing movies"),
 *     @OA\Property(property="body", type="string", example="<p dir=ltr>Adobe Premiere Pro is the best software for editing movies</p>"),
 *     @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *     @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
 *     @OA\Property(property="featured", type="boolean", example="false"),
 *     @OA\Property(property="author_id", type="integer", example="10"),
 *     @OA\Property(property="images", type="object",
 *          @OA\Property(property="background", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
 *          @OA\Property(property="thumbnail", type="string", example="https://static.taketones.com/f/images/1537e2849d4c33774ca1e1d8f99c628c.jpeg"),
 *     ),
 *     @OA\Property(property="meta", type="object",
 *          @OA\Property(property="title", type="string", example="Computer for Adobe Premiere Pro: Requirements and Specs"),
 *          @OA\Property(property="description", type="string", example="Adobe Premiere Pro is the best software for editing movies"),
 *          @OA\Property(property="keywords", type="object")
 *     ),
 *     @OA\Property(property="tags", type="object",
 *          @OA\Property(property="tags", type="array", @OA\Items(
 *              @OA\Property(property="name", type="string", example="After-Effects"),
 *              @OA\Property(property="slug", type="string", example="after-effects"),
 *          ))
 *     ),
 *     @OA\Property(property="categories", type="array", @OA\Items(
 *          @OA\Property(property="name", type="string", example="Video"),
 *          @OA\Property(property="slug", type="string", example="Video"),
 *          @OA\Property(property="order", type="string", example="4"),
 *     )),
 *     @OA\Property(property="author", type="object",
 *          @OA\Property(property="id", type="string", example="1"),
 *          @OA\Property(property="name", type="string", example="Paul Keane"),
 *          @OA\Property(property="slug", type="string", example="paul-keane"),
 *          @OA\Property(property="social_links", type="array", @OA\Items(
 *              @OA\Property(property="id", type="integer", example="4"),
 *              @OA\Property(property="object_class", type="string", example="App\\Models\\BlogAuthor"),
 *              @OA\Property(property="object_id", type="integer", example="4"),
 *              @OA\Property(property="social_link_id", type="integer", example="4"),
 *              @OA\Property(property="social_link_url", type="string", example="https://www.facebook.com/paul.keane.56829"),
 *              @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *              @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
 *              @OA\Property(property="link", type="object",
 *                  @OA\Property(property="id", type="string", example="4"),
 *                  @OA\Property(property="name", type="string", example="Facebook"),
 *                  @OA\Property(property="slug", type="string", example="Facebook"),
 *              )
 *          )),
 *     ),
 * )
 */
class BlogResource extends JsonResource
{
    public function toArray($request)
    {
        /**
         * @var $res Blog
         */
        $res = $this->resource;

        return [
            'id' => $res->id,
            $this->merge($res->toArray()),
            'images' => $res->getImages(),
            'meta' => $res->getMeta(),
            'tags' => $res->getAllTags(),
            'categories' => $res->categories,
            'author' => $res->author,
        ];
    }
}
