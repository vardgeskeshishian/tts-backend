<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoritesFolderContentResource extends JsonResource
{
    private string $type;

    public function type($value): static
    {
        $this->type = $value;
        return $this;
    }

    public function toArray(Request $request): array
    {
        if ($this->type == 'track') {
            return $this->track();
        }
        if ($this->type == 'sfxTrack') {
            return $this->sfxTrack();
        }
        if ($this->type == 'videoEffect') {
            return $this->videoEffect();
        }
        return ['type' => 'Unknown'];
    }

    private function track(): array
    {
        $resource = (new TrackResource($this))->toResponse(app('request'));
        return json_decode($resource->getContent(), true)['data'];
    }

    private function sfxTrack(): array
    {
        $resource = (new TrackSfxResource($this))->toResponse(app('request'));
        return json_decode($resource->getContent(), true)['data'];
    }

    private function videoEffect(): array
    {
        $resource = (new VideoEffectResource($this))->toResponse(app('request'));
        return json_decode($resource->getContent(), true)['data'];
    }

    public static function collection($resource): FavoritesFolderContentResourceCollection
    {
        return new FavoritesFolderContentResourceCollection($resource);
    }
}