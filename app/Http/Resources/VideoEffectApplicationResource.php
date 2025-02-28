<?php

namespace App\Http\Resources;

use App\Models\Structure\TemplateMeta;
use App\Models\VideoEffects\VideoEffectApplication;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="VideoEffectApplicationResource",
 *     title="VideoEffectApplicationResource",
 *     @OA\Property(property="id", type="string"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="h1", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="meta", type="object",
 *          @OA\Property(property="title", type="string"),
 *          @OA\Property(property="description", type="string"),
 *     ),
 *     @OA\Property(property="google_url", type="string"),
 *     @OA\Property(property="is_black", type="boolean"),
 *     @OA\Property(property="image", type="string"),
 *     @OA\Property(property="foreground_image", type="string"),
 * )
 */
class VideoEffectApplicationResource extends JsonResource
{
    public ?TemplateMeta $template = null;

    public function __construct($resource, $template = null)
    {
        $this->template = $template;
        parent::__construct($resource);
    }

    /**
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        /**
         * @var $res VideoEffectApplication
         */
        $res = $this->resource;

        return [
            'id' => $res->id,
            'name' => $res->name,
            'slug' => $res->slug,
            'h1' => is_null($res->h1) ? str_replace('%Category_Name%', $res->name,
                str_replace('%category_name%', Str::lower($res->name), $this->template?->h1)) : $res->h1,
            'description' => is_null($res->description) ? Str::ucfirst(str_replace('%Category_Name%', $res->name,
                str_replace('%category_name%', Str::lower($res->name), $this->template?->description))) : Str::ucfirst($res->description),
            'meta' => [
                'title' => is_null($res->metaTitle) ? str_replace('%Category_Name%', $res->name, $this->template?->metaTitle) : $res->metaTitle,
                'description' => is_null($res->metaDescription) ? str_replace('%Category_Name%', $res->name, $this->template?->metaDescription) : $res->metaDescription,
            ],
            'google_url' => $res->google_url,
            'is_black' => $res->is_black,
            'image' => !is_null($res->icon?->url)
            && file_exists(base_path().'/public_html'.$res->icon?->url)
                ? $res->icon?->url
                : $this->template?->image,
            'foreground_image' => $res->foreground?->first()?->url,
            'priority' => $res->priority
        ];
    }
}