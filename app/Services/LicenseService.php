<?php

namespace App\Services;

use Exception;
use App\Models\License;
use App\Traits\CanStore;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\LicensesRepository;
use Illuminate\Validation\ValidationException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\ResponseCache\Facades\ResponseCache;

class LicenseService extends AbstractModelService
{
    use CanStore;

    protected $modelClass = License::class;

    /**
     * @var LicensesRepository
     */
    private $licensesRepository;
    /**
     * @var OneTimeLinkService
     */
    private $oneTimeLinkService;

    public function __construct(
        ImagesService $imagesService,
        MetaService $metaService,
        TaggingService $taggingService,
        LicensesRepository $licensesRepository,
        OneTimeLinkService $oneTimeLinkService
    ) {
        parent::__construct($imagesService, $metaService, $taggingService);
        $this->licensesRepository = $licensesRepository;

        $this->setValidationRules();
        $this->oneTimeLinkService = $oneTimeLinkService;
    }

    private function setValidationRules()
    {
        $this->validationRules = [
            'payment_type' => [
                'required',
                Rule::in(['standard', 'recurrent']),
            ],
            'type' => 'required',
            'price' => 'required_if:payment_type,standard',
            'downloads_limit' => 'required_if:payment_type,standard',
            'plans.*.price' => 'required_if:payment_type,recurrent',
            'plans.*.plan' => 'required_if:payment_type,recurrent',
            'plans.*.total_price' => 'required_if:payment_type,recurrent',
            'plans.*.paddle_secret_key' => 'required_if:payment_type,recurrent',
            'plans.*.paddle_product_id' => 'required_if:payment_type,recurrent',
        ];
    }

    /**
     * @param int|null $licenseId
     *
     * @return License
     */
    public function findSFXLicense(?int $licenseId)
    {
        return License::findOrFail($licenseId);
    }

    /**
     * @param Model|License $model
     * @param $builtData
     *
     * @return License|Model
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|Exception
     */
    protected function fillInModel($model, $builtData)
    {
        [$data, $meta, $images] = $builtData;

        if (isset($data['payment_type']) && $model->payment_type && ($model->payment_type !== $data['payment_type'])) {
            throw new Exception("you can't change payment_type");
        }

        foreach ($this->excludedUpdateFields as $fieldName) {
            if (isset($data[$fieldName])) {
                unset($data[$fieldName]);
            }
        }

        // remove default from every other license
        if (isset($data['default']) && $data['default'] && !$model->default) {
            License::where('default', true)->update(['default' => false]);
        }

        $data['slug'] = $model->slug ?? Str::slug($data['type']);
        $model->fill($data);
        $model->save();

        $this->licenseUpload($model);

        $this->imagesService->upload($model, $images);


        Cache::forget('licenses:non-free');
        Cache::rememberForever('licenses:non-free', function () {
            return License::whereHas('standard', fn ($q) => $q->where('price', '!=', 0))
                ->where('payment_type', 'standard')->get();
        });

        ResponseCache::clear();

        return $model;
    }

    protected function licenseUpload($model)
    {
        if (!request()->hasFile('license')) {
            return;
        }

        $file = request()->file('license');

        $fileName = $file->getClientOriginalName();

        $storage = $this->getStorage();

        $path = "license";
        $licenseName = str_replace(' ', '-', $fileName);
        $licenseUrl = $storage->putFileAs($path, $file, $licenseName);

        $this->storeInCloud($path, $licenseName, $file);

        $link = '/storage/' . $licenseUrl;

        $model->url = $link;
        $model->save();

        Cache::forget('licenses:non-free');
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws ValidationException
     * @throws Exception
     */
    public function download(Request $request)
    {
        $this->validate($request, [
            'order_item_id' => 'required',
        ]);

        $orderItemId = $request->get('order_item_id');

        $orderItem = OrderItem::find($orderItemId);

        abort_if(!$orderItem->order, 404);
        abort_if($orderItem->order->user_id !== auth()->id(), 404);

        return [
            'success' => true,
            'url' => $this->oneTimeLinkService->generate('ol', [
                'di' => $request->get('order_item_id'),
            ]),
        ];
    }
}
