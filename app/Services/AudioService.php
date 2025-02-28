<?php

namespace App\Services;

use App\Models\Track;
use App\Models\TrackAudio;
use App\Repositories\AudioRepository;
use App\Traits\CanStore;
use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use maximal\audio\Waveform;

class AudioService
{
    use CanStore;

    /**
     * @var $storage FilesystemAdapter
     */
    protected $storage;
    /**
     * @var AudioRepository
     */
    private $audioRepository;
    /**
     * @var ZipWavService
     */
    private $zipWavService;

    /**
     * AudioService constructor.
     *
     * @param AudioRepository $audioRepository
     * @param ZipWavService $zipWavService
     */
    public function __construct(
        AudioRepository $audioRepository,
        ZipWavService $zipWavService
    ) {
        $this->storage         = $this->getStorage();
        $this->audioRepository = $audioRepository;
        $this->zipWavService   = $zipWavService;
    }

    /**
     * @param Track $track
     * @param Request $request
     *
     * @return mixed
     * @throws Exception
     */
    public function upload(Track $track, Request $request)
    {
        $file     = $request->file('audio');
        $waveform = $request->get('waveform', []);
        $audioId  = $request->get('id');

        if (is_string($waveform)) {
            $waveform = explode(',', $waveform);
        }

        $waveform = array_map(function ($item) {
            return (float)$item;
        }, $waveform);

        $waveform = json_encode($waveform);

        if ($audioId && $waveform) {
            /**
             * @var $audio TrackAudio
             */
            $audio = TrackAudio::find($audioId);

            abort_if(! $audio, 500);

            $audio->waveform = [];
            $audio->save();
            $audio->refresh()->flushCache();

            $this->storage->put("/waveforms/$track->id/$audio->id.json", $waveform);

            $waveformLink = storage_path("/app/public/waveforms/$track->id/$audio->id.json");

            $this->setCloudNamespace('waveforms')
                ->storeInCloud("/$track->id", "$audio->id.json", $waveformLink);

            return $audio;
        }

        $trackSlug = Str::slug($track->name);

        $fileName = $file->getClientOriginalName();

        $path         = "audio/{$trackSlug}";
        $audioName    = str_replace(' ', '-', $fileName);
        $songLocalUrl = $this->storage->putFileAs($path, $file, $audioName);
        $link         = '/storage/' . $songLocalUrl;

        $audio = $this->audioRepository->insertOne([
            'track_id'     => $track->id,
            'type'         => $file->getClientOriginalExtension(),
            'preview_name' => $request->get('preview_name', ''),
            'duration'     => $request->get('duration', 0),
            'url'          => $link,
            'waveform'     => [],
        ]);

        $audio->preview_name = $this->getSongPreviewName($track, $audio);
        $audio->duration = $this->getSongDuration($audio);
        $audio->save();

        $this->storage->put("/waveforms/$track->id/$audio->id.json", $waveform);

        $waveformLink = storage_path("/app/public/waveforms/$track->id/$audio->id.json");

        $this->storeInCloud("/waveforms/$track->id", "$audio->id.json", $waveformLink);

        return $audio;
    }

    public function waveform(Request $request, TrackAudio $audio)
    {
        $waveform = $request->get('waveform');

        $audio->waveform = $waveform;
        $audio->save();

        $audio->refresh()->flushCache();

        return $audio;
    }

    /**
     * @throws Exception
     */
    public function generateWaveform(Waveform $waveform): string
    {
        $lines = $waveform->getWaveformData(128);
        $lines = $lines['lines1'];
        $center = 32;

        $svg = '<svg width="1024" height="64" viewBox="0 0 1024 64" xmlns="http://www.w3.org/2000/svg">
                    <path d="';

        $upLine = 'M0 32';
        $downLine = 'M0 32';

        $coefficientHeight = max(max($lines), abs(min($lines))) == 0 ? 0 : 1 / max(max($lines), abs(min($lines)));

        for ($i = 0; $i < count($lines); $i += 2)
        {
            $x = $i / 2;
            $min = $lines[$i];
            $max = $lines[$i + 1];
            $upLine .= 'L'.$x.' '.($center - $max * $center * $coefficientHeight);
            $downLine .= 'L'.$x.' '.($center - $min * $center * $coefficientHeight);
        }

        $svg .= $upLine.' '.$downLine.'L'.$x.' '.($center - $max * $center * $coefficientHeight);
        $svg .= '"/></svg>';

        return $svg;
    }

    /**
     * @param TrackAudio $audio
     *
     * @return bool
     * @throws Exception
     */
    public function delete(TrackAudio $audio)
    {
        return $audio->delete() !== null;
    }

    /**
     * Run ffprobe to get song duration
     *
     * @param TrackAudio $audio
     *
     * @return float|int
     */
    public function getSongDuration(TrackAudio $audio): float|int
    {
        $fullStorageLink = base_path().$audio->url;
        exec(
            "ffprobe -i $fullStorageLink -show_entries format=duration -v quiet -of csv=\"p=0\"",
            $output
        );

        if (count($output) > 0) {
            return floatval($output[0]);
        }

        return 0;
    }

    /**
     * @param TrackAudio $audio
     * @return string
     */
    public function getSongWaveform(TrackAudio $audio): string
    {
        $pathJson = '/audio';
        if (!Storage::exists($pathJson))
            Storage::createDirectory($pathJson);

        $pathJson = Storage::path($pathJson).'/'.$audio->id.'.json';
        exec('audiowaveform -i '.base_path('/public_html').$audio->url.' -o '.$pathJson.' -b 8 -z 12000', $output);

        $json = file_get_contents($pathJson);
        $json = json_decode($json, true);

        $waveformData = $json['data'];

        $width = 500;
        $height = 200;
        $lineColor = 'blue';
        $lineWidth = 2;

        $maxAmplitude = max($waveformData);
        $normalizedData = array_map(function ($amplitude) use ($maxAmplitude, $height) {
            return $height / 2 - ($amplitude / $maxAmplitude) * ($height / 2);
        }, $waveformData);

        $xInterval = $width / count($normalizedData);

        $pathData = "M0," . ($height / 2);
        for ($i = 0; $i < count($normalizedData); $i++) {
            $x = $i * $xInterval;
            $y = $normalizedData[$i];
            $pathData .= " L" . $x . "," . $y;
        }

        $svg = '<svg width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
        $svg .= '<path d="' . $pathData . '" stroke="' . $lineColor . '" stroke-width="' . $lineWidth . '" fill="none"/>';
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * @param Track $track
     * @param TrackAudio $audio
     *
     * @return string
     */
    public function getSongPreviewName(Track $track, TrackAudio $audio)
    {
        $exploded = explode('/', $audio->getRawUrlAttribute());
        $audioName = $exploded[count($exploded) - 1];
        $previewFileName = ltrim(str_replace([$track->slug, 'preview'], '', $audioName), '-');
        $preview = explode('.', $previewFileName)[0];

        $preview = str_replace('-', ' ', $preview);
        $preview = preg_replace('/(loop)\s(\d)/', '$1$2', $preview);

        return strtolower($preview);
    }
}
