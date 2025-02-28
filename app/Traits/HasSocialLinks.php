<?php

namespace App\Traits;

use App\Models\SocialLinkPivot;

trait HasSocialLinks
{
    public function socialLinks()
    {
        return $this->hasMany(SocialLinkPivot::class, 'object_id')
            ->where('object_class', $this->getMorphClass())->with('link');
    }
}
