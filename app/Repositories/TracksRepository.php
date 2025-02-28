<?php

namespace App\Repositories;

use App\Models\Track;
use Illuminate\Support\Str;

class TracksRepository extends BasicRepository
{
    protected $modelName = Track::class;

    protected $filterFields = [
        'author',
        'name',
        'description',
        'author_name',
        'created_at',
    ];

    public function getAuthorFilter($value)
    {
        return $this->model->whereHas('author', function ($query) use ($value) {
            return $query->where('name', $value)->orWhere('slug', Str::slug($value));
        });
    }

    public function getAuthorNameFilter($value)
    {
        return $this->getAuthorFilter($value);
    }

    public function getNameFilter($value)
    {
        return $this->model->where('name', 'LIKE', "%{$value}%");
    }
}
