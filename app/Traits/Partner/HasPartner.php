<?php


namespace App\Traits\Partner;

use App\Models\Partner\Partner;

trait HasPartner
{
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
