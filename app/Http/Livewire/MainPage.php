<?php

namespace App\Http\Livewire;

use App\Constants\MainPageConstants;
use App\Services\MainPageService;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class MainPage extends Component
{
    use LivewireAlert;

    public $type;
    public $inputs = [];
    public $menu = [];
    public string|null $sectionToDelete;
    public string|null $itemToDelete;
    /**
     * @var MainPageService
     */
    private mixed $mainPageService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->mainPageService = resolve(MainPageService::class);
    }

    public function render()
    {
        $this->menu = [
            MainPageConstants::TYPE_ROOT => [
                'url' => '',
                'active' => $this->type === MainPageConstants::TYPE_ROOT,
            ],
            MainPageConstants::TYPE_VFX => [
                'url' => '/' . MainPageConstants::TYPE_VFX,
                'active' => $this->type === MainPageConstants::TYPE_VFX,
            ],
            MainPageConstants::TYPE_SFX => [
                'url' => '/' . MainPageConstants::TYPE_SFX,
                'active' => $this->type === MainPageConstants::TYPE_SFX,
            ],
        ];

        if (empty($this->inputs)) {
            $this->reloadInputs();
        }

        unset($this->inputs['testimonials']);

        return view('livewire.main-page');
    }

    public function switchType($type)
    {
        $this->type = $type;
        $this->reloadInputs();
    }

    public function addItem($section)
    {
        $newSectionOrder = count($this->inputs[$section]) + 1;

        $this->inputs[$section]["new_section_$newSectionOrder"] = [
            'key' => "new_section_$newSectionOrder",
            'value' => 'write a description here',
            'editable_key' => true,
        ];
    }

    public function addSection()
    {
        $lenOfSections = count($this->inputs) + 1;
        $section = "section_$lenOfSections";
        while (isset($this->inputs[$section])) {
            $lenOfSections++;
            $section = "section_$lenOfSections";
        }
        $this->inputs[$section] = [];
    }

    public function save()
    {
        foreach ($this->inputs as $sectionId => $sectionData) {
            foreach ($sectionData as $type => $data) {
                if ($data['key'] !== $type) {
                    $this->inputs[$sectionId][$data['key']] = $data;
                    unset($this->inputs[$sectionId][$type]);
                }
            }
        }

        $this->mainPageService->saveSystemMainPage($this->inputs, $this->type);
        $this->reloadInputs();
    }

    public function showDeleteSectionAlert($sectionName)
    {
        $this->sectionToDelete = $sectionName;

        $this->alert('question', 'Are you sure?', [
            'text' => "You are about to delete {$sectionName}. This action is irreversible and can crash the site!",
            'showConfirmButton' => true,
            'confirmButtonText' => 'Yes, I am sure',
            'onConfirmed' => 'onDeleteSectionConfirm',
            'onDismissed' => 'clear'
        ]);
    }

    protected $listeners = [
        'onDeleteSectionConfirm',
        'onDeleteItemConfirm',
        'cancelled',
    ];

    public function onDeleteSectionConfirm()
    {
        $this->mainPageService->deleteSection($this->sectionToDelete, $this->type);

        $this->reloadInputs();
        $this->clear();

        $this->showSuccess();
    }

    public function showDeleteItemConfirm($sectionName, $itemKey)
    {
        $this->sectionToDelete = $sectionName;
        $this->itemToDelete = $itemKey;

        $this->alert('question', 'Are you sure?', [
            'text' => "You are about to delete {$itemKey} from {$sectionName}. This action is irreversible!",
            'showConfirmButton' => true,
            'confirmButtonText' => 'Yes, I am sure',
            'onConfirmed' => 'onDeleteItemConfirm',
            'onDismissed' => 'clear'
        ]);
    }

    public function onDeleteItemConfirm()
    {
        $this->mainPageService->deleteItem($this->sectionToDelete, $this->itemToDelete, $this->type);

        $this->reloadInputs();
        $this->clear();

        $this->showSuccess();
    }

    public function clear()
    {
        $this->sectionToDelete = null;
        $this->itemToDelete = null;
    }

    private function reloadInputs()
    {
        $this->inputs = $this->mainPageService->getMainPageOfTypeForSystem($this->type);
    }

    private function showSuccess()
    {
        $this->alert('success', 'All good!');
    }
}
