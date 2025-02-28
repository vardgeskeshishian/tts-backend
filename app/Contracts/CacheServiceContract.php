<?php


namespace App\Contracts;

interface CacheServiceContract
{
    public function getFreeLicenses();
    public function getUsdRate();
    public function isOnlineKassaEnabled(): bool;

    public function warmUpCache(string $cacheKey);
}
