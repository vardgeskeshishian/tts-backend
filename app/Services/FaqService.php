<?php

namespace App\Services;

use App\Models\Structure\FAQ;
use App\Repositories\LicensesRepository;
use App\Traits\CanStore;

class FaqService extends AbstractModelService
{
    use CanStore;

    protected $validationRules = [];

    protected $modelClass = FAQ::class;

    /**
     * @var LicensesRepository
     */
    private $licensesRepository;
    /**
     * @var OneTimeLinkService
     */
    private $oneTimeLinkService;

    public function __construct(
        ImagesService $imagesService,
        MetaService $metaService,
        TaggingService $taggingService
    ) {
        parent::__construct($imagesService, $metaService, $taggingService);
    }
}
