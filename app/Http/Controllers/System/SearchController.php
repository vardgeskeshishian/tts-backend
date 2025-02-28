<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Api\ApiController;
use App\Models\BlogCategory;
use App\Models\Tags\Tag;

class SearchController extends ApiController
{
    protected $types = [
        'blog-categories' => BlogCategory::class,
        'tags' => Tag::class
    ];

    public function search($type)
    {
        $q = request()->get('q')['term'];

        $m = resolve($this->types[$type]);
        $res = $m::where('slug', 'LIKE', "%{$q}%")->get();

        $tags = [];

        foreach ($res as $id => $value) {
            $tags[ $id ][ 'id' ]   = $value[ 'slug' ];
            $tags[ $id ][ 'text' ] = $value[ 'name' ];
        }

        return $tags;
    }
}
