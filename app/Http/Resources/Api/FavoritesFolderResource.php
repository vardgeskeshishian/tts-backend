<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="FavoritesFolder",
 *     title="FavoritesFolder",
 *     @OA\Property(property="id", type="integer", example="123"),
 *     @OA\Property(property="title", type="string", example="Folder name"),
 *     @OA\Property(property="folder_type", type="string", enum={"music", "template", "sfx"}, example="music"),
 *     @OA\Property(property="user_id", type="integer", example="123"),
 *     @OA\Property(property="can_deleted", type="boolean", example="true"),
 *     @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
 *     @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
 * )
 */
class FavoritesFolderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'folder_type' => $this->getTypeSlug($this->folder_type),
            'user_id' => $this->user_id,
            'can_deleted' => (bool)$this->can_deleted,
            'count_item' => $this->countItem(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getTypeSlug(string $type): string
    {
        $types = [
            'App\Models\Track' => 'music',
            'App\Models\VideoEffects\VideoEffect' => 'templates',
            'App\Models\SFX\SFXTrack' => 'sfx'
        ];
        return $types[$type];
    }
}
