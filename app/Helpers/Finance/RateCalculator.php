<?php

namespace App\Helpers\Finance;

use App\Constants\SubmissionsEnv;
use App\Models\Track;
use App\Models\VideoEffects\VideoEffect;

class RateCalculator
{
    public static function getRate(Track|VideoEffect $track)
    {
        return match ($track::class) {
            VideoEffect::class => $track->exclusivity ? 50 : 40,
	    Track::class => self::getTrackRate($track),
    	};
    }

    public static function getTrackRate($track) 
    {
	$coopType = null;
	if ($track->submission && $track->submission->exclusive) {
	    $coopType = $track->submission->coop_type;
	}

	if ($track->coop_type) {
	    $coopType = $track->coop_type;
	}

	return $coopType === SubmissionsEnv::COOP_TYPE_EXC ? 50 : 40;
    }
}
