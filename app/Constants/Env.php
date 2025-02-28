<?php

namespace App\Constants;

class Env
{
    const STATUS_NEW = 'new';
    const STATUS_FINISHED = 'finished';
    const STATUS_REFUNDED = 'refunded';

    const TYPE_FAST = 'fast';
    const ORDER_TYPE_FULL = 'full';

    const ITEM_TYPE_TRACKS = 'App\Models\Track';
    const ITEM_TYPE_PACKS = 'sfx_packs';
    const ITEM_TYPE_EFFECTS = 'App\Models\SFX\SFXTrack';
    const ITEM_TYPE_VIDEO_EFFECTS = 'App\Models\VideoEffects\VideoEffect';

    const USER_DOWNLOADS_TYPE_PREVIEW = 'preview-download';
    const USER_DOWNLOADS_TYPE_VIDEO_EFFECTS_D = 'video-effects-download';
}
