<?php

namespace App\Http\Livewire;

use App\Constants\VideoEffects;
use App\Models\Mail;
use App\Models\VideoEffects\VideoEffect;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Livewire\Component;

class VideoEffectChangeStatus extends Component
{
    use DispatchesJobs;

    public VideoEffect $videoEffect;
    /**
     * @var string[]
     */
    public array $statuses;
    public int $statusId;

    protected $rules = [
        'statusId' => 'required',
    ];

    public function mount(VideoEffect $videoEffect)
    {
        $this->videoEffect = $videoEffect;
        $this->statuses = VideoEffects::STATUSES;
        $this->statusId = $videoEffect->getRawOriginal('status');
    }

    public function render()
    {
        return view('livewire.video-effect-change-status');
    }

    public function changeStatus()
    {
        $originalStatus = $this->videoEffect->getRawOriginal('status');
        $newStatus = $this->statusId;
        
        if ($originalStatus === $newStatus) {
            return;
        }

        $emailData = [];

        $latestComment = optional($this->videoEffect->publicComments()->latest()->first())->comment;
        $mailAddons = $latestComment ? ['comment' => $latestComment] : [];

        switch ($newStatus) {
            case VideoEffects::STATUS_SOFT_REJECT:
                $emailData = [
                    'title' => "Regarding your video-effect '{$this->videoEffect->name}'",
                    'body' => "Your video-effect '{$this->videoEffect->name}' still needs to be improved",
                    'addons' => $mailAddons,
                ];

                break;
            case VideoEffects::STATUS_HARD_REJECT:
                $emailData = [
                    'title' => "Regarding your video-effect '{$this->videoEffect->name}'",
                    'body' => "Unfortunately, your video-effect '{$this->videoEffect->name}' is not approved for the TakeTones library.",
                    'addons' => $mailAddons,
                ];

                break;
            case VideoEffects::STATUS_APPROVED:
                $emailData = [
                    'title' => "Regarding your video-effect '{$this->videoEffect->name}'",
                    'body' => "Congratulations! your video-effect '{$this->videoEffect->name}' was successfully verified. Now you need to send us all versions of your video-effect",
                    'addons' => [
                    ]
                ];

                break;
            default:
                break;
        }

        if (!empty($emailData)) {
            $this->dispatch(function () use ($emailData) {
                Mail::send('email.general-email', $emailData, function ($message) use ($emailData) {
                    $author = $this->videoEffect->author;
                    $message->from('no-reply@taketones.com')
                        ->to($author->getAuthorEmail())
                        ->subject($emailData['title']);
                });
            });
        }

        $this->videoEffect->status = $this->statusId;
        $this->videoEffect->save();
    }
}
