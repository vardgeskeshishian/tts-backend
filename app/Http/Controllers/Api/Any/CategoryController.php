<?php

namespace App\Http\Controllers\Api\Any;

use App\Http\Controllers\Api\ApiController;
use App\Models\SFX\SFXCategory;
use App\Models\SFX\SFXTag;
use App\Models\Structure\TemplateMeta;
use App\Models\Tags\CuratorPick;
use App\Models\Tags\Genre;
use App\Models\Tags\Instrument;
use App\Models\Tags\Mood;
use App\Http\Resources\Any\AbstractTagResource;
use App\Http\Resources\VideoEffectApplicationResource;
use App\Http\Resources\VideoEffectCategoryResource;
use App\Models\Tags\SortCategory;
use App\Models\Tags\Tag;
use App\Models\Tags\Type;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Models\VideoEffects\VideoEffectTag;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends ApiController
{
    public function __construct(
        private CategoryService $categoryService
    )
    {}

    /**
     * @OA\Get(
     *     path="/v1/public/categories/genres",
     *     summary="List Genre",
     *     tags={"Category"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/AbstractTagResource"
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getGenres(): JsonResponse
    {
        $template = TemplateMeta::where('type', Genre::class)->first();
        return response()->json(
            Genre::with('icon')->get()->map(fn($item) => new AbstractTagResource($item, $template))
        );
    }

    /**
     * @OA\Get(
     *     path="/v1/public/categories/moods",
     *     summary="List Mood",
     *     tags={"Category"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/AbstractTagResource"
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getMoods(): JsonResponse
    {
        $template = TemplateMeta::where('type', Mood::class)->first();
        return response()->json(
            Mood::with('icon')->get()->map(fn($item) => new AbstractTagResource($item, $template))
        );
    }

    /**
     * @OA\Get(
     *     path="/v1/public/categories/instruments",
     *     summary="List Instrument",
     *     tags={"Category"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/AbstractTagResource"
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getInstruments(): JsonResponse
    {
        $template = TemplateMeta::where('type', Instrument::class)->first();
        return response()->json(
            Instrument::with('icon')->get()->map(fn($item) => new AbstractTagResource($item, $template))
        );
    }

    /**
     * @OA\Get(
     *     path="/v1/public/categories/usage-types",
     *     summary="List Usage Type",
     *     tags={"Category"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/AbstractTagResource"
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getUsageTypes(): JsonResponse
    {
        $template = TemplateMeta::where('type', Type::class)->first();
        return response()->json(
            Type::with('icon')->get()->map(fn($item) => new AbstractTagResource($item, $template))
        );
    }

    /**
     * @OA\Get(
     *     path="/v1/public/categories/track-tags",
     *     summary="List Track Tag",
     *     tags={"Category"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/AbstractTagResource"
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getTags(): JsonResponse
    {
        $template = TemplateMeta::where('type', Tag::class)->first();
        return response()->json(
            Tag::with('icon')->get()->map(fn($item) => new AbstractTagResource($item, $template))
        );
    }

    /**
     * @OA\Get(
     *     path="/v1/public/categories/application",
     *     summary="List VideoEffect Application",
     *     tags={"Category"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/VideoEffectApplicationResource"
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getVideoEffectApplications(): JsonResponse
    {
        $template = TemplateMeta::where('type', VideoEffectApplication::class)->first();
        return response()->json(
            VideoEffectApplication::with('icon')->get()->map(fn($item) => new VideoEffectApplicationResource($item, $template))
        );
    }

    /**
     * @OA\Get(
     *     path="/v1/public/categories/video-categories",
     *     summary="List VideoEffect Categories",
     *     tags={"Category"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/VideoEffectCategoryResource"
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getVideoEffectCategories(): JsonResponse
    {
        $template = TemplateMeta::where('type', VideoEffectCategory::class)->first();
        return response()->json(
            VideoEffectCategory::with('icon')->get()->map(fn($item) => new VideoEffectCategoryResource($item, $template))
        );
    }

    /**
     * @OA\Get(
     *     path="/v1/public/categories/video-tags",
     *     summary="List Video Tag",
     *     tags={"Category"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/AbstractTagResource"
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getVideoEffectTags(): JsonResponse
    {
        $template = TemplateMeta::where('type', VideoEffectTag::class)->first();
        return response()->json(
            VideoEffectTag::with('icon')->get()->map(fn($item) => new AbstractTagResource($item, $template))
        );
    }

    /**
     * @OA\Get(
     *     path="/v1/public/categories/sfx-categories",
     *     summary="List Sfx Category",
     *     tags={"Category"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/AbstractTagResource"
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getSFXCategory(): JsonResponse
    {
        $template = TemplateMeta::where('type', SFXCategory::class)->first();
        return response()->json(
            SFXCategory::with('icon')->get()->map(fn($item) => new AbstractTagResource($item, $template))
        );
    }

    /**
     * @OA\Get(
     *     path="/v1/public/categories/sfx-tags",
     *     summary="List Sfx Tag",
     *     tags={"Category"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/AbstractTagResource"
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getSFXTag(): JsonResponse
    {
        $template = TemplateMeta::where('type', SFXTag::class)->first();
        return response()->json(
            SFXTag::with('icon')->get()->map(fn($item) => new AbstractTagResource($item, $template))
        );
    }

    /**
     * @OA\Get(
     *     path="/v1/public/categories/all",
     *     summary="List All Category",
     *     tags={"Category"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="track", type="object",
     *                  @OA\Property(property="genres", type="array",
     *                      @OA\Items(ref="#/components/schemas/AbstractTagResource")
     *                  ),
     *                  @OA\Property(property="moods", type="array",
     *                      @OA\Items(ref="#/components/schemas/AbstractTagResource")
     *                  ),
     *                  @OA\Property(property="instruments", type="array",
     *                      @OA\Items(ref="#/components/schemas/AbstractTagResource")
     *                  ),
     *                  @OA\Property(property="usage-types", type="array",
     *                      @OA\Items(ref="#/components/schemas/AbstractTagResource")
     *                  ),
     *              ),
     *              @OA\Property(property="video", type="object",
     *                  @OA\Property(property="applications", type="array",
     *                      @OA\Items(ref="#/components/schemas/AbstractTagResource")
     *                  ),
     *                  @OA\Property(property="categories", type="array",
     *                      @OA\Items(ref="#/components/schemas/AbstractTagResource")
     *                  ),
     *              ),
     *              @OA\Property(property="sfx", type="object",
     *                  @OA\Property(property="categories", type="array",
     *                      @OA\Items(ref="#/components/schemas/AbstractTagResource")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getAllCategories(): JsonResponse
    {
        $sortCategories = SortCategory::get();
        $return = [];

        foreach ($sortCategories as $sortCategory)
        {
            $categories = $this->categoryService->query($sortCategory->class)
                ->with('icon')->get()
                ->map(fn($item) => $this->categoryService->getResource($sortCategory->class, $item))
                ->sortBy(function ($item) {
                    return $item->priority ?? 'z' . $item->id;
                });

            $categoriesArr = [];
            foreach ($categories as $category)
                $categoriesArr[] = $category;

            if (count($categories) > 0)
                $return[$this->categoryService->getTypeCategory($sortCategory->type)][$this->categoryService->getSlugCategory($sortCategory->class)] =
                    [
                        'is_hidden' => $sortCategory->is_hidden,
                        'data' => $categoriesArr
                    ];
        }

        return response()->json($return);
    }
}