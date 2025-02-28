<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FavoritesFolderContentResourceCollection extends ResourceCollection
{
    protected string $type;

    public function type($value): static
    {
        $this->type = $value;
        return $this;
    }

    public function toArray(Request $request){
        return $this->collection->map(function(FavoritesFolderContentResource $resource) use($request){
            return $resource->type($this->type)->toArray($request);
        })->all();
    }
}