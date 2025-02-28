<?php

namespace App\Http\Controllers\Api\Authorized;

use App\Http\Controllers\Api\AuthorizedController;
use App\Http\Resources\Api\FavoritesFolderResource;
use App\Models\UserFavoritesFolder;
use App\Services\FavoritesFolderService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\ResponseCache\Facades\ResponseCache;
use App\Enums\TypeContentEnum;

/**
 * @group Favorites Folders
 *
 * Class FavoritesFolderController
 * @package App\Http\Controllers\Api\Authorized
 */
class FavoritesFolderController extends AuthorizedController
{

    private FavoritesFolderService $folderService;

    public function __construct(
        FavoritesFolderService $folderService,
    )
    {
        parent::__construct();
        $this->folderService = $folderService;
    }

    /**
     * List of all favorites folders
     *
     * @OA\Get(
     *     path="/v1/protected/favorites-folders",
     *     summary="List of all favorites folders",
     *     tags={"Favorites Folders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *                   @OA\Property(property="folders", type="object",
     *                        @OA\Property(property="music", type="array", @OA\Items(ref="#/components/schemas/FavoritesFolder")),
     *                        @OA\Property(property="template", type="array", @OA\Items(ref="#/components/schemas/FavoritesFolder")),
     *                        @OA\Property(property="sfx", type="array", @OA\Items(ref="#/components/schemas/FavoritesFolder")),
     *                   ),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function get()
    {
        $user = auth()->user();
        return $this->success([
            'success' => true,
            'folders' => $this->folderService->getFolderList($user),
        ]);
    }

    /**
     * List of content in folder
     *
     * @OA\Get(
     *     path="/v1/protected/favorites-folders/{folder_id}",
     *     summary="List of content in folder",
     *     tags={"Favorites Folders"},
     *     @OA\Parameter(parameter="folder_id", description="Folder identifier", required=true, in="path", name="folder_id"),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *                   @OA\Property(property="folder", type="object", ref="#/components/schemas/FavoritesFolder"),
     *                   @OA\Property(property="contents", type="array", @OA\Items(
     *                       @OA\Property(property="id", type="integer", example="123"),
     *                   )),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function getContents(UserFavoritesFolder $folder)
    {
        $user = auth()->user();
        if ($user->id != $folder->user_id) {
            return response('Authorized user is not the owner of the folder', 403);
        }
        return $this->success([
            'success' => true,
            'folder' => FavoritesFolderResource::make($folder),
            'contents' => $this->folderService->getContents($folder),
        ]);
    }

    /**
     * Create favorites folder
     *
     * @OA\Post(
     *     path="/v1/protected/favorites-folders",
     *     summary="Create favorites folder",
     *     tags={"Favorites Folders"},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *           @OA\Property(property="title", type="string", description="Folder title"),
     *           @OA\Property(property="folder_type", type="string", enum={"music", "template", "sfx"}, description="Folder type"),
     *      ))),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *                   @OA\Property(property="folder", type="object", ref="#/components/schemas/FavoritesFolder"),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function create(Request $request)
    {
        $request->merge(['user_id' => auth()->user()->id]);
        try {
            $folder = $this->folderService->updateOrCreate($request);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(), 'code' => $e->getCode()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }
        return $this->success([
            'success' => true,
            'folder' => FavoritesFolderResource::make($folder),
        ]);
    }

    /**
     * Update favorites folder
     *
     * @OA\Put(
     *     path="/v1/protected/favorites-folders/{folder_id}",
     *     summary="Update favorites folder",
     *     tags={"Favorites Folders"},
     *     @OA\Parameter(parameter="folder_id", description="Folder identifier", required=true, in="path", name="folder_id"),
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *           @OA\Property(property="title", type="string", description="Folder title"),
     *      ))),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *                   @OA\Property(property="folder", type="object", ref="#/components/schemas/FavoritesFolder"),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function update(UserFavoritesFolder $folder, Request $request)
    {
        $request->merge(['id' => $folder->id, 'user_id' => auth()->user()->id]);
        try {
            $folder = $this->folderService->updateOrCreate($request);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(), 'code' => $e->getCode()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }
        return $this->success([
            'success' => true,
            'folder' => FavoritesFolderResource::make($folder),
        ]);
    }

    /**
     * Delete favorites folder
     *
     * @OA\Delete(
     *     path="/v1/protected/favorites-folders/{folder_id}",
     *     summary="Delete favorites folder",
     *     tags={"Favorites Folders"},
     *     @OA\Parameter(parameter="folder_id", description="Folder identifier", required=true, in="path", name="folder_id"),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function delete(UserFavoritesFolder $folder)
    {
        try {
            $contentIdsFolderType = TypeContentEnum::getTypeContent($folder->folder_type)->getQuery();
            $folder->delete();
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(), 'code' => $e->getCode()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }

        $contentIds = $contentIdsFolderType->has('folders')->pluck('id')->unique();

        return $this->success([
            'success' => true,
            'ids' => $contentIds
        ]);
    }

    /**
     * Add content to favorites folder
     *
     * @OA\Post(
     *     path="/v1/protected/favorites-folders/{folder_id}/add/{content_id}",
     *     summary="Add content to favorites folder",
     *     tags={"Favorites Folders"},
     *     @OA\Parameter(parameter="folder_id", description="Folder identifier", required=true, in="path", name="folder_id"),
     *     @OA\Parameter(parameter="content_id", description="Content identifier", required=true, in="path", name="content_id"),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function addContent(UserFavoritesFolder $folder, string $item): Application|Response|JsonResponse|\Illuminate\Contracts\Foundation\Application|ResponseFactory
    {
        try {
            $content = $this->folderService->addContent($folder, $item);
			ResponseCache::clear();
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(), 'code' => $e->getCode()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }

        $contentIds = TypeContentEnum::getTypeContent($folder->folder_type)->getQuery()
            ->has('folders')->pluck('id')->unique();

        return $this->success([
            'success' => true,
            'content' => $content,
            'ids' => $contentIds
        ]);
    }

    /**
     * Remove content from favorites folder
     *
     * @OA\Post(
     *     path="/v1/protected/favorites-folders/{folder_id}/remove/{content_id}",
     *     summary="Remove content from favorites folder",
     *     tags={"Favorites Folders"},
     *     @OA\Parameter(parameter="folder_id", description="Folder identifier", required=true, in="path", name="folder_id"),
     *     @OA\Parameter(parameter="content_id", description="Content identifier", required=true, in="path", name="content_id"),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *               )
     *         ),
     *     ),
     * )
     *
     */
    public function removeContent(UserFavoritesFolder $folder, string $item): Application|Response|JsonResponse|\Illuminate\Contracts\Foundation\Application|ResponseFactory
    {
        try {
            $content = $this->folderService->removeContent($folder, $item);
			ResponseCache::clear();
		} catch (\Exception $e) {
            return response(['message' => $e->getMessage(), 'code' => $e->getCode()], $e->getCode() >= 200 && $e->getCode() <= 500 ? $e->getCode() : 400);
        }

        $contentIds = TypeContentEnum::getTypeContent($folder->folder_type)->getQuery()
            ->has('folders')->pluck('id')->unique();

        return $this->success([
            'success' => true,
            'content' => $content,
            'ids' => $contentIds
        ]);
    }

    /**
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/v1/protected/favorites-folders/list-content",
     *     summary="List of all favorites folders",
     *     tags={"Favorites Folders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     ),
     * )
     */
    public function getContentIds(): JsonResponse
    {
        return response()->json([
            'tracks' => TypeContentEnum::TRACK->getQuery()->has('folders')->pluck('id')->unique(),
            'templates' => TypeContentEnum::VIDEO_EFFECT->getQuery()->has('folders')->pluck('id')->unique(),
            'sfxs' => TypeContentEnum::SFX->getQuery()->has('folders')->pluck('id')->unique(),
        ]);
    }

    /**
     * @param string $typeContent
     * @param int $idContent
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/v1/protected/favorites-folders/by-content/{typeContent}/{idContent}",
     *     summary="Remove content from favorites folder",
     *     tags={"Favorites Folders"},
     *     @OA\Parameter(parameter="typeContent", description="Content type", required=true, in="path", name="typeContent"),
     *     @OA\Parameter(parameter="idContent", description="Content id", required=true, in="path", name="idContent"),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="success", type="boolean", example="true"),
     *               )
     *         ),
     *     ),
     * )
     */
    public function getFolders(string $typeContent, int $idContent): JsonResponse
    {
        $typeContentEnum = TypeContentEnum::getTypeContent($typeContent);
        $class = $typeContentEnum->getClass();
        $folders = UserFavoritesFolder::where('user_id', auth()->user()->id)
            ->where('folder_type', $class)
            ->whereHas($typeContentEnum->getNameTypeFolder(), function ($query) use ($idContent) {
                $query->where('id', $idContent);
            })->get();

        return response()->json([
            'folders' => FavoritesFolderResource::collection($folders),
        ]);
    }
}
