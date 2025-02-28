<?php

namespace App\Http\Controllers\Api\Any;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Any\BlogResource;
use App\Models\Structure\Blog;
use App\Http\Resources\Api\BlogForListResource;
use Illuminate\Http\JsonResponse;

class BlogController extends ApiController
{
    /**
     * @param $slug
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/v1/public/blog/{slug}",
     *     summary="Get Blog by slug",
     *     @OA\Parameter(parameter="slug", description="slug blog", required=true, in="path", name="slug", example="main"),
     *     tags={"Blog"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     ),
     * )
     */
    public function find($slug): JsonResponse
    {
        $blog = Blog::where('slug', $slug)->first();
        
        return response()->json(
            BlogResource::make($blog)
        );
    }

    /**
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/v1/public/blog/list",
     *     summary="Get Blogs",
     *     tags={"Blog"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     ),
     * )
     */
    public function getList(): JsonResponse
    {
        $blogs = Blog::get();
        return response()->json(
            BlogForListResource::collection($blogs)
        );
    }
}
