<?php
/**
 * Created by PhpStorm.
 * User: notant
 * Date: 2019-03-16
 * Time: 22:57
 */

namespace App\Http\Resources\Authorized\Collection;

use Illuminate\Http\Resources\Json\JsonResource;

class LicenseStandardResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->type,
            'description' => $this->description,
            'price' => $this->info->price,
            'list_1' => $this->list_1,
            'list_2' => $this->list_2
        ];
    }
}
