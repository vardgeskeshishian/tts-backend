<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\VideoEffects\VideoEffect;
use App\Services\VideoEffectsService;
use Illuminate\Support\Collection;
use Livewire\Component;

class VideoEffectCommentBlock extends Component
{
    public VideoEffect $videoEffect;
    public Collection $comments;
    public bool $private;
    public $admins = [];

    public $reviewer_id;
    public $comment;

    protected $rules = [
        'comments.*.comment'
    ];

    public function mount(VideoEffect $videoEffect, bool $private = false)
    {
        $this->videoEffect = $videoEffect;
        $this->private = $private;
        $this->reloadComments();

        $this->admins = User::where('role', 'admin')->get();
        $this->reviewer_id = $this->admins->first()->id;
    }

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function render()
    {
        $this->reloadComments();
        return view('livewire.video-effect-comment-block');
    }

    public function addComment(VideoEffectsService $videoEffectsService)
    {
        $videoEffectsService->addComment($this->videoEffect, $this->reviewer_id, $this->private, $this->comment);
        $this->comment = '';
        $this->emit('refreshComponent');
    }

    private function reloadComments()
    {
        $this->comments = $this->private ? $this->videoEffect->privateComments : $this->videoEffect->publicComments;
    }
}
