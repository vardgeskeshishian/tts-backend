<?php

namespace App\Http\Livewire;

use App\Models\Structure\Core;
use App\Services\CoreService;
use App\Services\QuestionsService;
use Livewire\Component;

class CoreDetailed extends Component
{
    public Core $core;
    public bool $dataNameLockedFields = true;
    public array $meta;
    public array $branding;
    public array $faq;
    public array $removedBlocks = [];
    public $tabs;
    private QuestionsService $questionsService;
    private CoreService $coreService;
    public array $dataName;

    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->coreService = resolve(CoreService::class);
        $this->questionsService = resolve(QuestionsService::class);
        $this->removedBlocks = [];
    }

    public function mount(Core $core, array $meta, array $branding, array $faq)
    {
        $this->tabs = [
            'meta' => true,
            'branding' => false,
            'faq' => false,
        ];

        $this->core = $core;
        $this->dataName = $core->getCoreDataName();
        $this->dataNameLockedFields = $core->id > 0;
        $this->meta = $meta;
        $this->branding = $branding;
        $this->faq = $faq;
    }

    public function render()
    {
        return view('livewire.core-detailed');
    }

    public function addFaqSection()
    {
        $this->faq[] = [
            'show_editing_border' => true,
            'section_id' => uniqid(),
            'section_name' => '',
            'section_blocks' => [
                $this->initEmptyFaqBlock(),
            ]
        ];
    }

    public function addFaqBlock($index)
    {
        $this->faq[$index]['section_blocks'][] = $this->initEmptyFaqBlock();
    }

    public function rememberSelectedTab($tabName)
    {
        foreach ($this->tabs as $index => $value) {
            $this->tabs[$index] = false;
        }
        $this->tabs[$tabName] = true;
    }

    private function initEmptyFaqBlock()
    {
        return [
            'block_id' => uniqid(),
            'block_name' => '',
            'block_question' => '',
            'block_answer' => '',
        ];
    }

    public function removeFaqBlock($sectionIndex, $blockIndex)
    {
        $this->removedBlocks[] = $this->faq[$sectionIndex]['section_blocks'][$blockIndex];

        unset($this->faq[$sectionIndex]['section_blocks'][$blockIndex]);
    }

    public function save()
    {
        $this->saveMeta();
        $this->saveFaq();
    }

    private function saveMeta()
    {
        if ($this->core->id === null) {
            $this->core->fill($this->dataName);
            $this->core->name = implode(":", $this->core->getCoreDataName());
        }

        $this->coreService->saveFromArray($this->core, $this->meta, $this->branding);
    }

    private function saveFaq()
    {
        $this->questionsService->saveFromArray($this->core, $this->faq, $this->removedBlocks);
        $this->faq = $this->questionsService->getForModelAsArray($this->core);
    }
}
