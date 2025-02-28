<?php

namespace App\Filters;

use App\Enums\TypeContentEnum;

class DownloadFilter extends QueryAbstractFilter
{
    /**
     * @param string $type
     * @return void
     */
    public function type(string $type): void
    {
        $this->builder->where('class', TypeContentEnum::getTypeContent($type)->getClass());
    }
}