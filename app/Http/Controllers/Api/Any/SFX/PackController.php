<?php


namespace App\Http\Controllers\Api\Any\SFX;

use Exception;
use App\Models\User;
use App\Constants\Env;
use App\Models\License;
use App\Models\SFX\SFXPack;
use App\Models\UserDownloads;
use App\Services\OrderService;
use App\Services\LicenseService;
use App\Http\Requests\ApiRequest;
use App\Services\AnalyticsService;
use App\Services\OneTimeLinkService;
use App\Services\SFX\Pack\PackService;
use App\Services\LicenseNumberService;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Any\SFX\PackResource;
use App\Http\Requests\SFX\Pack\BuyPackRequest;
use App\Http\Requests\SFX\Pack\GetPacksRequest;
use App\Http\Requests\SFX\Pack\AddToCartRequest;
use App\Http\Requests\SFX\Pack\RenamePackRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PackController extends ApiController
{
    /**
     * @var PackService
     */
    private PackService $service;
    /**
     * @var AnalyticsService
     */
    private AnalyticsService $analyticsService;

    public function __construct(PackService $service, AnalyticsService $analyticsService)
    {
        $this->service = $service;
        $this->analyticsService = $analyticsService;
    }

    public function getUnfinishedPack(GetPacksRequest $request): PackResource
    {
        return new PackResource($this->service->getUnfinishedPack($request->user()));
    }

    public function renamePack(RenamePackRequest $request): PackResource|JsonResponse
    {
        try {
            $pack = $this->service->renamePack(
                $request->user(),
                $request->input('packId'),
                $request->input('name')
            );

            return new PackResource($pack);
        } catch (Exception $exception) {
            return $this->errorWrapped($exception);
        }
    }

    public function getPacks(): AnonymousResourceCollection
    {
        return PackResource::collection(SFXPack::whereHas('params', function ($query) {
            $query->where('personal', false)->where('published', true);
        })->orderByDesc('created_at')->get());
    }

    public function buy(
        BuyPackRequest $request,
        LicenseService $licenseService,
        OrderService $orderService
    ): JsonResponse
    {
        try {
            $pack = $this->service->newPack(
                $request->user(),
                $request->input('ids'),
                $request->input('name')
            )->getPack();

            $license = $licenseService->findSFXLicense($request->input('licenseId'));

            $paymentLink = $orderService->fastForSFX($pack, $license);

            return $this->success([
                'pack' => new PackResource($pack),
                'paymentLink' => $paymentLink,
            ]);
        } catch (Exception $e) {
            return $this->errorWrapped($e);
        }
    }

    public function buyStock(
        ApiRequest $request,
        LicenseService $licenseService,
        OrderService $orderService
    ): JsonResponse
    {
        $pack = SFXPack::find($request->input('pack_id'));

        $license = $licenseService->findSFXLicense($request->input('licenseId'));

        $paymentLink = $orderService->fastForSFX($pack, $license);

        return $this->success([
            'pack' => new PackResource($pack),
            'paymentLink' => $paymentLink,
        ]);
    }

    public function addToCart(
        AddToCartRequest $request,
        LicenseService $licenseService,
        OrderService $orderService
    ): JsonResponse
    {
        try {
            $pack = $this->service->newPack(
                $request->user(),
                $request->input('ids'),
                $request->input('name')
            )->getPack();

            $license = $licenseService->findSFXLicense($request->input('licenseId'));

            $order = $orderService->findOrCreateFullOrder();
            $cart = $orderService->addSFXOrderItem($order, $pack, $license);

            return $this->success([
                'pack' => new PackResource($pack),
                'cart' => $cart,
            ]);
        } catch (Exception $e) {
            return $this->errorWrapped($e);
        }
    }

    public function addToCartStock(
        ApiRequest $request,
        LicenseService $licenseService,
        OrderService $orderService
    ): JsonResponse
    {
        try {
            $pack = SFXPack::find($request->input('pack_id'));

            $license = $licenseService->findSFXLicense($request->input('licenseId'));

            $order = $orderService->findOrCreateFullOrder();
            $cart = $orderService->addSFXOrderItem($order, $pack, $license);

            return $this->success([
                'pack' => new PackResource($pack),
                'cart' => $cart,
            ]);
        } catch (Exception $e) {
            return $this->errorWrapped($e);
        }
    }

    public function customPackSubDownload(
        ApiRequest $request,
        LicenseNumberService $numberService,
        OneTimeLinkService $linkService
    ): JsonResponse
    {
        $pack = $this->service->newPack(
            $request->user(),
            $request->input('ids'),
            $request->input('name')
        )->getPack();

        return $this->subDownload($pack, $numberService, $linkService);
    }

    public function subDownload(
        SFXPack $pack,
        LicenseNumberService $numberService,
        OneTimeLinkService $linkService
    ): JsonResponse
    {
        abort_if(!request()->has('license_id'), 404, "license not found");

        $license = License::find(request('license_id'));

        abort_if($license->payment_type !== 'recurrent', 404, "wrong license type");

        $download = UserDownloads::create([
            'user_id' => auth()->user()->id,
            'track_id' => $pack->id,
            'type' => Env::ITEM_TYPE_PACKS,
            'license_number' => $numberService->generate($license),
            'license_id' => $license->id,
            'class' => SFXPack::class,
        ]);

        /**
         * @var $user User
         */
        $user = auth()->user();

        $user->increment('downloads');

        if ($license->payment_type === 'recurrent') {
            $this->analyticsService->sendSubDownload($pack->name . ' (SFX Pack)');
        }

        $zipUrl = $linkService->generateDownloadsZip($pack->id, $download);
        $licUrl = $linkService->generateForUserDownloadLicense($download);

        return $this->success([
            'success' => true,
            'license' => $licUrl,
            'zip' => $zipUrl,
        ]);
    }
}
