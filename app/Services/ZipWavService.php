<?php

namespace App\Services;

use App\Models\Track;
use App\Models\TrackAudio;
use App\Models\TrackArchive;
use Exception;
use Illuminate\Support\Str;
use ZipArchive;

class ZipWavService
{
    /**
     * @var OneTimeLinkService
     */
    private $oneTimeLinkService;

    public function __construct(OneTimeLinkService $oneTimeLinkService)
    {
        $this->oneTimeLinkService = $oneTimeLinkService;
    }

    /**
     * @param TrackAudio $trackAudio
     *
     * @return mixed
     * @throws Exception
     */
    public function addWavToZip(TrackAudio $trackAudio)
    {
        $track = $trackAudio->track;

        if (! $track->archive) {
            $archiveDir  = storage_path('tracks/archives/');
            $archiveName = Str::slug($track->full_name) . '.zip';

            if (! is_dir($archiveDir)) {
                mkdir($archiveDir, 0777, true);
            }

            $archivePath     = "/tracks/archives/{$archiveName}";
            $archiveFullName = $archiveDir . $archiveName;
        } else {
            $archivePath     = $track->archive->path;
            $archiveFullName = storage_path($archivePath);
        }

        $archive = new ZipArchive();

        if ($archive->open($archiveFullName, ZipArchive::CREATE)) {
            $url  = str_replace('storage/', '', $trackAudio->getRawUrlAttribute());
            $file = storage_path('/app/public/' . $url);

            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $name      = $trackAudio->name
                ? $trackAudio->name . '.' . $extension
                : basename($file);

            if (! $archive->addFile($file, $name)) {
                throw new Exception("file `{$file}` could not be added to the zip file: " . $archive->getStatusString());
            }

            if ($archive->close()) {
                return TrackArchive::updateOrCreate([
                    'track_id' => $track->id,
                ], [
                    'track_id' => $track->id,
                    'path'     => $archivePath,
                ]);
            }
        }
    }

    /**
     * @param Track $track
     *
     * @return mixed
     */
    public function makeZipArchiveFromTrack(Track $track)
    {
        $archive = new ZipArchive();

        if (! $track->archive) {
            $archiveDir  = storage_path('tracks/archives/');
            $archiveName = Str::slug($track->full_name) . '.zip';

            if (! is_dir($archiveDir)) {
                mkdir($archiveDir, 0777, true);
            }

            $archivePath     = "/tracks/archives/{$archiveName}";
            $archiveFullName = $archiveDir . $archiveName;
        } else {
            $archivePath     = $track->archive->path;
            $archiveFullName = storage_path($archivePath);
        }

        $openable = $archive->open($archiveFullName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if (!$openable) {
            return;
        }

        $track->audio->except('waveform')->map(function ($audio) use (
            $track,
            $archive,
            $archiveFullName,
            $archivePath
        ) {
            if (strtolower($audio->format) !== 'wav') {
                return;
            }

            $url  = str_replace('storage/', '', $audio->getRawUrlAttribute());
            $file = storage_path('/app/public/' . $url);

            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $name      = $audio->name
                ? $audio->name . '.' . $extension
                : basename($file);

            if (! $archive->addFile($file, $name)) {
                throw new Exception("file `{$file}` could not be added to the zip file: " . $archive->getStatusString());
            }
        });

        if ($archive->close()) {
            TrackArchive::updateOrCreate([
                'track_id' => $track->id,
            ], [
                'track_id' => $track->id,
                'path'     => $archivePath,
            ]);

            return $this->oneTimeLinkService
                ->generate('z', [
                    'di' => $track->id
                ], 1440);
        }
    }
}
