<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GeneratesSlug
{
    /**
     * Generating slug on track creation
     */
    protected static function bootGeneratesSlug()
    {
        static::creating(function ($model) {
            $model->slug = $model->slug ?? Str::slug($model->name);
        });
    }
}
