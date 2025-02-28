<?php

namespace App\Http\Controllers\Api\Authorized;

use App\Http\Controllers\Api\ApiController;
use App\Models\PayoutCoefficient;
use App\Models\Track;
use App\Models\User;
use App\Models\VideoEffects\VideoEffect;
use App\Services\DownloadService;
use App\Services\OrderItemService;
use Illuminate\Http\JsonResponse;
use App\Services\BalanceService;
use Carbon\Carbon;

class EarningsController extends ApiController
{
    public function __construct(
        private readonly DownloadService  $downloadService,
        private readonly OrderItemService $orderItemService,
        private readonly BalanceService $balanceService
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get(
     *     path="/v1/protected/earnings",
     *     summary="List earnings",
     *     tags={"Earnings"},
	 *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(property="items", type="array", @OA\Items(
     *                  @OA\Property(property="date", type="integer"),
     *                  @OA\Property(property="productName", type="string"),
     *                  @OA\Property(property="productType", type="string"),
     *                  @OA\Property(property="rate", type="integer"),
     *                  @OA\Property(property="discount", type="integer"),
     *                  @OA\Property(property="earnings", type="float", example="1"),
     *              )),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getEarningsTable(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = auth()->user();

        $user->load('subscriptionHistories');

        $authors = $user->authors()->get();

        $coefficients = PayoutCoefficient::pluck('value', 'name')->toArray();

        $result = [];

        $totalCurrentMonth = 0;

        foreach ($authors as $author)
        {
            $tracksIds = Track::where('author_profile_id', $author->id)->pluck('id');

            $videoEffectsIds = VideoEffect::where('author_profile_id', $author->id)->pluck('id');

            $detailedBalance = $this->balanceService->getEarnings($author->user_id);

            $userDownloads = $this->downloadService->getDownloadsByIds($tracksIds, $videoEffectsIds, $coefficients);
            $license = $this->orderItemService->getOrderItemsByIds($tracksIds, $videoEffectsIds, $coefficients);
            $content = $detailedBalance->merge($userDownloads)->merge($license)
                ->sortBy('date')->values();
            $totalCurrentMonth += $content->where('date', '>=', Carbon::now()->startOfMonth()->timestamp)
                ->where('date', '<=', Carbon::now()->timestamp)->sum('earnings');

            $result[] = [
                'author_id' => $author->id,
                'author_name' => $author->name,
                'content' => $content
            ];
        }

        return response()->json([
            'data' => $result,
            'totalCurrentMonth' => number_format($totalCurrentMonth, 2)
        ]);
    }
}
