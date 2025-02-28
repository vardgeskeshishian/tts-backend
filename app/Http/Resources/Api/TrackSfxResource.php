<?php

namespace App\Http\Resources\Api;

use App\Enums\TypeContentEnum;
use App\Models\SFX\SFXTrack;
use App\Models\Structure\TemplateMeta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;


class TrackSfxResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        /**
         * @var $res SFXTrack
         */
        $res = $this->resource;

        $template = Cache::remember(TypeContentEnum::SFX->value, Carbon::now()->addDay(), function () {
            return TemplateMeta::where('type', TypeContentEnum::SFX->getClass())->first();
        });

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
            'folders' => count($res->folders) > 0 ? $res->folders->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->title
            ]) : null,
            'isPremium' => $res->premium,
            'isNew' => $res->is_new,
            'default_picture' => $template?->image,
            'created_at' => $res->created_at,
        ];
    }
}