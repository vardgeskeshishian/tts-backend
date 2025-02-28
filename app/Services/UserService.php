<?php

namespace App\Services;

use App\Constants\Env;
use App\Constants\UserFieldsConstants;
use App\Exceptions\PasswordException;
use App\Http\Resources\Api\UserResource;
use App\Models\OrderItem;
use App\Models\SFX\SFXPack;
use App\Models\SFX\SFXTrack;
use App\Models\User;
use App\Models\UserDownloads;
use App\Models\VideoEffects\VideoEffect;
use App\Services\MailerLite\MailerLiteService;
use Exception;
use Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rule;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class UserService extends AbstractModelService
{
    protected $modelClass = User::class;
    private MailerLiteService $mailerLiteService;

    public function __construct(
        ImagesService     $imagesService,
        MetaService       $metaService,
        TaggingService    $taggingService,
        MailerLiteService $mailerLiteService
    )
    {
        parent::__construct($imagesService, $metaService, $taggingService);

        $this->setValidationRules();
        $this->mailerLiteService = $mailerLiteService;
    }

    /**
     */
    public function setValidationRules(): void
    {
        $userId = auth()->id();

        $this->validationRules = [
            'email' => [
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'old_password' => 'required_with_all:password,password_confirmed',
            'password' => 'sometimes|min:6',
            'password_confirmed' => 'required_with_all:old_password,password|same:password',
        ];
    }

    /**
     * @param Request $request
     * @param Model $model
     *
     * @return Model|array
     * @throws Exception
     */
    public function update(Request $request, Model $model)
    {
        $this->validate($request, $this->validationRules);

        $data = $this->buildDataFromRequest($request);

        return $this->fillInModel($model, $data);
    }

    /**
     * @param User $model
     * @param $builtData
     *
     * @return User|mixed
     * @throws PasswordException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function fillInModel($model, $builtData)
    {
        [$data] = $builtData;

        foreach ($this->excludedUpdateFields as $fieldName) {
            if (isset($data[$fieldName])) {
                unset($data[$fieldName]);
            }
        }

        $data['user_type'] = $data['activity_type'] ?? null;
        unset($data['activity_type'], $data[UserFieldsConstants::NEWSLETTER_AGREEMENT]);

        if (!isset($data['user_type'])) {
            unset($data['user_type']);
        }

        if (isset($data['password']) && $data['password'] !== "") {
            $old = $data['old_password'];
            $new = $data['password'];

            if (!Hash::check($old, $model->getAuthPassword())) {
                throw new PasswordException("original password is wrong", 400);
            }

            $data['password'] = bcrypt($new);
        }

        if (!empty($data)) {
            $model->fill($data);
            $model->save();
        }

        $this->mailerLiteService->setUser($model);

        $subscriptionStatus = !$model ? [] : $this->mailerLiteService->getSubscriptionStatus();

        $agreement = (int)request()->get(UserFieldsConstants::NEWSLETTER_AGREEMENT);

        $newStatus = null;

        if ($agreement) {
            if (!$subscriptionStatus['subscribed']) {
                $this->mailerLiteService->addSubscriber();

                $subscriptionStatus['subscribed'] = true;
                $subscriptionStatus['status'] = 'active';
            } elseif ($subscriptionStatus['status'] === $this->mailerLiteService::UNSUBSCRIBED_TYPE) {
                $subscriptionStatus['status'] = $newStatus = $this->mailerLiteService::SUBSCRIBED_TYPE;
            }
        } elseif ($subscriptionStatus['subscribed'] || $subscriptionStatus['status'] === 'active') {
            $subscriptionStatus['status'] = $newStatus = $this->mailerLiteService::UNSUBSCRIBED_TYPE;
        }

        if (!is_null($newStatus)) {
            $this->mailerLiteService->setActiveStatus($newStatus);
        }
        $this->mailerLiteService->updateUser();

        return new UserResource([
            'user' => $model,
            'mailer' => $subscriptionStatus,
        ]);
    }

    /**
     *
     * @OA\Schema(
     *     schema="OrdersList",
     *     title="OrdersList",
     *     @OA\Property(property="data", type="array", @OA\Items(
     *          @OA\Property(property="id", type="string", example="140391"),
     *          @OA\Property(property="order_id", type="string", example="1202"),
     *          @OA\Property(property="track_id", type="string", example="345"),
     *          @OA\Property(property="license_id", type="string", example="454"),
     *          @OA\Property(property="date", type="string", example="1602418511"),
     *          @OA\Property(property="license_sculpt", type="object",
     *              @OA\Property(property="type", type="string", example="Free"),
     *          ),
     *          @OA\Property(property="track_sculpt", type="object",
     *              @OA\Property(property="name", type="string", example="Joy"),
     *              @OA\Property(property="author_name", type="string", example="Paul Keane"),
     *              @OA\Property(property="images", type="object",
     *                  @OA\Property(property="thumbnail", type="string", example="https://static.taketones.com/f/images/498397e19c6d7fb25ce5d52314e86499.png"),
     *                  @OA\Property(property="background", type="string", example="https://static.taketones.com/storage/images/c9f0f895fb98ab9159f51fd0297e236d/e215466af2f4e31b5718d88c521ff3cb.jpeg"),
     *              ),
     *              @OA\Property(property="prices", type="array", @OA\Items(
     *                  @OA\Property(property="type", type="string", example="Standard"),
     *                  @OA\Property(property="license_id", type="string", example="1"),
     *                  @OA\Property(property="license", type="object",
     *                      @OA\Property(property="type", type="string", example="Standard"),
     *                      @OA\Property(property="short_description", type="string", example="Standard"),
     *                      @OA\Property(property="description", type="string", example="Standard"),
     *                      @OA\Property(property="list_1", type="string", example="Paid Ads, Education, Audiobooks"),
     *                      @OA\Property(property="list_2", type="string", example="no credits, commercial use, short versions"),
     *                      @OA\Property(property="comments", type="Including the uses covered by the previous licenses"),
     *                  ),
     *                  @OA\Property(property="price", type="string", example="12.00"),
     *              )),
     *          ),
     *          @OA\Property(property="effect_sculpt", type="object",
     *              @OA\Property(property="id", type="string", example="140391"),
     *              @OA\Property(property="name", type="string", example="Accept"),
     *              @OA\Property(property="extension", type="string", example="wav"),
     *              @OA\Property(property="price", type="string", example="14.22"),
     *              @OA\Property(property="duration", type="string", example="4,95"),
     *              @OA\Property(property="link", type="string", example="/sfx/audio/accelerating-spinning-whoosh.wav"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *              @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *              @OA\Property(property="images", type="object",
     *                  @OA\Property(property="thumbnail", type="string", example="https://static.taketones.com/f/images/498397e19c6d7fb25ce5d52314e86499.png"),
     *                  @OA\Property(property="background", type="string", example="https://static.taketones.com/storage/images/c9f0f895fb98ab9159f51fd0297e236d/e215466af2f4e31b5718d88c521ff3cb.jpeg"),
     *               ),
     *           ),
     *           @OA\Property(property="pack_sculpt", type="object",
     *              @OA\Property(property="id", type="string", example="140391"),
     *              @OA\Property(property="name", type="string", example="Accept"),
     *              @OA\Property(property="description", type="string", example="wav"),
     *              @OA\Property(property="price", type="string", example="14.22"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-27T06:20:59.000000Z"),
     *              @OA\Property(property="updated_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *              @OA\Property(property="deleted_at", type="string", example="2024-03-11T10:37:16.000000Z"),
     *              @OA\Property(property="images", type="object",
     *                  @OA\Property(property="thumbnail", type="string", example="https://static.taketones.com/f/images/498397e19c6d7fb25ce5d52314e86499.png"),
     *                  @OA\Property(property="background", type="string", example="https://static.taketones.com/storage/images/c9f0f895fb98ab9159f51fd0297e236d/e215466af2f4e31b5718d88c521ff3cb.jpeg"),
     *              ),
     *           ),
     *           @OA\Property(property="type", type="string", example="order"),
     *           @OA\Property(property="license_number", type="string", example="TTStandard85653898"),
     *           @OA\Property(property="receipt", type="string", example="http://my.paddle.com/receipt/58672618/254897648-chrefd133c8c889-2fbaf6d183"),
     *           @OA\Property(property="item_type", type="string", example="tracks"),
     *           @OA\Property(property="item_id", type="string", example="54"),
     *     )),
     *     @OA\Property(property="links", type="array", @OA\Items(
     *           @OA\Property(property="url", type="string", example="https://apitaketones/v1/private/users?page=1"),
     *           @OA\Property(property="label", type="string", example="1"),
     *           @OA\Property(property="active", type="string", example="true"),
     *     )),
     *     @OA\Property(property="meta", type="object",
     *          @OA\Property(property="current_page", type="string", example="1"),
     *          @OA\Property(property="last_page", type="string", example="1"),
     *     ),
     * )
     *
     * @return array
     */
    public function ordersList()
    {
        $userId = auth()->id();

        $orderItems = OrderItem::whereHas('order', function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->where('status', 'finished');
        })->orderByDesc('order_id')->get()->lazy();

        $downloads = UserDownloads::where('user_id', $userId)
            ->where('type', '!=', 'preview-download')
            ->orderByDesc('id')
            ->get();

        $itemCollection = new Collection();
        $orderItems->each(function (OrderItem $orderItem) use (&$itemCollection) {
            $licenseNumber = $orderItem->license_number;

            $sfxPackSculpt = null;
            $sfxTrackSculpt = null;
            $trackSculpt = null;

            $licenseImages = $orderItem->license->getImages();
            $image = $licenseImages['thumbnail'] ?? $licenseImages['icon'] ?? null;

            switch ($orderItem->item_type) {
                case Env::ITEM_TYPE_TRACKS:
                    $trackSculpt = $orderItem->track_sculpt;
                    break;
                case Env::ITEM_TYPE_PACKS:
                    $sfxPackSculpt = $orderItem->pack();
                    $sfxPackSculpt = array_merge($sfxPackSculpt, ['images' => [
                        'thumbnail' => $image,
                    ]]);
                    break;
                case Env::ITEM_TYPE_EFFECTS:
                    $sfxTrackSculpt = $orderItem->effect();
                    $sfxTrackSculpt = array_merge($sfxTrackSculpt, ['images' => [
                        'thumbnail' => $image,
                    ]]);
                    break;
                case Env::ITEM_TYPE_VIDEO_EFFECTS:
                    $trackSculpt = $orderItem->videoEffect();
                    break;
            }

            $itemCollection->push([
                'id' => $orderItem->id,
                'order_id' => $orderItem->order_id,
                'track_id' => $orderItem->track_id,
                'license_id' => $orderItem->license_id,
                'date' => $orderItem->updated_at,
                'license_sculpt' => [
                    'type' => $orderItem->license->type,
                ],
                'track_sculpt' => $trackSculpt,
                'effect_sculpt' => $sfxTrackSculpt,
                'pack_sculpt' => $sfxPackSculpt,
                'type' => 'order',
                'license_number' => $licenseNumber,
                'receipt' => $orderItem->order->receipt_url,
                'item_type' => $orderItem->item_type,
                'item_id' => $orderItem->getItemId(),
            ]);
        });

        $downloads->each(function (UserDownloads $userDownload) use (&$itemCollection) {
            $type = $userDownload->license_id ? 'subscription' : 'free';

            $itemType = match ($userDownload->type) {
                Env::ITEM_TYPE_PACKS, Env::ITEM_TYPE_EFFECTS => 'sfx',
                Env::ITEM_TYPE_VIDEO_EFFECTS => 'vfx',
                default => 'track'
            };

            $isSfx = in_array($userDownload->type, [Env::ITEM_TYPE_PACKS, Env::ITEM_TYPE_EFFECTS]);

            if (!$userDownload->track && !$isSfx) {
                return;
            }

            $pack = null;
            $effect = null;
            $vfx = null;

            $notTrack = match ($userDownload->type) {
                Env::ITEM_TYPE_VIDEO_EFFECTS, Env::ITEM_TYPE_EFFECTS, Env::ITEM_TYPE_PACKS => true,
                default => false
            };

            switch ($userDownload->type) {
                case Env::ITEM_TYPE_PACKS:
                    $pack = SFXPack::find($userDownload->track_id);
                    break;
                case Env::ITEM_TYPE_EFFECTS:
                    $effect = SFXTrack::find($userDownload->track_id);
                    break;
                case Env::ITEM_TYPE_VIDEO_EFFECTS:
                    $vfx = VideoEffect::find($userDownload->track_id);
                    break;
                default:
                    if (!$userDownload->track) {
                        return;
                    }
                    break;
            }

            $images = $notTrack ? $userDownload->license->getImages() : [];
            $image = $images['thumbnail'] ?? $images['icon'] ?? null;

            $itemCollection->push([
                'id' => $userDownload->id,
                'order_id' => $userDownload->id,
                'track_id' => $userDownload->track_id,
                'license_id' => $userDownload->license_id,
                'date' => $userDownload->updated_at,
                'license_sculpt' => $userDownload->license_sculpt,
                'track_sculpt' => $notTrack && $vfx ? array_merge($vfx->toArray(), [
                    'author_name' => $vfx->author_name,
                    'images' => [
                        'thumbnail' => $image,
                    ]]) : $userDownload->track,
                'pack_sculpt' => $pack ? array_merge($pack->toArray(), ['images' => [
                    'thumbnail' => $image,
                ]]) : null,
                'effect_sculpt' => $effect ? array_merge($effect->toArray(), ['images' => [
                    'thumbnail' => $image,
                ]]) : null,
                'type' => $type,
                'license_number' => $userDownload->license_number,
                'receipt' => null,
                'item_type' => $notTrack ? $userDownload->type : Env::ITEM_TYPE_TRACKS,
                'item_id' => $userDownload->track_id,
            ]);
        });


        return $this->paginate($itemCollection->sortByDesc('date'));
    }



    protected function paginate($items)
    {
        $page = request('page') ?: 1;
        $perPage = request('perpage', 15);
        $total = $items->count();
        $options = [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
            'query' => request()->all(),
        ];

        /**
         * @var $tracks Builder|Collection
         * @var $forPage Builder|Collection
         */
        $forPage = $items->forPage($page, $perPage);

        $paginator = new LengthAwarePaginator(
            $forPage,
            $total,
            $perPage,
            $page,
            $options
        );

        return [
            'data' => array_values($paginator->items()),
            'links' => $paginator->links(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }
}
