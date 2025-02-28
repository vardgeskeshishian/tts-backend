<?php

namespace App\Services;

use App\Models\Structure\FAQSection;
use App\Repositories\LicensesRepository;
use App\Traits\CanStore;

class FaqSectionService extends AbstractModelService
{
    use CanStore;

    protected $modelClass = FAQSection::class;

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
