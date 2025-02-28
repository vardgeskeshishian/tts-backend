<?php


namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\Structure\FAQ;
use App\Models\Structure\FAQSection;
use App\Services\FaqSectionService;
use App\Services\FaqService;
use Illuminate\Http\JsonResponse;

class FaqsController extends ApiController
{
    protected $faqService;
    protected $faqSectionService;

    public function __construct()
    {
        $this->faqService = resolve(FaqService::class);
        $this->faqSectionService = resolve(FaqSectionService::class);
    }

    public function create(): JsonResponse
    {
        return $this->wrapCall($this->faqService, 'create', request());
    }

    public function update(FAQ $faq): JsonResponse
    {
        return $this->wrapCall($this->faqService, 'update', request(), $faq);
    }

    public function delete(FAQ $faq): JsonResponse
    {
        $this->wrapCall($this->faqService, 'delete', $faq);

        return $this->success(FAQ::all());
    }

    public function get(): JsonResponse
    {
        return $this->success(FAQ::all());
    }

    public function find(FAQ $faq): JsonResponse
    {
        return $this->success($faq);
    }

    public function createSection(): JsonResponse
    {
        return $this->wrapCall($this->faqSectionService, 'create', request());
    }

    public function updateSection(FAQSection $faqSection): JsonResponse
    {
        return $this->wrapCall($this->faqSectionService, 'update', request(), $faqSection);
    }

    public function deleteSection(FAQSection $faqSection): JsonResponse
    {
        $this->wrapCall($this->faqSectionService, 'delete', $faqSection);

        return $this->success(FAQSection::all());
    }

    public function getSections(): JsonResponse
    {
        return $this->success(FAQSection::all());
    }

    public function findSection(FAQSection $faqSection): JsonResponse
    {
        return $this->success($faqSection);
    }
}
