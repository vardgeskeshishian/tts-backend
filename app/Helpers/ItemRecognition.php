<?php

namespace App\Helpers;

use App\Constants\Env;
use App\Models\Track;
use App\Models\VideoEffects\VideoEffect;

class ItemRecognition
{
    public static function getDistinctItemIdByModel(Track|VideoEffect $track)
    {
        $prefix = match ($track::class) {
            VideoEffect::class => "template",
            default => "track",
        };

        return sprintf("%s_%d", $prefix, $track->id);
    }

    public static function getDistinctItemIdByType($itemType, $itemId)
    {
        $prefix = match ($itemType) {
            Env::ITEM_TYPE_VIDEO_EFFECTS => "template",
            default => "track",
        };

        return sprintf("%s_%d", $prefix, $itemId);
    }
}