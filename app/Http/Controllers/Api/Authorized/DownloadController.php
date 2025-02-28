<?php

namespace App\Http\Controllers\Api\Authorized;

use App\Enums\TypeContentEnum;
use App\Filters\DownloadFilter;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\DownloadResource;
use App\Jobs\UpdateTrackCoefficientJobs;
use App\Jobs\UpdateVideoCoefficientJobs;
use App\Jobs\UpdateSfxCoefficientJobs;
use App\Models\Paddle\BillingProduct;
use App\Models\License;
use App\Models\Setting;
use App\Models\SFX\SFXPack;
use App\Models\SFX\SFXTrack;
use App\Models\Track;
use App\Models\UserDownloads;
use App\Models\VideoEffects\VideoEffect;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DownloadController extends ApiController
{
    const MATCH_JOBS = [
        Track::class => UpdateTrackCoefficientJobs::class,
        VideoEffect::class => UpdateVideoCoefficientJobs::class,
        SFXTrack::class => UpdateSfxCoefficientJobs::class
    ];

    /**
     * @OA\Get(
     *     path="/v1/protected/downloads",
     *     summary="List Downloads",
     *     tags={"Downloads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(parameter="type", description="Type Content: music, templates, sfx", required=false, in="query", name="type"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/DownloadResource"),
     *         ),
     *     ),
     * )
     *
     * @param DownloadFilter $filter
     * @return JsonResponse
     */
    public function getList(DownloadFilter $filter): JsonResponse
    {
        return response()->json(
            UserDownloads::filter($filter)
                ->where('class', '!=', SFXPack::class)
                ->with('downloadable')->where('user_id', auth()->user()?->id)
                ->get()->map(fn($item) => new DownloadResource($item))
        );
    }

    /**
     * @OA\Post(
     *     path="/v1/protected/downloads/{typeContent}/{id}",
     *     summary="Download Content",
     *     tags={"Downloads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(parameter="typeContent", description="Type Content", in="path", name="typeContent"),
     *     @OA\Parameter(parameter="id", description="ID Content", in="path", name="id"),
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="product_id", type="string", description="Product ID (Paddle)"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              ref="/components/schemas/TrackApiResource"
     *         ),
     *     ),
     * )
     *
     * @param TypeContentEnum $typeContent
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function downloadContent(TypeContentEnum $typeContent, string $id, Request $request): JsonResponse
    {
        try {
            $product = BillingProduct::where('product_id', $request->input('product_id'))
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }

        $class = $typeContent->getClass();

        $license = License::where('type', $product->name)
            ->where('license_type_id', $typeContent->typeIdContent())
            ->first();

        $subscriptions = auth()->user()->getActiveSubscriptions();
        if (!$subscriptions['business'] && !$subscriptions['creator']) {
            $setting = Setting::where('key', 'free_downloads')->first();
            $limit = $setting->value ?? 0;
            if (auth()->user()->downloads >= $limit) {
                return response()->json(['message' => 'The limit has been reached', 'code' => '400'], 400);
            }
            auth()->user()->downloads++;
            auth()->user()->save();
        } else {
            $content = $typeContent->getQuery()->where('id', $id)->first();
            $path = '/storage/licenses';
            if (!file_exists(base_path('/public_html'.$path)))
                mkdir(base_path('/public_html'.$path));
            $licenseNumber = 'TT'.$product->name.random_int(1000, 9999).'-'.Carbon::now()->timestamp;

            $html = $license->sample;
            $html = str_replace('%Number_License%', $licenseNumber, $html);
            $html = str_replace('%Author_Name%', $content->authorName(), $html);
            $html = str_replace('%Product_Link%', env('APP_URL') . $content->url(), $html);
            $html = str_replace('%Product_Name%', $content->name, $html);
            $html = str_replace('%Date%', Carbon::now(), $html);

            Pdf::loadHTML($html)->setPaper('a4')
                ->save(base_path('/public_html'.$path) .'/'. $licenseNumber . '.pdf');
        }

        $userDownload = UserDownloads::create([
            'user_id' => auth()->user()->id,
            'track_id' => $id,
            'type' => $product->name,
            'license_id' => $license?->id,
            'billing_product_id' => $product->id,
            'class' => $class,
            'license_number' => isset($licenseNumber) ? $licenseNumber : null,
            'license_url' => isset($licenseNumber) ? $path .'/'. $licenseNumber . '.pdf' : null,
        ]);

        self::MATCH_JOBS[$class]::dispatch($id);

        return response()->json(
            new DownloadResource($userDownload->load('downloadable'))
        );
    }
}