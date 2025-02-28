<?php


namespace App\Http\Controllers\Api\Any;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Any\FAQ\FAQSectionResource;
use App\Models\Structure\FAQCategory;
use App\Models\Structure\FAQSection;
use App\Http\Resources\Any\FAQ\FAQCategoryResource;
use Illuminate\Http\JsonResponse;

class FaqsController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/v1/public/faq",
     *     summary="List FAQ",
     *     tags={"FAQ"},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="categories", type="array",
     *                  @OA\Items(ref="#/components/schemas/FAQCategoryResource")
     *              ),
     *              @OA\Property(property="popular_sections", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="integer"),
     *                      @OA\Property(property="title", type="string"),
     *                      @OA\Property(property="url", type="string"),
     *                  )
     *              ),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function get(): JsonResponse
    {
        return response()->json([
            'categories' => FAQCategory::wherehas('sections', function ($query) {
                $query->has('faqs');
            })->with('sections', function ($query) {
                $query->has('faqs');
            })->get()->map(fn($category) => new FAQCategoryResource($category)),
            'popular_sections' => FAQSection::has('faqs')->where('is_popular', 1)->get()
                ->map(fn($section) => [
                    'id' => $section->id,
                    'title' => $section->title,
                    'url' => $section->url,
                ])
        ]);
    }

    /**
     * @OA\Get(
     *     path="/v1/public/faq/section/{url}",
     *     summary="Get FAQ Section by URL",
     *     tags={"FAQ"},
     *     @OA\Parameter(parameter="url", description="URL FAQ Section", required=true, in="path", name="url"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/FAQSectionResource"
     *         ),
     *     ),
     * )
     *
     * @param string $url
     * @return JsonResponse
     */
    public function getSectionByUrl(string $url): JsonResponse
    {
        $section = FAQSection::where('url', $url)->first();
        return response()->json(
            new FAQSectionResource($section)
        );
    }
}
