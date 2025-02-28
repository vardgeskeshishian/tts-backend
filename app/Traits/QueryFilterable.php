<?php

namespace App\Traits;

use App\Exceptions\EmptySearchResult;
use App\Filters\QueryAbstractFilter;
use Illuminate\Database\Eloquent\Builder;

trait QueryFilterable
{
    /**
     * @param Builder $builder
     * @param QueryAbstractFilter $filter
     * @throws EmptySearchResult
     */
    public function scopeFilter(Builder $builder, QueryAbstractFilter $filter): void
    {
        $filter->apply($builder);
    }
}