<?php

namespace App\Services;

use App\Http\Resources\Api\AuthorResource;
use App\Models\Authors\AuthorProfile;
use App\Models\SystemAuthor;
use Exception;
use Illuminate\Support\Str;
use Spatie\ResponseCache\Facades\ResponseCache;

class AuthorsService extends AbstractModelService
{
    protected $modelClass = AuthorProfile::class;
    protected $validationRules = [
        'name' => 'required|unique:authors,name'
    ];

    /**
     * @param SystemAuthor $author
     * @param $builtData
     *
     * @return AuthorResource
     * @throws Exception
     */
    protected function fillInModel($author, $builtData)
    {
        [$data, $meta, $images] = $builtData;

        $data['slug'] = $author->slug ?? Str::slug($data['name']);

        $author->fill($data);
        $author->save();

        $this->metaService->fillInForObject($author, $meta);
        $this->imagesService->upload($author, $images);

        ResponseCache::clear();

        return new AuthorResource($author);
    }
}
