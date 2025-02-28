<?php

namespace App\Http\Livewire;

use App\Models\Structure\Core;
use Illuminate\Support\Collection;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CoreIndex extends Component
{
    use LivewireAlert;

    public Collection $cores;
    public ?Core $coreToDelete = null;

    public function mount(Collection $cores)
    {
        $this->cores = $cores;
    }

    public function render()
    {
        $this->cores = Core::all();
        return view('livewire.core-index');
    }

    public function beforeDelete(Core $core)
    {
        $this->coreToDelete = $core;

        $this->alert('question', 'Are you sure?', [
            'text' => "You are about to delete $core->name. This action is irreversible!",
            'showConfirmButton' => true,
            'confirmButtonText' => 'Yes, I am sure',
            'onConfirmed' => 'onDeleteCoreConfirm',
            'onDismissed' => 'clear'
        ]);
    }

    protected $listeners = [
        'onDeleteCoreConfirm',
        'cancelled',
    ];

    public function onDeleteCoreConfirm()
    {
        $this->coreToDelete->delete();

        $this->coreToDelete = null;

        $this->showSuccess();
    }

    public function cancelled()
    {
        $this->coreToDelete = null;
    }

    private function showSuccess()
    {
        $this->alert('success', 'All good!');
    }
}
