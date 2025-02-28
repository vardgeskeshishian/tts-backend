<?php

namespace App\Repositories;

use App\Models\Structure\Meta;
use App\Services\MetaService;

class MetaRepository extends BasicRepository
{
    /**
     * @var MetaService
     */
    private $metaService;

    protected $modelName = Meta::class;

    public function __construct(MetaService $metaService)
    {
        parent::__construct();
        $this->metaService = $metaService;
    }

    public function getForEntity(string $className, string $id)
    {
        $className = get_class_name($className);
        $className = $this->metaService->morphTypeKey($className);

        $metaData = parent::getWhere([
            'type' => $className,
            'type_id' => $id,
        ]);

        $result = [];

        foreach ($metaData as $datum) {
            $result[$datum['slug']] = $datum['value'];
        }

        return $result;
    }
}
