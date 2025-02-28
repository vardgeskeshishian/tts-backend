<?php

namespace App\Http\Livewire;

use App\Constants\SubmissionsEnv;
use App\Models\Authors\AuthorSubmission;
use Asantibanez\LivewireSelect\LivewireSelect;
use Illuminate\Support\Collection;

class TrackExtraSubmissionId extends LivewireSelect
{
    public function options($searchTerm = null): Collection
    {
        $submissions = AuthorSubmission::where('final_status', SubmissionsEnv::STATUS_DELIVERY_C)
            ->when($searchTerm, function ($query) use ($searchTerm) {
                $query->where('track_name', 'like', "%$searchTerm%");
            })
            ->get()->transform(function ($item) {
                return [
                    'value' => $item,
                    'description' => "{$item->id} - {$item->track_name}"
                ];
            });

        return collect($submissions);
    }

    public function selectedOption($value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, false, 512, JSON_THROW_ON_ERROR);
        }

        return ['value' => $value->id, 'description' => "{$value->id} - {$value->track_name}"];
    }

    public function getListeners()
    {
        $listeners = parent::getListeners();

        $listeners['resetSubmissionId'] = 'resetSubmissionId';

        return $listeners;
    }

    public function resetSubmissionId(): void
    {
        $this->emitTo('track-extra-save', 'resetSubmission');

        $this->value = null;
    }
}
