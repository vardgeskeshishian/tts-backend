<?php

namespace App\Traits;

use App\Filters\QueryAbstractFilter;
use Illuminate\Database\Eloquent\Builder;

trait QueryCategoriesFilterable
{
    /**
     * @param Builder $builder
     * @param QueryAbstractFilter $filter
     */
    public function scopeFilterCategories(Builder $builder, QueryAbstractFilter $filter): void
    {
        $filter->applyWithCategories($builder);
    }
}