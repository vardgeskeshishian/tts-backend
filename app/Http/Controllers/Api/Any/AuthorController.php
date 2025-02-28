<?php


namespace App\Http\Controllers\Api\Any;

use App\Models\Authors\AuthorProfile;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\AuthorResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class AuthorController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/v1/public/authors",
     *     summary="List Authors",
     *     tags={"Authors"},
     *     @OA\Response(
     *         response="200",
     *         description="Success"
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function get(): JsonResponse
    {
        return response()->json(AuthorProfile::select(['slug', 'name'])->get());
    }

    /**
     * @OA\Get(
     *     path="/v1/public/authors/{slug}",
     *     summary="Get By Slug Authors",
     *     tags={"Authors"},
     *     @OA\Parameter(parameter="slug", description="Slug Author", required=true, in="path", name="slug"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/AnyAuthorResource"
     *         ),
     *     ),
     * )
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function getBySlug(string $slug): JsonResponse
    {
        try {
            $author = AuthorProfile::with('user')->where('slug', $slug)->firstOrFail();

            return response()->json(
                new AuthorResource($author)
            );
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
