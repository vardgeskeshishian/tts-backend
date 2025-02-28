<?php

namespace App\Http\Controllers\System;

use Carbon\Carbon;
use App\Models\SFX\SFXTrack;
use App\Models\SyncStatistic;
use App\Services\ImagesService;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Api\ApiController;
use App\Services\SFX\SoundEffectsSynchronizeService;

class SoundEffectsController extends ApiController
{
    /**
     * @var ImagesService
     */
    private $service;

    /**
     * SoundEffectsController constructor.
     *
     * @param ImagesService $service
     */
    public function __construct(ImagesService $service)
    {
        $this->service = $service;
    }

    public function callSyncTracks(SoundEffectsSynchronizeService $synchronizeService)
    {
        $effects = glob('/mnt/volume_sfo2_02/SFX/*.wav');

        $synchronisationStatistic = SyncStatistic::first();
        $latestSyncTime = optional($synchronisationStatistic)->finished_at;

        if (!$synchronisationStatistic) {
            $synchronisationStatistic = SyncStatistic::create();
        }

        $synchronisationStatistic->started_at = Carbon::now();

        $created = 0;
        $updated = 0;
        $deleted = 0;

        foreach ($effects as $effect) {
            $synchronizeService->sync($effect, $created, $updated, $latestSyncTime);
        }

        /**
         * foreach sfx track check if file exists, if not -> delete
         */
        SFXTrack::select('id')->with('params')->chunk(100, function ($effects) use (&$deleted) {
            /**
             * @var $effect SFXTrack
             */
            foreach ($effects as $effect) {
                if (file_exists($effect->params->ftp_link)) {
                    continue;
                }

                $effect->delete();
                $deleted++;
            }
        });

        $synchronisationStatistic->fill([
            'created' => $created,
            'updated' => $updated,
            'deleted' => $deleted,
            'finished_at' => Carbon::now(),
        ])->save();

        return redirect()->back();
    }

    public function test()
    {
        /**
         * @var $sound UploadedFile|null
         */
        $sound = request()->files->get('sound');


        shell_exec("ffprobe -v quiet -show_format -show_streams -print_format json {$sound->getFileInfo()}");

        return view('admin.sounds.test');
    }

    public function listView()
    {
        if (request()->has('q')) {
            $query = request()->get('q');
            $list = SFXTrack::where('name', 'like', "%{$query}%")->paginate();
        } else {
            $list = SFXTrack::paginate();
        }

        return view('admin.sound-effects.list', compact('list'));
    }

    public function singleView($effect)
    {
        $effect = SFXTrack::find($effect);

        return view('admin.sound-effects.single', compact('effect'));
    }
}
