<?php


namespace App\Services\Authors;

use App\Services\MetaService;
use App\Models\Authors\Author;
use App\Services\ImagesService;
use App\Models\Authors\AuthorProfile;

class AuthorProfileService
{
    /**
     * @var ImagesService
     */
    private ImagesService $imagesService;
    /**
     * @var MetaService
     */
    private MetaService $metaService;

    public function __construct(ImagesService $imagesService, MetaService $metaService)
    {
        $this->imagesService = $imagesService;
        $this->metaService = $metaService;
    }

    public function createNewProfile(Author $author, string $name = null, string $description = null, $isSystem = false)
    {
        /**
         * @var $profile AuthorProfile
         */
        $profile = AuthorProfile::make();

        $name = request()->input('name', $name);

        $profile->user_id = $author->id;
        $profile->name = $name;
        $profile->description = request()->input('description', $description ?? sprintf("Author %s profile, named as %s", $author->email, $name));
        $profile->save();

        if (request()->files->has('images')) {
            $this->imagesService->upload($profile, request()->files->get('images'));
        }

        if (request()->has('meta')) {
            $this->metaService->fillInForObject($profile, request()->input('meta'));
        }

        return $profile;
    }
}
