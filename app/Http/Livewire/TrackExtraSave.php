<?php

namespace App\Http\Livewire;

use App\Constants\SubmissionsEnv;
use App\Models\Authors\AuthorSubmission;
use App\Models\Track;
use Livewire\Component;

class TrackExtraSave extends Component
{
    public Track $track;
    public $coop_type = null;
    public ?AuthorSubmission $submission = null;

    protected $rules = [
        'track.has_content_id' => 'sometimes|boolean',
        'track.premium' => 'sometimes|boolean',
    ];

    protected $listeners = [
        'submission_idUpdated' => 'fillAuthorSubmissionTrackId',
        'resetSubmission' => 'resetSubmission',
    ];

    public function save()
    {
        $this->validate();

        $this->track->exclusive = $this->exclusive;
        $this->track->save();

        if (!$this->submission) {
            return;
        }

        $this->track->has_content_id = $this->submission->has_content_id;
        $this->track->save();

        $this->submission->track_id = $this->track->id;
        $this->submission->final_status = SubmissionsEnv::STATUS_PUBLISHED;
        $this->submission->save();
    }

    public function fillAuthorSubmissionTrackId($params): void
    {
        $this->submission = AuthorSubmission::find($params['value']);
    }

    public function resetSubmission(): void
    {
        $this->track->submission()->update([
            'track_id' => null,
            'final_status' => SubmissionsEnv::STATUS_DELIVERY_C,
        ]);
    }

    public function render()
    {
        return view('livewire.track-extra-save');
    }

    public function setCoopType($exclusivity = null)
    {
        $this->track->exclusive = $exclusivity;

        $this->exclusive = $exclusivity;
    }

}
