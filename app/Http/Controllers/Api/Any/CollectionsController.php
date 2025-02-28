<?php

namespace App\Http\Controllers\Api\Any;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Any\Collection\TrackResource;
use App\Http\Resources\CollectionsResource;
use App\Models\Structure\Collection;
use App\Services\CollectionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CollectionsController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/v1/public/collections",
     *     summary="List of all collection",
     *     tags={"Collections"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                  ref="#/components/schemas/CollectionsResource"
     *              )),
     *              @OA\Property(property="links", type="object",
     *                  @OA\Property(property="first", type="string", example="https://apitaketones/v1/public/collections?page=1"),
     *                  @OA\Property(property="last", type="string", example="https://apitaketones/v1/public/collections?page=5"),
     *                  @OA\Property(property="prev", type="string", example="https://apitaketones/v1/public/collections?page=2"),
     *                  @OA\Property(property="next", type="string", example="https://apitaketones/v1/public/collections?page=4"),
     *              ),
     *              @OA\Property(property="meta", type="object",
     *                  @OA\Property(property="current_page", type="integer", example="3"),
     *                  @OA\Property(property="from", type="integer", example="3"),
     *                  @OA\Property(property="last_page", type="integer", example="3"),
     *                  @OA\Property(property="links", type="array", @OA\Items(
     *                      @OA\Property(property="url", type="string", example="https://apitaketones/v1/public/collections?page=1"),
     *                      @OA\Property(property="label", type="string", example="1"),
     *                      @OA\Property(property="active", type="boolean", example="true"),
     *                  )),
     *                  @OA\Property(property="path", type="string", example="https://apitaketones/v1/public/collections"),
     *                  @OA\Property(property="per_page", type="integer", example="10"),
     *                  @OA\Property(property="to", type="integer", example="10"),
     *                  @OA\Property(property="total", type="integer", example="20"),
     *              ),
     *         ),
     *     ),
     * )
     *
     * @return AnonymousResourceCollection
     */
    public function get(): AnonymousResourceCollection
    {
        $service = resolve(CollectionsService::class);

        return $service->collectionsGet();
    }

    /**
     * @OA\Get(
     *     path="/v1/public/collections/{collection}",
     *     summary="Find Name Collection",
     *     tags={"Collections"},
     *     @OA\Parameter(parameter="collection", description="ID collection", required=true, in="path", name="collection", example="4"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="collection", type="object", ref="#/components/schemas/CollectionsResource"),
     *                  @OA\Property(property="tracks", type="object",
     *                       @OA\Property(property="id", type="integer", example="820"),
     *                       @OA\Property(property="slug", type="string", example="mirage"),
     *                       @OA\Property(property="name", type="string", example="Mirage"),
     *                       @OA\Property(property="author_name", type="string", example="EdRecords"),
     *                       @OA\Property(property="description", type="string", example="Chill hiphop for YouTube content"),
     *                       @OA\Property(property="tempo", type="string", example="91"),
     *                       @OA\Property(property="duration", type="string", example="12.01"),
     *                       @OA\Property(property="downloads", type="integer", example="123"),
     *                       @OA\Property(property="price", type="float", example="10.01"),
     *                       @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *                       @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *                       @OA\Property(property="sales_count", type="integer", example="123"),
     *                       @OA\Property(property="is_free", type="boolean", example="false"),
     *                       @OA\Property(property="has_content_id", type="boolean", example="false"),
     *                       @OA\Property(property="full_name", type="string", example="Santa is Coming by StudioKolomna"),
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     *
     * @param Collection $collection
     * @return JsonResponse
     */
    public function find(Collection $collection): JsonResponse
    {
        $resource = new CollectionsResource($collection);

        return $this->success([
            'collection' => $resource,
            'tracks' => TrackResource::collection($collection->tracks->where('has_content_id', false))
        ]);
    }

    /**
     * @OA\Get(
     *     path="/v1/public/collections/by-name/{collectionName}",
     *     summary="Find By Name Collection",
     *     tags={"Collections"},
     *     @OA\Parameter(parameter="collectionName", description="Name Collections", required=true, in="path", name="collectionName", example="Sweet Wedding"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="collection", type="object", ref="#/components/schemas/CollectionsResource"),
     *                  @OA\Property(property="tracks", type="object",
     *                       @OA\Property(property="id", type="integer", example="820"),
     *                       @OA\Property(property="slug", type="string", example="mirage"),
     *                       @OA\Property(property="name", type="string", example="Mirage"),
     *                       @OA\Property(property="author_name", type="string", example="EdRecords"),
     *                       @OA\Property(property="description", type="string", example="Chill hiphop for YouTube content"),
     *                       @OA\Property(property="tempo", type="string", example="91"),
     *                       @OA\Property(property="duration", type="string", example="12.01"),
     *                       @OA\Property(property="downloads", type="integer", example="123"),
     *                       @OA\Property(property="price", type="float", example="10.01"),
     *                       @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *                       @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *                       @OA\Property(property="sales_count", type="integer", example="123"),
     *                       @OA\Property(property="is_free", type="boolean", example="false"),
     *                       @OA\Property(property="has_content_id", type="boolean", example="false"),
     *                       @OA\Property(property="full_name", type="string", example="Santa is Coming by StudioKolomna"),
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     *
     * @param $collectionName
     * @return JsonResponse|array
     */
    public function findByName($collectionName): JsonResponse|array
    {
        $collections = Collection::all();

        $collectionObject = $collections->where('slug', $collectionName)->first();

        if (!$collectionObject) {
            return [];
        }

        $collection = Collection::where('id', $collectionObject->id)->first();

        if (!$collection) {
            return [];
        }
        
        return $this->find($collection);
    }

    /**
     * @OA\Get(
     *     path="/v1/public/collections/for-main",
     *     summary="List of all collection for main",
     *     tags={"Collections"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                  ref="#/components/schemas/CollectionsResource"
     *              )),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getForMain(): JsonResponse
    {
        $res = Collection::where('hidden', false)->latest()->limit(5)->get();

        $collections = CollectionsResource::collection($res);

        return $this->success($collections);
    }
}
