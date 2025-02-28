<?php


namespace App\Services\SFX;

use Exception;
use Carbon\Carbon;
use TelegramLoggerFacade;
use App\Jobs\ProcessTags;
use Illuminate\Support\Str;
use App\Constants\QueueEnv;
use App\Models\SFX\SFXTrack;
use App\Jobs\ConvertSFXToMp3;
use App\Services\TaggingService;
use App\Services\ElasticService;
use App\Jobs\RunSFXWaveformGenerator;
use Illuminate\Support\LazyCollection;
use App\Contracts\TelegramLoggerContract;

class SoundEffectsSynchronizeService
{
    /**
     * @var ElasticService
     */
    private ElasticService $elasticService;
    /**
     * @var TaggingService
     */
    private TaggingService $taggingService;

    public function __construct(ElasticService $elasticService, TaggingService $taggingService)
    {
        $this->elasticService = $elasticService;
        $this->taggingService = $taggingService;
    }

    public function run()
    {
        $soundEffects = [];

        LazyCollection::make(function () {
            $effects = glob('/mnt/volume_sfo2_02/SFX/*.wav');

            TelegramLoggerFacade::pushToChat(
                TelegramLoggerContract::CHANNEL_DEBUG_ID,
                "Starting sound effects sync",
                [
                    'tracks-on-ftp' => count($effects),
                    'tracks-on-production' => SFXTrack::count(),
                ]
            );

            foreach ($effects as $effect) {
                yield $effect;
            }
        })->each(function ($effect) use (&$soundEffects) {
            $soundEffects[] = $this->sync($effect);
        });

        $soundEffectsCurrent = SFXTrack::all()->pluck('id')->all();

        $effectsToDelete = array_diff($soundEffects, $soundEffectsCurrent);

        SFXTrack::whereIn('id', $effectsToDelete)->delete();

        TelegramLoggerFacade::pushToChat(
            TelegramLoggerContract::CHANNEL_DEBUG_ID,
            "Successfully synced sound effects",
        );
    }

    public function sync($soundEffect, &$created, &$updated, $latestSyncTime = null)
    {
        if ($latestSyncTime) {
            $lastModified = Carbon::createFromTimestamp(filemtime($soundEffect));

            if ($lastModified->lessThan($latestSyncTime)) {
                return;
            }
        }

        $escaped = escapeshellarg($soundEffect);
        $result = shell_exec("ffprobe -v quiet -show_format -print_format json {$escaped}");
        $result = json_decode($result, true);

        $format = $result['format'];
        $tags = $format['tags'];
        $duration = $format['duration'];
        $extension = $format['format_name'];
        $filename = pathinfo($format['filename'], PATHINFO_FILENAME);

        $slug = Str::slug($filename);
        $slugWithExtension = "{$slug}.{$extension}";

        $storagePath = '/home/admin/web/static.taketones.com/public_html/sfx/audio/';

        $localFile = "{$storagePath}{$slugWithExtension}";

        try {
            mkdir($storagePath, '0777', true);
        } catch (Exception $e) {
        }

        $alreadyLinked = false;
        try {
            symlink($soundEffect, $localFile);
        } catch (Exception $e) {
            $alreadyLinked = true;
        }

        $sqlDir = "/sfx/audio/{$slugWithExtension}";

        $effect = SFXTrack::updateOrCreate([
            'name' => $filename,
            'extension' => $extension,
        ], [
            'price' => 10,
            'name' => $filename,
            'extension' => $extension,
            'duration' => $duration,
            'link' => $sqlDir,
            'ftp_link' => $soundEffect,
        ]);

        $effect->flushCache();
        $effect->restore();

        $effect->params()->updateOrCreate([
            'sfx_track_id' => $effect->id,
        ], [
            'album' => $tags['album'] ?? null,
            'artist' => $tags['artist'] ?? null,
            'bit_rate' => (int)$format['bit_rate'],
            'synced_at' => Carbon::now(),
        ]);

        $categories = $tags['genre'] ?? '';
        $tagsFromComment = $tags['comment'] ?? '';

        $taggable = [
            TaggingService::SFX_CATEGORY => explode(',', $categories),
            TaggingService::SFX_TAG => explode(',', $tagsFromComment),
        ];

        ProcessTags::dispatch($effect, $taggable)->onQueue(QueueEnv::TRACKS_WORKER);

        if ($alreadyLinked) {
            $updated++;
            return;
        }

        $created++;

        RunSFXWaveformGenerator::dispatch($effect->id);
        ConvertSFXToMp3::dispatch($effect->id);
    }
}
