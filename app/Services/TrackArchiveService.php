<?php

namespace App\Services;

use App\Models\Track;
use App\Models\TrackArchive;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ZipArchive;

class TrackArchiveService
{

    /**
     * @var ErrorService
     */
    private $errorService;

    public function __construct(ErrorService $errorService)
    {
        $this->errorService = $errorService;
    }

    /**
     * @param Track $track
     *
     * @return TrackArchive|Model
     * @throws Exception
     */
    public function create(Track $track)
    {
        $sounds = $track->other_tracks;

        $archiveDir = storage_path('tracks/archives/');
        $archiveName = Str::slug($track->full_name) . '.zip';

        if (!is_dir($archiveDir)) {
            mkdir($archiveDir, 0777, true);
        }

        $archiveFullName = $archiveDir . $archiveName;

        $archive = new ZipArchive();

        if ($archive->open($archiveFullName, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            foreach ($sounds as $sound) {
                $file = storage_path('/app/' . $sound->url);

                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $name      = $sound->name ? $sound->name . '.' . $extension : basename($file);

                if ($archive->addFile($file, $name)) {
                    continue;
                } else {
                    throw new Exception("file `{$file}` could not be added to the zip file: " . $archive->getStatusString());
                }
            }

            try {
                if ($archive->close()) {
                    return TrackArchive::updateOrCreate([
                        'track_id' => $track->id
                    ], [
                        'track_id' => $track->id,
                        'path'  => $archiveFullName
                    ]);
                }
            } catch (Exception $e) {
                $this->errorService->logError("Can't close archive for {$track->id}");
            }
        } else {
            $this->errorService->logError("Can't open archive for {$track->id}");
        }
    }
}
