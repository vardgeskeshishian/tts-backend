<?php

namespace App\Services;

use App\Models\Structure\Meta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MetaService
{
    public function fillInForObject(Model $model, array $metaData, $slug = null)
    {
        $typeKey = get_class_name($model->getMorphClass());
        $typeKey = $this->morphTypeKey($typeKey);
        $typeId = $model->id;

        $metas = [];

        foreach ($metaData as $metaKey => $metaValue) {
            $slug = Str::contains($metaKey, '_') ? $metaKey : Str::slug($metaKey);

            $metas[$slug] = Meta::updateOrCreate([
                'type' => $typeKey,
                'type_id' => $typeId,
                'slug' => $slug,
            ], [
                'value' => $metaValue,
            ]);
        }

        return $metas;
    }

    public function morphTypeKey(string $typeKey)
    {
        if (strpos('Meta-', $typeKey) > -1) {
            return $typeKey;
        }

        return 'Meta-' . ucfirst($typeKey);
    }
}
