<?php

namespace App\Repositories;

use App\Models\Tags\TagPosition;
use App\Models\Tags\AbstractTag;

class TagPositionRepository extends BasicRepository
{
    protected $modelName = TagPosition::class;

    public function updateForModel(AbstractTag $tag, int $id, int $position)
    {
        $dataToFind = [
            'taggable_type' => $tag->getMorphClass(),
            'taggable_id' => $id
        ];

        $dataToUpdate = [
            'position' => $position
        ];

        $fullData = array_merge($dataToFind, $dataToUpdate);

        return $this->model->updateOrCreate($dataToFind, $fullData);
    }
}
