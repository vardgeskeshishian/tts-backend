<?php

namespace App\Services\Cache;

use App\Constants\CacheEnv;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Models\VideoEffects\VideoEffectVersion;

class CacheServiceMapper
{
    private $map = [
        VideoEffectApplication::class => CacheEnv::VFX_APPLICATION,
        VideoEffectVersion::class => CacheEnv::VFX_VERSION
    ];

    private function getModelKey(string $modelClass): string
    {
        return $this->map[$modelClass] ?? "";
    }

    public function buildKey(string $modelClass, $attr): string
    {
        $key = $this->getModelKey($modelClass);

        return "$key$attr";
    }
}