<?php

namespace App\Services;

use App\Constants\VideoEffects;
use App\DTO\VFX\VideoEffectExcelDTO;
use App\Helpers\VFX\VideoEffectExcelHelper;
use App\Http\Resources\VideoEffectProtectedResource;
use App\Models\Authors\AuthorProfile;
use App\Models\Libs\Role;
use App\Models\User;
use App\Models\VideoEffects\VideoEffect;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Models\VideoEffects\VideoEffectPlugin;
use App\Models\VideoEffects\VideoEffectResolution;
use App\Models\VideoEffects\VideoEffectTag;
use App\Models\VideoEffects\VideoEffectVersion;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VideoEffectsService
{
    protected $fileFields = ['preview_photo', 'preview_video', 'zip'];
    /**
     * @var FilesService
     */
    private FilesService $filesService;
    private CacheService $cacheService;
    private UserRoleService $roleService;
    private VideoEffectExcelHelper $videoEffectExcelHelper;

    /**
     * VideoEffectsService constructor.
     *
     * @param CacheService $cacheService
     * @param FilesService $filesService
     * @param UserRoleService $roleService
     * @param VideoEffectExcelHelper $videoEffectExcelHelper
     */
    public function __construct(
        CacheService           $cacheService,
        FilesService           $filesService,
        UserRoleService        $roleService,
        VideoEffectExcelHelper $videoEffectExcelHelper
    )
    {
        $this->filesService = $filesService;
        $this->filesService->setCloudNamespace("video-effects");
        $this->cacheService = $cacheService;
        $this->roleService = $roleService;
        $this->videoEffectExcelHelper = $videoEffectExcelHelper;
    }

    /**
     * @return VideoEffect
     * @throws Exception
     */
    public function createFromExcelFile($row)
    {
        $dto = $this->videoEffectExcelHelper->setData($row)->parseExcelRowData();

        [$effectName, $effectNameId] = [$dto->effectName, $dto->effectNameId];

        // 668
        if ($dto->effectNameId === '09096' || $dto->effectNameId === '15016') {
            return null;
        }

        // ids_data
        $application = $this->cacheService->getModelByName(VideoEffectApplication::class, $row['applications']);
        $version = $this->cacheService->getModelByName(VideoEffectVersion::class, $row['version']);
        $categories = $this->cacheService
            ->getModelsByKey(VideoEffectCategory::class, 'slug', explode(",", $row['categories']));
        $plugins = $this->cacheService->getModelsByName(VideoEffectPlugin::class, explode(",", $row['plugins']));
        $resolutions = $this->cacheService
            ->getModelsByKey(VideoEffectResolution::class, "full", explode(",", $row['resolution']));
        $tags = trim($row['tags']);

        // prices
        $standardPrice = $row['price1_9_29'];
        $extendedPrice = (int)$row['price2_59_99'];

        $description = $row['description'];

        /**
         * @var $user User
         * @var $profile AuthorProfile
         */
        $user = User::firstOrCreate(
            ['email' => $row['email']],
            ['name' => $row['author_name']]
        );

        $this->roleService->assignRoleToUser($user, Role::ROLE_AUTHOR);

        $author = $user->getAuthor();

        $profile = $author
            ->profiles()
            ->firstOrCreate([
                'name' => $row['author_name'],
            ]);

        $videoEffect = new VideoEffect;

        $videoEffect->fill([
            'name' => $dto->effectName,
            'slug' => sprintf("%s-%s", Str::slug($effectName), $effectNameId),
            'description' => $description,
            'is_system' => true,
            'user_id' => $user->id,
            'author_profile_id' => $profile->id,
            'associated_music' => $row['music_url'] ?? '',
        ]);
        $videoEffect->status = VideoEffects::STATUS_PUBLISHED;
        $videoEffect->save();

        $this->saveProperties($videoEffect, [
            'applications' => $application->id ?? null,
            'categories' => $categories->pluck('id')->all(),
            'plugins' => $plugins->pluck('id')->all(),
            'version' => $version->id ?? null,
            'resolutions' => $resolutions->pluck('id')->all(),
            'standard_price' => $standardPrice,
            'extended_price' => $extendedPrice,
        ]);

        $tags = $this->prepareTagsFromString($tags);

        if (count($tags) > 0) {
            $videoEffect->tags()->createMany($tags);
        }

        $this->uploadFilesDirectly($videoEffect, $dto);

        return $videoEffect;
    }

    protected function saveProperties(VideoEffect $videoEffect, $data = [])
    {
        if (isset($data['applications'])) {
            $videoEffect->application_id = $data['applications'];
        }
        if (isset($data['plugins'])) {
            $videoEffect->plugin_id = $data['plugins'];
            $videoEffect->plugin_ids = $this->makeArray($data, 'plugins');
        }
        if (isset($data['resolutions'])) {
            $videoEffect->resolution_id = $data['resolutions'];
            $videoEffect->resolution_ids = $this->makeArray($data, 'resolutions');
        }
        if (isset($data['categories'])) {
            $videoEffect->category_id = $data['categories'];
            $videoEffect->category_ids = $this->makeArray($data, 'categories');
        }
        if (isset($data['version'])) {
            $videoEffect->version_id = $data['version'];
        }
        if (isset($data['standard_price'])) {
            $videoEffect->price_standard = $data['standard_price'];
        }
        if (isset($data['extended_price'])) {
            $videoEffect->price_extended = $data['extended_price'];
        }

        $videoEffect->save();
    }

    protected function makeArray($origArray, $key): array
    {
        return is_array($origArray[$key]) ? $origArray[$key] : [$origArray[$key]];
    }

    public function prepareTagsFromString(string $data)
    {
        if (strlen($data) === 0) {
            return [];
        }

        return $this->prepareTags(explode(',', $data));
    }

    private function prepareTags(array $unparsedTags)
    {
        $tags = [];
        $slugs = [];
        foreach ($unparsedTags as $item) {
            $item = trim($item);
            $slug = Str::slug($item);
            $tags[] = [
                'name' => $item,
                'slug' => $slug,
            ];
            $slugs[] = $slug;
        }

        VideoEffectTag::query()->insertOrIgnore($tags);

        return VideoEffectTag::whereIn('slug', $slugs)
            ->select(['id', 'name'])
            ->get()
            ->map(fn($item) => [
                'tag_id' => $item->id,
                'name' => $item->name,
            ])
            ->toArray();
    }

    public function uploadFilesDirectly(VideoEffect $videoEffect, VideoEffectExcelDTO $dto)
    {
        $uploaded = [
            'preview_video' => $dto->previewVideo,
            'preview_photo' => $dto->previewImage,
            'zip' => $dto->zip,
        ];

        $only = ['preview_video', 'preview_photo', 'zip'];
//        $only = ['preview_photo'];

        $files = [];

        foreach ($only as $key) {
            $files[$key] = $uploaded[$key];
        }

        /**
         * @var $file UploadedFile
         */
        foreach ($files as $key => $file) {
            if (!$file) {
                continue;
            }

            $this->filesService->setCloudNamespace("video-effects");

            $this->filesService->setConfig("$key/$videoEffect->id", $file);

            switch ($key) {
                case 'preview_photo':
                    $files[$key] = $this->filesService->setExtension("jpg")->cloudUpload();
                    $this->filesService->setCloudNamespace("video-effects");
                    $files["thumbnail_$key"] = $this->filesService->setExtension('webp')->uploadThumbnail();
                    break;
                case 'zip':
                    $files[$key] = $dto->zip;
                    break;
                default:
                    $files[$key] = $this->filesService->setExtension("mp4")->cloudUpload();
            }
        }

        $videoEffect->fill($files);
        $videoEffect->save();

        return $this->returnResource($videoEffect);
    }

    protected function returnResource(VideoEffect $videoEffect): VideoEffectProtectedResource
    {
        return new VideoEffectProtectedResource($videoEffect->refresh());
    }

    public function updateVideoEffectFields(Collection $row, $fields = [])
    {
        $dto = $this->videoEffectExcelHelper->setData($row)->parseExcelRowData();
        [$effectName, $effectNameId] = [$dto->effectName, $dto->effectNameId];

        $videoEffect = VideoEffect::where([
            'slug' => sprintf("%s-%s", Str::slug($effectName), $effectNameId),
        ])->first();

        $mappedFields = [];

        foreach ($fields as $modelKey => $rowKey) {
            $separatedRowValue = explode(',', $row[$rowKey]);

            $mappedFields[$modelKey] = match ($rowKey) {
                'categories' => $this->cacheService->getModelsByKey(
                    VideoEffectCategory::class,
                    'slug',
                    $separatedRowValue
                )->pluck('id')->all(),
                'resolution' => $this->cacheService->getModelsByKey(
                    VideoEffectResolution::class,
                    'full',
                    $separatedRowValue
                )->pluck('id')->all(),
                default => $row[$rowKey] ?? null,
            };
        }

        if (!$videoEffect) {
            return null;
        }

        $videoEffect->fill($mappedFields);
        $videoEffect->save();

        return $videoEffect;
    }

    public function prepareTagsFromArray(array $tags)
    {
        return $this->prepareTags($tags);
    }

    public function updateFilesFromExcel(Collection $row)
    {
        $dto = $this->videoEffectExcelHelper->setData($row)->parseExcelRowData();
        [$effectName, $effectNameId] = [$dto->effectName, $dto->effectNameId];

        $videoEffect = VideoEffect::where([
            'slug' => sprintf("%s-%s", Str::slug($effectName), $effectNameId),
        ])->first();

        $this->uploadFilesDirectly($videoEffect, $dto);

        return $videoEffect;
    }

    public function update(VideoEffect $videoEffect)
    {
        $data = $this->buildDataFromRequest(request());
        $newStatus = $this->getNewStatus($videoEffect);

        switch ($newStatus) {
            case VideoEffects::STATUS_PUBLISHED:
                $data = collect($data)->only(['standard_price', 'extended_price']);
                break;
            case VideoEffects::STATUS_HARD_REJECT:
                abort(400);
        }

        $this->saveProperties($videoEffect, $data);

        if (isset($data['tags'])) {
            $videoEffect->tags()->delete();
            $videoEffect->tags()->createMany($this->prepareTagsFromString($data['tags'] ?? ''));
        }

        $videoEffect->status = $newStatus;

        $videoEffect->fill($data);
        $videoEffect->save();

        return $this->returnResource($videoEffect);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function buildDataFromRequest(Request $request)
    {
        $data = $request->except($this->fileFields);

        if (isset($data['author'])) {
            $data['author_profile_id'] = $data['author'];
        }

        if (isset($data['author_name'])) {
            $author = AuthorProfile::where('name', $data['author_name'])->first();

            if ($author) {
                $data['author_profile_id'] = $author->id;
            }
        }

        if (isset($data['standard_price'])) {
            $price = $data['standard_price'];

            if ($price < 9) {
                $data['standard_price'] = 9;
            }
            if ($price > 29) {
                $data['standard_price'] = 29;
            }
        }

        if (isset($data['extended_price'])) {
            $price = $data['extended_price'];

            if ($price < 59) {
                $data['extended_price'] = 59;
            }
            if ($price > 99) {
                $data['extended_price'] = 99;
            }
        }

        return $data;
    }

    private function getNewStatus(VideoEffect $videoEffect)
    {
        $origStatus = $videoEffect->getRawOriginal('status');
        return match ($origStatus) {
            VideoEffects::STATUS_SOFT_REJECT, VideoEffects::STATUS_APPROVED => VideoEffects::STATUS_RESUBMITTED,
            default => $origStatus,
        };
    }

    public function getForAuthor()
    {
        return VideoEffectProtectedResource::collection(VideoEffect::where('user_id', auth()->user()->id)->get());
    }

    public function uploadFiles(VideoEffect $videoEffect): VideoEffectProtectedResource
    {
        $files = [
            'preview_video' => request()->files->get('preview_video'),
            'preview_photo' => request()->files->get('preview_photo'),
            'zip' => request()->files->get('zip'),
        ];

        $status = $videoEffect->getRawOriginal('status');

        switch ($status) {
            case VideoEffects::STATUS_PUBLISHED:
            case VideoEffects::STATUS_HARD_REJECT:
                abort(400, "can't change hard_reject");
        }

        $this->filesService->setCloudNamespace("video-effects");

        /**
         * @var $file UploadedFile
         */
        foreach ($files as $key => $file) {
            if (!$file) {
                continue;
            }

            $this->filesService->setConfig("$key/$videoEffect->id", $file);

            switch ($key) {
                case 'preview_photo':
                    $files[$key] = $this->filesService->setExtension("jpg")->cloudUpload();
                    $files["thumbnail_$key"] = $this->filesService->setExtension('webp')->uploadThumbnail();
                    break;
                case 'preview_video':
                    // just to set an extension and fallthrough
                    $this->filesService->setExtension("mp4");
                // no break
                default:
                    $files[$key] = $this->filesService->cloudUpload();
            }
        }

        $newStatus = VideoEffects::STATUS_NEW;
        switch ($status) {
            case VideoEffects::STATUS_SOFT_REJECT:
            case VideoEffects::STATUS_RESUBMITTED:
            case VideoEffects::STATUS_APPROVED:
                $newStatus = VideoEffects::STATUS_RESUBMITTED;
                break;
        }

        $videoEffect->status = $newStatus;
        $videoEffect->fill($files);
        $videoEffect->save();

        return $this->returnResource($videoEffect);
    }

    public function addUserComment(VideoEffect $videoEffect): VideoEffectProtectedResource
    {
        $videoEffect->comments()->create([
            'user_id' => auth()->id(),
            'user_comment' => request('user_comment'),
        ]);

        return $this->returnResource($videoEffect);
    }

    /**
     * @return VideoEffectProtectedResource
     * @throws Exception
     */
    public function create()
    {
        $data = $this->buildDataFromRequest(request());

        $videoEffect = new VideoEffect;

        $videoEffect->user_id = auth()->user()->id;
        $videoEffect->fill($data);
        $videoEffect->status = VideoEffects::STATUS_NEW;
        $videoEffect->save();
        $videoEffect->slug = sprintf("%s-%s", Str::slug($videoEffect->slug), $videoEffect->id);

        $this->saveProperties($videoEffect, $data);
        $tags = $this->prepareTagsFromString($data['tags'] ?? '');
        if (count($tags) > 0) {
            $videoEffect->tags()->createMany($tags);
        }

        return $this->returnResource($videoEffect);
    }

    public function addComment(VideoEffect $videoEffect, int $reviewer, bool $hidden = false, string $comment = '')
    {
        return match ($hidden) {
            true => $this->addHiddenComment($videoEffect, $reviewer, $comment),
            default => $this->addReviewerComment($videoEffect, $reviewer, $comment),
        };
    }

    public function addHiddenComment(VideoEffect $videoEffect, int $reviewer, string $comment = '')
    {
        $videoEffect->comments()->create([
            'user_id' => $reviewer,
            'reviewer_comment' => request('comment', $comment),
            'hidden' => true,
        ]);

        return $videoEffect;
    }

    public function addReviewerComment(VideoEffect $videoEffect, int $reviewer, string $comment = '')
    {
        $videoEffect->comments()->create([
            'user_id' => $reviewer,
            'reviewer_comment' => request('comment', $comment)
        ]);

        return $videoEffect;
    }

    /**
     * @param int $authorId
     * @param float $totalAllSubscription
     * @param array $coefficients
     * @return array
     */
    public function getVideoEffectsForPortfolio(int $authorId, array $coefficients, $downloadOneVideoEffect): array
    {
        return VideoEffect::withCount(['downloads' => function ($query) {
            $query->whereIn('license_id', [12, 13]);
        }])->where('author_profile_id', $authorId)
            ->withSum(['orderItem' => function ($query) {
                $query->whereDate('created_at', '>=', Carbon::now()->startOfMonth());
            }], 'price')
            ->withSum('detailedBalances', 'award')
            ->withCount(['downloads as current_month_downloads' => function ($query) {
                $query->whereIn('license_id', [12, 13])
                    ->whereDate('created_at', '>=', Carbon::now()->startOfMonth());
            }])
            ->withCount('orderItem')->get()
            ->map(function ($videoEffect) use ($coefficients, $downloadOneVideoEffect) {
                $totalLicense = $videoEffect->order_item_sum_price *
                    ($videoEffect->exclusive ? $coefficients['wex'] : $coefficients['wnoex']);

                $currentMonthDownloads = $videoEffect->current_month_downloads * $downloadOneVideoEffect
                    * ($videoEffect->exclusive ? $coefficients['wex'] : $coefficients['wnoex']);

                $sum = $videoEffect->detailed_balances_sum_award + $totalLicense + $currentMonthDownloads;
                return [
                    'id' => $videoEffect->id,
                    'name' => $videoEffect->name,
                    'slug' => $videoEffect->slug,
                    'category' => 'Video Effect',
                    'sales' => $videoEffect->order_item_count,
                    'downloads' => $videoEffect->downloads_count,
                    'rate' => $videoEffect->exclusive ? $coefficients['wex'] * 10 : $coefficients['wnoex'] * 10,
                    'total' => (float)number_format($sum, 2, '.', '')
                ];
            })->toArray();
    }
}
