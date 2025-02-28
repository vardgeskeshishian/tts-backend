<?php

namespace App\Repositories;

use App\Models\Tags\Tagging;

class TaggingRepository extends BasicRepository
{
    protected $modelName = Tagging::class;

    public function getWhere(array $values)
    {
        return $this->model->where($values)->get();
    }
}
