<?php

namespace App\Traits;

trait CanFormatImages
{
    protected function formatImages($imgs)
    {
        if (is_array($imgs)) {
            $imgs = collect($imgs);
        }

        return $imgs->count() > 0 ? $imgs->mapWithKeys(fn ($i) => [$i->type_key => $i->url]) : [];
    }
}
