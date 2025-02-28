<?php

namespace App\Repositories;

use App\Models\Images;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;

class VideosRepository extends BasicRepository
{
    protected $modelName = Video::class;

    public function getForModel(Model $model)
    {
    }

    /**
     * @param Model $model
     *
     * @return Images
     */
    public function findForModel(Model $model)
    {
        return $this->findOneWhere([
            'type' => $model->getMorphClass(),
            'type_id' => $model->id,
        ]);
    }

    /**
     * @param Model $model
     * @param string $url
     * @return bool
     */
    public function insertOneForModel(Model $model, string $url)
    {
        $morphData = [
            'type' => $model->getMorphClass(),
            'type_id' => $model->id,
        ];

        $insertedData = ['url' => $url];

        return $this->insertOne(array_merge($morphData, $insertedData));
    }

    public function insertSimple(string $simpleType, string $url)
    {
        return $this->insertOne([
            'type' => $simpleType,
            'type_id' => 0,
            'url' => $url
        ]);
    }
}
