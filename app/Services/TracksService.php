<?php

namespace App\Services;

use App\Events\AttachTagEvent;
use App\Events\CreateArchiveForTracksEvent;
use App\Jobs\Mixify;
use App\Jobs\RunAudioWaveformGeneratorJobs;
use App\Models\Images;
use App\Models\Orchid\Attachment;
use App\Models\Tags\Genre;
use App\Models\Tags\Instrument;
use App\Models\Tags\Mood;
use App\Models\Tags\Tag;
use App\Models\Tags\Type;
use App\Models\TrackAudio;
use App\Http\Resources\Any\Collection\TrackResource as PublicTrackResource;
use App\Http\Resources\Api\TrackResource;
use App\Models\Authors\AuthorProfile;
use App\Models\Track;
use App\Models\TrackPrice;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Rules\AllOf;
use Respect\Validation\Rules\Not;
use Respect\Validation\Rules\NotEmpty;
use Respect\Validation\Rules\In;
use Respect\Validation\Rules\Unique;
use Spatie\ResponseCache\Facades\ResponseCache;

class TracksService extends AbstractModelService
{
    protected $modelClass = Track::class;

    public function __construct(
        ImagesService $imagesService,
        MetaService $metaService,
        TaggingService $taggingService,
    ) {
        parent::__construct($imagesService, $metaService, $taggingService);
    }

    protected $taggable = ['tags', 'genres', 'instruments', 'moods', 'types'];

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function buildDataFromRequest(Request $request)
    {
        [$data, $meta, $image, $taggable] = parent::buildDataFromRequest($request);

        if (isset($data[ 'author' ])) {
            $data[ 'author_profile_id' ] = $data[ 'author' ];
        }

        if (isset($data[ 'author_name' ])) {
            $author = AuthorProfile::where('name', $data[ 'author_name' ])->first();

            if ($author) {
                $data[ 'author_profile_id' ] = $author->id;
            }
        }

        $prices = $data[ 'prices' ] ?? [];

        return [$data, $meta, $image, $taggable, $prices];
    }

    /**
     * @param Track $track
     * @param $builtData
     *
     * @return TrackResource
     * @throws Exception
     */
    protected function fillInModel($track, $builtData)
    {
        [$data, $meta, $images, $taggable, $prices] = $builtData;

        $track->disableModelCaching()->fill($data);
        $track->save();

        $this->metaService->fillInForObject($track, $meta);
        $this->imagesService->upload($track, $images);
        $this->taggingService->process($track, $taggable);

        if (isset($prices) && ! empty($prices)) {
            foreach ($prices as $license_id => $price) {
                if (! is_numeric($price) && empty($price)) {
                    TrackPrice::where([
                        'track_id'   => $track->id,
                        'license_id' => $license_id,
                    ])->delete();

                    continue;
                }

                TrackPrice::updateOrCreate([
                    'track_id'   => $track->id,
                    'license_id' => $license_id,
                ], [
                    'track_id'   => $track->id,
                    'license_id' => $license_id,
                    'price'      => $price,
                ]);
            }
        }

        /**
         * @var $elasticService ElasticService
         */
        $elasticService = resolve(ElasticService::class);
        $elasticService->mixify($track);

        ResponseCache::clear();
        $track->flushCache();
        Cache::forget("meta:". Track::class . ":{$track->id}");

        return new TrackResource($track);
    }

    public function getForMain()
    {
        $tracks = Track::with(['audio', 'prices', 'images', 'author'])
                       ->where([
                           'has_content_id' => false,
                           'featured' => true,
                       ])
                       ->get();

        return PublicTrackResource::collection($tracks);
    }

    /**
     * @param Request $request
     * @return Track
     */
    public function create(Request $request): Track
    {
        $meta = $request->get('meta');

        $data = [
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'author_profile_id' => $request->input('author_id'),
            'description' => $request->input('description'),
            'tempo' => $request->input('bpm') ?? 0,
            'hidden' => $request->input('hidden') ?? 0,
            'featured' => $request->input('featured') ?? 0,
            'has_content_id' => $request->input('has_content_id') ?? 0,
            'exclusive' => $request->input('exclusive') ?? 0,
            'metaTitle' => $meta['title'] ?? null,
            'metaDescription' => $meta['description'] ?? null,
            'premium' => $request->input('premium') ?? 0,
        ];

        return Track::create($data);
    }

    /**
     * @param Request $request
     * @param Track $track
     * @return Track
     */
    public function updateTrack(Request $request, Track $track): Track
    {
        $meta = $request->get('meta');
        $data = $request->except('meta', 'images', 'tracks', 'genres', 'moods', 'instruments', 'usageTypes', 'tags');
        $data['metaTitle'] = $meta['title'] ?? null;
        $data['metaDescription'] = $meta['description'] ?? null;
        $data['author_profile_id'] = $request->input('author_id');
        $track->update($data);
        return $track;
    }

    /**
     * @param array $files
     * @param Track $track
     * @return true
     */
    public function attachTrack(array $files, Track $track): true
    {
        $pathFiles = $track->audio()->pluck('url');
        foreach ($pathFiles as $pathFile)
        {
            if (file_exists(base_path().'/public_html'.$pathFile))
                unlink(base_path().'/public_html'.$pathFile);
        }
        $track->audio()->delete();

        foreach ($files as $key => $file)
        {
            $storagePublicHtmlPath = date('Y') .'/'. date('m') .'/'. date('d');
            $fullPath = storage_path('app/public_html') .'/'. $storagePublicHtmlPath;
            $fileName = str_replace(' ', '_', $file->getClientOriginalName());
            $attachment = Attachment::create([
                'name' => $fileName,
                'original_name' => $fileName.'.'.$file->getClientOriginalExtension(),
                'mime' => $file->getClientMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
                'path' => $storagePublicHtmlPath.'/',
                'user_id' => auth()->user()->id,
                'disk' => 'public_html'
            ]);
            $file->move($fullPath, $fileName.'.'.$file->getClientOriginalExtension());

            $trackAudio = TrackAudio::create([
                'track_id' => $track->id,
                'type' => $attachment->extension,
                'url' => '/storage/'.$attachment->path.
                    $attachment->name.'.'.$attachment->extension,
                'attachment_id' => $attachment->id,
                'is_hq' => $key === 'hq',
            ]);

            RunAudioWaveformGeneratorJobs::dispatch($trackAudio->id);
            CreateArchiveForTracksEvent::dispatch($track);
        }

        return true;
    }

    /**
     * @param array $images
     * @param Track $track
     * @return true
     */
    public function attachImages(array $images, Track $track): true
    {
        foreach ($images as $key => $image)
        {
            $storagePublicHtmlPath = date('Y') .'/'. date('m') .'/'. date('d');
            $fullPath = storage_path('app/public_html') .'/'. $storagePublicHtmlPath;
            $fileName = str_replace(' ', '_', $image->getClientOriginalName());
            $image->move($fullPath, $fileName.'.'.$image->getClientOriginalExtension());
            Images::updateOrCreate(
                [
                    'type' => Track::class,
                    'type_id' => $track->id,
                    'type_key' => $key,
                ],
                [
                    'url' => '/storage/'.$storagePublicHtmlPath.'/'.$fileName,
                ]
            );
        }
        return true;
    }

    /**
     * @param Request $request
     * @param Track $track
     * @return JsonResponse
     */
    public function saveTrack(Request $request, Track $track): JsonResponse
    {
        $images = $request->images;
        if (!is_null($images))
            $this->attachImages($images, $track);

        $trackFiles = $request->tracks;
        if (!is_null($trackFiles))
            $this->attachTrack($trackFiles, $track);

        $genres = $request->input('genres');
        $moods = $request->input('moods');
        $instruments = $request->input('instruments');
        $usageTypes = $request->input('usageTypes');
        $tags = $request->input('tags');

        AttachTagEvent::dispatch(Tag::class, $tags ?? [], $track->id, Track::class);
        AttachTagEvent::dispatch(Genre::class, $genres ?? [], $track->id, Track::class);
        AttachTagEvent::dispatch(Mood::class, $moods ?? [], $track->id, Track::class);
        AttachTagEvent::dispatch(Instrument::class, $instruments ?? [], $track->id, Track::class);
        AttachTagEvent::dispatch(Type::class, $usageTypes ?? [], $track->id, Track::class);
        Mixify::dispatch($track);

        return response()->json(new TrackResource($track->load('audio')));
    }

    /**
     * @param array $values
     * @param int|null $track_id
     * @return array
     */
    public function validation(array $values, ?int $track_id = null): array
    {
        $errors = [];

        $rules = $this->rules($track_id);
        foreach ($rules as $property => $rule)
        {
            try {
                $value = $values[$property] ?? null;
                $rule->check($value);
            } catch (ValidationException $e) {
                $errors[$property] = $e->getMessage();
            }
        }

        return $errors;
    }

    /**
     * @param int|null $track_id
     * @return array
     */
    private function rules(?int $track_id = null): array
    {
        $trackSlugs = Track::query();
        if (!is_null($track_id))
            $trackSlugs = $trackSlugs->where('id', '!=', $track_id);
        $trackSlugs = $trackSlugs->pluck('slug')->toArray();

        return [
            'author_id' => (new AllOf(
                (new NotEmpty())
                    ->setTemplate(
                        'The author_id field is required'
                    ),
                (new In(auth()->user()->authors()->pluck('id')->toArray()))
                    ->setTemplate(
                        'This author is not linked to your account'
                    )
            ))->setName('author_id'),

            'slug' => (new AllOf(
                (new NotEmpty())
                    ->setTemplate(
                        'The slug field is required'
                    ),
                (new Not(
                    (new In($trackSlugs))
                        ->setTemplate('A track with slug servants already exists')
                ))
            ))->setName('slug'),
        ];
    }

    /**
     * @param int $authorId
     * @param float $totalAllSubscription
     * @param array $coefficients
     * @return array
     */
    public function getTracksForPortfolio(int $authorId, array $coefficients, float $downloadOneTrack): array
    {
        return Track::withCount(['downloads' => function ($query) {
            $query->whereIn('license_id', [12, 13]);
        }])->where('author_profile_id', $authorId)
            ->withSum(['orderItem' => function ($query) {
                $query->whereDate('created_at', '>=', Carbon::now()->startOfMonth());
            }], 'price')
            ->withCount(['downloads as current_month_downloads' => function ($query) {
                $query->whereIn('license_id', [12, 13])
                    ->whereDate('created_at', '>=', Carbon::now()->startOfMonth());
            }])
            ->withSum('detailedBalances', 'award')
            ->withCount('orderItem')->get()
            ->map(function ($track) use ($coefficients, $downloadOneTrack) {
                $totalLicense = $track->order_item_sum_price *
                    ($track->exclusive ? $coefficients['wex'] : $coefficients['wnoex']);

                $currentMonthDownloads = $track->current_month_downloads * $downloadOneTrack
                    * ($track->exclusive ? $coefficients['wex'] : $coefficients['wnoex']);

                $sum = $track->detailed_balances_sum_award + $totalLicense + $currentMonthDownloads;
                return [
                    'id' => $track->id,
                    'name' => $track->name,
                    'slug' => $track->slug,
                    'category' => 'Track',
                    'sales' => $track->order_item_count,
                    'downloads' => $track->downloads_count,
                    'rate' => $track->exclusive ? $coefficients['wex'] * 10 : $coefficients['wnoex'] * 10,
                    'total' => (float)number_format($sum, 2, '.', '')
                ];
            })->toArray();
    }
}
