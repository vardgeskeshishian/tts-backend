<?php

namespace App\Orchid\Screens\SFX;

use App\Events\AttachTagEvent;
use App\Jobs\Mixify;
use App\Models\Orchid\Attachment;
use App\Models\SFX\SFXCategory;
use App\Models\SFX\SFXTag;
use App\Models\SFX\SFXTrack;
use App\Orchid\Layouts\SFX\SFXCheckboxLayout;
use App\Orchid\Layouts\SFX\SFXFileLayout;
use App\Orchid\Listeners\SFX\SFXTrackEditListener;
use App\Orchid\Layouts\SFX\SFXTagLayout;
use App\Jobs\RunSFXWaveformGeneratorJobs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use App\Jobs\UpdateSfxCoefficientJobs;

class SFXEditScreen extends Screen
{
    public $sfx;

    /**
     * @param SFXTrack $sfx
     * @return SFXTrack[]
     */
    public function query(SFXTrack $sfx): array
    {
        return [
            'sfx' => $sfx
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Edit SFX Track';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Change associated with the sfx track.';
    }

    /**
     * @return iterable|null
     */
    public function permission(): ?iterable
    {
        return [
            'platform.systems.users',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make(__('Remove'))
                ->icon('bs.trash3')
                ->novalidate()
                ->method('remove'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            SFXTrackEditListener::class,
            Layout::block([
                SFXFileLayout::class
            ]),
            Layout::block([
                SFXTagLayout::class
            ]),
            Layout::block([
                SFXCheckboxLayout::class
            ]),
        ];
    }

    /**
     * @param Request $request
     * @param SFXTrack $sfx
     * @return RedirectResponse
     */
    public function save(Request $request, SFXTrack $sfx): RedirectResponse
    {
        $data = $request->toArray();
        $data['sfx']['slug'] = Str::slug($request->input('sfx.slug'));
        $request->replace($data);

        try {
            $request->validate([
                'sfx.slug' => [
                    'required',
                    Rule::unique(SFXTrack::class, 'slug')->ignore($sfx),
                ]
            ]);
        } catch (ValidationException $e) {//ToDo redirect()->back()->withInput(); ?
            Alert::error($e->getMessage());
            if ($sfx->exists)
                return redirect()->route('platform.systems.sfx.edit', $sfx->id);
            else
                return redirect()->route('platform.systems.sfx.create');
        }

        $data = $request->get('sfx');
        $file = $request->input('sfx.attachment_id');
        $data['attachment_id'] = is_array($file) ? $file[0] : null;
        $attachment = Attachment::where('id', $data['attachment_id'])->first();
        if ($attachment)
            $data['link'] = '/storage/'.$attachment->path.
                $attachment->name.'.'.$attachment->extension;

        $sfx->fill($data)->save();

        AttachTagEvent::dispatch(SFXCategory::class, $data['sfxCategories'] ?? [], $sfx->id, SFXTrack::class);
        AttachTagEvent::dispatch(SFXTag::class, $data['sfxTags'] ?? [], $sfx->id, SFXTrack::class);
        RunSFXWaveformGeneratorJobs::dispatch($sfx->id);
        Mixify::dispatch($sfx);

        UpdateSfxCoefficientJobs::dispatch($sfx->id);

        Toast::info(__('SFX Track was saved'));

        return redirect()->route('platform.systems.sfx');
    }

    /**
     * @param SFXTrack $sfx
     * @return RedirectResponse
     */
    public function remove(SFXTrack $sfx): RedirectResponse
    {
        $sfx->delete();

        Toast::info(__('SFX Track was removed'));

        return redirect()->route('platform.systems.sfx');
    }
}
