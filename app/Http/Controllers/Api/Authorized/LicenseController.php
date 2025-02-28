<?php

namespace App\Http\Controllers\Api\Authorized;

use App\Http\Controllers\Api\AuthorizedController;
use App\Http\Requests\GenerateLicenseRequest;
use App\Models\Paddle\BillingPrice;
use App\Models\License;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SFX\SFXPack;
use App\Models\UserDownloads;
use App\Services\PaddleApiService;
use App\Services\Orfium\OrfiumService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class LicenseController extends AuthorizedController
{
    public function __construct(
        private readonly PaddleApiService $paddleApiService,
        private readonly OrfiumService $orfiumService
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get(
     *     path="/v1/protected/licenses",
     *     summary="List of licenses for user",
     *     tags={"License"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function listLicense(): JsonResponse
    {
        $orderItems = OrderItem::where('item_type','!=', 'sfx_packs')->whereHas('order', function ($query) {
            $query->where('user_id', auth()->user()->id);
        })->with('order')->get()->map(function ($item) {
            $content = $item->orderItemable;
            $class = explode('\\', $item->item_type);
            $licenseLink = !is_null($item->license_url) ?
                url($item->license_url) : url('/v1/public/licenses/licenseNumber/'.$item->license_number);

            return [
                'content_id' => $item->item_id,
                'content_name' => $content?->name,
                'content_slug' => $content?->slug,
                'content_author_name' => $content?->authorName(),
                'type' => end($class),
                'license_number' => $item->license_number,
                'created_at' => $item->created_at,
                'licenseLink' => $licenseLink,
                'transaction_id' => $item->order?->transaction_id,
                'invoice_number' => $item->order?->invoice_number,
            ];
        });

        $downloads = UserDownloads::where('class', '!=', SFXPack::class)
            ->with('downloadable')->where('user_id', auth()->user()?->id)
            ->whereNotNull('license_number')
            ->get()->map(function ($item) {
                $content = $item->downloadable;
                $class = explode('\\', $item->item_type);
                $licenseLink = !is_null($item->license_url) ?
                    url($item->license_url) : url('/v1/public/licenses/licenseNumber/'.$item->license_number);

                return [
                    'content_id' => $item->track_id,
                    'content_name' => $content?->name,
                    'content_slug' => $content?->slug,
                    'content_author_name' => $content?->authorName(),
                    'type' => end($class),
                    'license_number' => $item->license_number,
                    'created_at' => $item->created_at,
                    'licenseLink' => $licenseLink,
                    'transaction_id' => null,
                    'invoice_number' => null,
                ];
            });

        return response()->json(
            collect($orderItems)->merge($downloads)->sortByDesc('created_at')->values()
        );
    }

    /**
     * @OA\Post(
     *     path="/v1/protected/licenses/generate",
     *     summary="Generate License",
     *     tags={"License"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *          @OA\Property(property="contentType", type="string", description="Content Type: 'music', 'templates', 'sfx'"),
     *          @OA\Property(property="contentId", type="string", description="Content Id"),
     *          @OA\Property(property="priceId", type="string", description="License Price Id (Paddle)"),
     *          @OA\Property(property="transactionId", type="integer", description="Transaction Id (Paddle)"),
     *          @OA\Property(property="subscriptionId", type="integer", description="Subscription Id (Paddle)"),
     *     ))),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     ),
     * )
     *
     * @param GenerateLicenseRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function generateLicense(GenerateLicenseRequest $request): JsonResponse
    {
        $isGenerateLicense = true;

        if (config('app.paddle_billing') && !is_null($request->getTransactionId()))
        {
            $resultApi = $this->paddleApiService->getTransaction($request->getTransactionId());
            $isGenerateLicense = $resultApi['status'] === 'completed';
        }

        if ($isGenerateLicense)
        {
            try {
                $price = BillingPrice::where('price_id', $request->getPriceId())
                    ->firstOrFail();
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 404);
            }

            $typeLicense = in_array($request->getContentTypeRequest(), ['music', 'sfx']) ?
                3 : 4;

            $license = License::where('type', $price->name)
                ->where('license_type_id', $typeLicense)
                ->first();

            $path = '/storage/licenses';
            if (!file_exists(base_path('/public_html'.$path)))
                mkdir(base_path('/public_html'.$path));

            $licenseNumber = 'TT'.$price->name.random_int(1000, 9999).'-'.Carbon::now()->timestamp;

            try {
                $content = $request->getQuery()->where('id', $request->getContentId())->firstOrFail();
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 404);
            }

            if ($request->has('subscriptionId') ||
                !Order::where('transaction_id', $request->getTransactionId())->exists())
            {
                $html = $license->sample;
                $html = str_replace('%Number_License%', $licenseNumber, $html);
                $html = str_replace('%Author_Name%', $content->authorName(), $html);
                $html = str_replace('%Product_Link%', env('APP_URL') . $content->url(), $html);
                $html = str_replace('%Product_Name%', $content->name, $html);
                $html = str_replace('%Date%', Carbon::now(), $html);

                Pdf::loadHTML($html)->setPaper('a4')
                    ->save(base_path('/public_html'.$path) .'/'. $licenseNumber . '.pdf');

                $order = Order::create([
                    'user_id' => auth()->user()->id,
                    'status' => 'finished',
                    'type' => 'fast',
                    'succeeded_at' => Carbon::now(),
                    'transaction_id' => $request->getTransactionId(),
                    'subscription_id' => $request->getSubscriptionId(),
                ]);

                $orfiumLicense = $this->orfiumService->createLicense(
                    user: auth()->user(),
                    channelId: null,
                    license_type: 'CREATORSINGLE',
                    quota: 1,
                    assetId: $licenseNumber
                );

                OrderItem::create([
                    'order_id' => $order->id,
                    'track_id' => $request->getContentId(),
                    'license_id' => $license->id,
                    'license_url' => $path .'/'. $licenseNumber . '.pdf',
                    'price' => is_null($request->getTransactionId()) ? 0 : $price->unit_price_amount,
                    'track_copy' => '',
                    'license_copy' => '',
                    'license_number' => $licenseNumber,
                    'item_type' => $request->getClass(),
					'type_licence' => $request->getTypeLicence(),
                    'license_code' => $orfiumLicense['code']
                ]);

                return response()->json([
                    'path' => $path .'/'. $licenseNumber . '.pdf'
                ]);
            } else {
                return response()->json([
                    'message' => 'This transaction has already been used to generate a license.'
                ], 500);
            }
        } else {
            return response()->json([
                'message' => 'The transaction is not yet in the "completed" status.'
            ], 500);
        }
    }
}
