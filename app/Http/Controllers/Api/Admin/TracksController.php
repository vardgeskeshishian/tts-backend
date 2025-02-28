<?php

namespace App\Http\Controllers\Api\Admin;

use App\Services\TracksService;
use App\Jobs\RunWaveformGenerator;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\Collection\TrackResource as TrackCollectionResource;
use App\Http\Resources\Api\TrackResource;
use App\Models\Track;
use App\Models\TrackAudio;
use App\Repositories\TracksRepository;
use App\Services\AudioService;
use App\Services\ZipWavService;
use App\Models\TrackArchive;
use App\Scopes\ExcludeTrackScope;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Spatie\ResponseCache\Facades\ResponseCache;

class TracksController extends ApiController
{
    /**
     * @var TracksService
     */
    private $tracksService;

    /**
     * @var AudioService
     */
    private $audioService;

    protected $repository = TracksRepository::class;
    /**
     * @var ZipWavService
     */
    private $zipWavService;

    /**
     * TracksController constructor.
     *
     * @param TracksService $tracksService
     * @param AudioService $audioService
     * @param ZipWavService $zipWavService
     */
    public function __construct(
        TracksService $tracksService,
        AudioService $audioService,
        ZipWavService $zipWavService
    ) {
        parent::__construct();

        $this->tracksService = $tracksService;
        $this->audioService = $audioService;
        $this->zipWavService = $zipWavService;
    }

    /**
     * @param Track $track
     *
     * @return JsonResponse
     */
    public function find($track): JsonResponse
    {
        $track = Track::find($track);
        return $this->success(new TrackResource($track));
    }

    public function findOriginal(Track $track)
    {
        return $this->success([
            'audio' => $track->audio->map(function ($item) {
                return [
                    'url' => $item->static_url,
                    'id' => $item->id
                ];
            })
        ]);
    }

    /**
     * @return AnonymousResourceCollection
     * @noinspection PhpMethodParametersCountMismatchInspection
     */
    public function get(): AnonymousResourceCollection
    {
        $sort = request('sort', []);
        $filter = request('filter', []);

        $by = $sort['by'] ?? 'id';
        $order = $sort['order'] ?? 'desc';

        $perpage = request('perpage', 15);

        $tracks = Track::withoutGlobalScope(ExcludeTrackScope::class);

        if (!empty($filter) && $filter['by'] === 'name') {
            $filter['value'] = strtolower($filter['value']);
            $tracks = $tracks->whereRaw('LOWER(tracks.name) like ?', "%{$filter['value']}%");
        }

        if ($by === 'author_name') {
            $tracks = $tracks->join('authors as a', 'tracks.author_id', '=', 'a.id')
                ->select('tracks.*', 'a.id', 'a.name as false_name')
                ->orderBy('false_name', $order);
        } else {
            $tracks = $tracks->orderBy($by, $order);
        }

        return TrackCollectionResource::collection($tracks->paginate($perpage)->appends(request()->except('page')));
    }

    public function createTrack(Request $request): JsonResponse
    {
        return $this->wrapCall($this->tracksService, 'create', $request);
    }

    /**
     *
     * @param Request $request
     * @param Track $track
     * @return JsonResponse
     */
    public function updateTrack(Request $request, Track $track): JsonResponse
    {
        return $this->wrapCall($this->tracksService, 'update', $request, $track);
    }

    public function deleteTrack(Track $track): JsonResponse
    {
        $audiosResult = TrackAudio::where('track_id', $track->id)->delete();
        $trackResult = $track->delete();

        $result = $audiosResult && $trackResult;

        if (!$result) {
            $result = [
                'status' => 'No Ok',
                'audios' => $audiosResult,
                'track' => $trackResult
            ];
        }

        return $this->success($result);
    }

    public function createAudio(Track $track, Request $request): JsonResponse
    {
        $index = $request->get('index');

        $result = $this->audioService->upload($track, $request);

        return $this->success([
            'audio' => $result,
            'index' => $index
        ]);
    }

    public function updateAudio(Request $request, Track $track, TrackAudio $audio): JsonResponse
    {
        if ($audio->track_id !== $track->id) {
            return $this->error("", "Can't manipulate with audio of a different track");
        }

        $audio = $this->audioService->waveform($request, $audio);

        return $this->success($audio);
    }

    public function deleteAudio(Track $track, TrackAudio $audio): JsonResponse
    {
        if ($audio->track_id !== $track->id) {
            return $this->error("", "Can't manipulate with audio of a different track");
        }

        $result = $this->audioService->delete($audio);

        return $this->success($result);
    }

    public function makeArchive(Track $track): JsonResponse
    {
        return $this->success($this->zipWavService->makeZipArchiveFromTrack($track));
    }

    public function findTrackByName($name): Collection
    {
        return Track::where('name', 'like', "%$name%")->get();
    }

    public function exportFromFTP(Track $track): JsonResponse
    {
        $audios = glob("/mnt/volume_sfo2_02/music/{$track->name}/*");

        if (empty($audios)) {
            return $this->error("{$track->name} not found on ftp", "");
        }

        foreach ($audios as $audioLink) {
            $audioLink = str_replace(' ', '\\ ', trim($audioLink));
            $exploded = explode('/', $audioLink);
            $splitted = array_slice($exploded, 4);

            $trackName = $splitted[0];
            $trackFile = $splitted[1];
            $trackSlug = Str::slug($trackName);
            $fileExtension = pathinfo($trackFile, PATHINFO_EXTENSION);

            $trackAudioSlug = str_replace($fileExtension, '.' . $fileExtension, Str::slug($trackFile));

            if ($fileExtension !== 'zip') {
                $fileDir = 'app/public/audio/' . md5($track->id);
            } else {
                $fileDir = 'tracks/archives';
            }

            $linkedDir = storage_path($fileDir);

            if (!is_dir($linkedDir)) {
                exec("mkdir -m 777 {$linkedDir} && chown root: {$linkedDir}");
            }

            $localFileDir = $fileExtension === 'zip'
                ? $fileDir . '/' . $trackAudioSlug
                : "/storage/audio/" . md5($track->id) . "/{$trackAudioSlug}";

            $targetDir = $linkedDir . '/' . $trackAudioSlug;

            if (is_file($targetDir)) {
                continue;
            }

            exec("ln -s {$audioLink} {$targetDir}", $o, $r);

            if ($fileExtension !== 'zip') {
                $trackAudio = TrackAudio::create([
                    'track_id' => $track->id,
                    'type' => $fileExtension,
                    'preview_name' => "tmp",
                    'duration' => 0,
                    'url' => $localFileDir,
                    'waveform' => [],
                ]);

                $trackAudio->preview_name = $this->audioService->getSongPreviewName($track, $trackAudio);
                $trackAudio->duration = $this->audioService->getSongDuration($trackAudio);
                $trackAudio->save();
            } else {
                TrackArchive::create([
                    'track_id' => $track->id,
                    'path' => $localFileDir,
                ]);
            }
        }

        Artisan::call('convert:wav-mp3', [
            '--track-id' => $track->id,
        ]);

        RunWaveformGenerator::dispatch($track->id);

        $track->refresh();
        ResponseCache::clear();

        return $this->success([
            'status' => 'ok'
        ]);
    }
}
