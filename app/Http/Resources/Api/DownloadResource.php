<?php

namespace App\Http\Resources\Api;

use App\Enums\TypeContentEnum;
use App\Models\UserDownloads;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DownloadResource",
 *     title="DownloadResource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="content_id", type="integer"),
 *     @OA\Property(property="content_name", type="integer"),
 *     @OA\Property(property="content_slug", type="string"),
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="license_number", type="string"),
 *     @OA\Property(property="created_at", type="string"),
 * )
 */
class DownloadResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        /**
         * @var $res UserDownloads
         */
        $res = $this->resource;

        $content = $res->downloadable;
        $class = TypeContentEnum::getTypeContent($res->class);

        $resourse = TypeContentEnum::getResourseContent($content, $res->class);

        return [
            'id' => $res->id,
            'content' => $resourse,
            'type' => $class,
            'created_at' => $res->created_at,
            'licenseLink' => $content?->url()
        ];
    }
}
