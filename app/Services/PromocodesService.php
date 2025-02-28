<?php

namespace App\Services;

use App\Models\Promocode;

class PromocodesService extends AbstractModelService
{
    protected $modelClass = Promocode::class;
    protected $validationRules = [
        'code' => 'required',
        'discount' => 'required',
    ];

    /**
     * @param Promocode $promocode
     * @param $builtData
     *
     * @return Promocode
     */
    protected function fillInModel($promocode, $builtData)
    {
        [$data, $meta, $images, $taggable] = $builtData;

        if (isset($data['uses_allowed'])) {
            $data['uses_left'] = $data['uses_allowed'];
        }

        $promocode->fill($data);
        $promocode->save();

        return $promocode;
    }
}
