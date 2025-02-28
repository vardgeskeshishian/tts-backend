<?php

namespace App\Services;

use App\Http\Resources\Api\FavoritesFolderContentResource;
use App\Http\Resources\Api\FavoritesFolderContentResourceCollection;
use App\Http\Resources\Api\FavoritesFolderResource;
use App\Models\SFX\SFXTrack;
use App\Models\Track;
use App\Models\UserFavoritesFolder;
use App\Models\VideoEffects\VideoEffect;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FavoritesFolderService
{
    private array $item_info;

    public function __construct()
    {
        $this->item_info = [
            'App\Models\Track' => [
                'db' => 'tracks_folders',
                'id_name' => 'track_id',
                'model' => new Track(),
                'relations' => ['author', 'archive', 'audio', 'genres', 'moods', 'instruments', 'types', 'tags'],
                'type' => 'track',
            ],
            'App\Models\VideoEffects\VideoEffect' => [
                'db' => 'video_effects_folders',
                'id_name' => 'video_effect_id',
                'model' => new VideoEffect(),
                'relations' => ['author', 'application', 'categories', 'resolutions', 'version', 'tags'],
                'type' => 'videoEffect',
            ],
            'App\Models\SFX\SFXTrack' => [
                'db' => 'sfx_tracks_folders',
                'id_name' => 'sfx_track_id',
                'model' => new SFXTrack(),
                'relations' => [],
                'type' => 'sfxTrack',
            ],
        ];
    }

    public function getFolderList($user): array
    {
        return [
            'music' => FavoritesFolderResource::collection($user->favoriteFolders()->where('folder_type', 'App\Models\Track')->get()),
            'templates' => FavoritesFolderResource::collection($user->favoriteFolders()->where('folder_type', 'App\Models\VideoEffects\VideoEffect')->get()),
            'sfx' => FavoritesFolderResource::collection($user->favoriteFolders()->where('folder_type', 'App\Models\SFX\SFXTrack')->get()),
        ];
    }

    public function getContents(UserFavoritesFolder $folder): FavoritesFolderContentResourceCollection
    {
        $info = $this->item_info[$folder->folder_type];
        $identifiers = DB::table($info['db'])->where('user_favorites_folder_id', $folder->id)->pluck($info['id_name']) ?? [];
        $items = $info['model']->whereIn('id', $identifiers)->with($info['relations'])->get();
        return FavoritesFolderContentResource::collection($items)->type($info['type']);
    }

    public function updateOrCreate(Request $request)
    {
        $data = $request->validate([
            'id' => 'exists:user_favorites_folders,id',
            'title' => 'nullable|string',
            'folder_type' => 'required_without:id|in:music,template,sfx',
            'user_id' => 'required|exists:users,id'
        ]);

        if (isset($data['id'])) {
            $folder = UserFavoritesFolder::updateOrCreate(
                ['id' => $data['id']],
                ['title' => $data['title'] ?? '']
            );
        } else {
            $folder_types = [
                'music' => 'App\Models\Track',
                'template' => 'App\Models\VideoEffects\VideoEffect',
                'sfx' => 'App\Models\SFX\SFXTrack',
            ];
            $data['folder_type'] = $folder_types[$data['folder_type']];
            $folder = UserFavoritesFolder::query()->create($data);
        }
        return UserFavoritesFolder::query()->findOrFail($folder->id);
    }

    public function addContent(UserFavoritesFolder $folder, $content_id)
    {
        $info = $this->item_info[$folder->folder_type];
        $content = $info['model']->where('id', $content_id)->first();
        if ($content) {
            DB::table($info['db'])->upsert(
				[
					'user_favorites_folder_id' => $folder->id,
				 	$info['id_name'] => $content->id
				],
				[
					'user_favorites_folder_id',
					$info['id_name']
				]);
        } else {
            throw new \Exception('Invalid content identifier', 400);
        }
        return FavoritesFolderContentResource::make($content)->type($info['type']);
    }

    public function removeContent(UserFavoritesFolder $folder, $content_id)
    {
        $info = $this->item_info[$folder->folder_type];
        $content = $info['model']->where('id', $content_id)->first();
        $attachment = DB::table($info['db'])->where('user_favorites_folder_id', $folder->id)
            ->where($info['id_name'], $content_id);
        if (!$attachment->first()) {
            throw new \Exception('This content is not in the folder', 404);
        }
        $attachment->delete();
        return FavoritesFolderContentResource::make($content)->type($info['type']);
    }
}
