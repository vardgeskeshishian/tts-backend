<?php

namespace App\Repositories;

use App\Models\Images;
use Exception;
use Illuminate\Database\Eloquent\Model;

class ImagesRepository extends BasicRepository
{
    protected $types = [
        'background',
        'thumbnail',
        'icon'
    ];

    protected $modelName = Images::class;

    public function getForModel(Model $model)
    {
    }

    /**
     * @param Model $model
     * @param string $type
     *
     * @return Images
     * @throws Exception
     */
    public function findForModel(Model $model, string $type = 'background')
    {
        if (!in_array($type, $this->types)) {
            throw new Exception("Can't find image for this type {$type}");
        }

        return $this->findOneWhere([
            'type' => $model->getMorphClass(),
            'type_id' => $model->id,
            'type_key' => $type
        ]);
    }

    /**
     * @param Model $model
     * @param string $url
     * @param string $type
     *
     * @return bool
     * @throws Exception
     */
    public function insertOneForModel(Model $model, string $url, string $type)
    {
        $exists = $this->findForModel($model, $type);

        $morphData = [
            'type' => $model->getMorphClass(),
            'type_id' => $model->id,
            'type_key' => $type,
        ];

        $insertedData = ['url' => $url];

        if ($exists) {
            return $exists->fill($insertedData)->save();
        } else {
            return $this->insertOne(array_merge($morphData, $insertedData));
        }
    }

    public function insertSimple(string $simpleType, string $url, string $type)
    {
        return $this->insertOne([
            'type' => $simpleType,
            'type_id' => 0,
            'type_key' => $type,
            'url' => $url
        ]);
    }
}
