<?php

namespace App\Services;

use Carbon\Carbon;

class LicenseNumberService
{
    public function generate($license)
    {
        $prefix = "TT";
        $suffix = random_int(1000, 9999).'-'.Carbon::now()->timestamp;
        $name   = $license->type;

        return "{$prefix}{$name}{$suffix}";
    }
}
