<?php

namespace App\Http\Controllers\Api\Any;

use App\Http\Controllers\Api\ApiController;
use App\Models\SettingText;
use App\Models\SFX\SFXCategory;
use App\Models\SFX\SFXTag;
use App\Models\Structure\TemplateMeta;
use App\Models\Tags\Genre;
use App\Models\Tags\Instrument;
use App\Models\Tags\Mood;
use App\Http\Resources\Any\AbstractTagResource;
use App\Http\Resources\VideoEffectApplicationResource;
use App\Http\Resources\VideoEffectCategoryResource;
use App\Models\Tags\Tag;
use App\Models\Tags\Type;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Models\VideoEffects\VideoEffectTag;
use Illuminate\Http\JsonResponse;

/**
 * @group Texts
 *
 * Class TextsController
 * @package App\Http\Controllers\Api\Any
 */
class TextController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/v1/public/texts/downloads",
     *     summary="Get downloads texts",
     *     tags={"Texts"},
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *               @OA\Property(property="data", type="object",
     *                    @OA\Property(property="free_download", type="string", example="text"),
     *                    @OA\Property(property="unlimited_download", type="string", example="text"),
     *                )
     *          ),
     *      ),
     * )
     *
     */
    public function downloads(): JsonResponse
    {
        return response()->json([
            'data' => [
                'free_download' => SettingText::where('key', 'free_download_text')->first()->value ?? '',
                'unlimited_download' => SettingText::where('key', 'unlimited_download_text')->first()->value ?? ''
            ]
        ]);
    }
}