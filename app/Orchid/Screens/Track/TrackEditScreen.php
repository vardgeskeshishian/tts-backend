<?php

namespace App\Orchid\Screens\Track;

use App\Events\AttachTagEvent;
use App\Jobs\RunAudioWaveformGeneratorJobs;
use App\Jobs\UpdateTrackCoefficientJobs;
use App\Jobs\Mixify;
use App\Models\Tags\Genre;
use App\Models\Tags\Instrument;
use App\Models\Tags\Mood;
use App\Models\Tags\Tag;
use App\Models\Tags\Type;
use App\Models\Track;
use App\Models\Orchid\Attachment;
use App\Models\TrackArchive;
use App\Models\TrackAudio;
use App\Models\Images;
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
use App\Orchid\Layouts\Tracks\TrackFileLayout;
use App\Orchid\Layouts\Tracks\TrackChexboxLayout;
use App\Orchid\Layouts\Tracks\TrackAuthorLayout;
use App\Orchid\Layouts\Tracks\TrackTempoLayout;
use App\Orchid\Layouts\Tracks\TrackMetaLayout;
use App\Orchid\Layouts\Tracks\TrackTagLayout;
use App\Orchid\Layouts\Tracks\TrackImagesLayout;
use App\Orchid\Layouts\Tracks\TrackLinkArchiveLayout;
use App\Orchid\Listeners\Track\TrackEditListener;
use Orchid\Support\Facades\Toast;
use Spatie\ResponseCache\Facades\ResponseCache;

class TrackEditScreen extends Screen
{
    public $track;

    /**
     * Fetch data to be displayed on the screen.
     *
     *
     * @return array
     */
    public function query(Track $track): iterable
    {
        $track->load(['audio', 'author', 'prices', 'audio.attachment', 'background', 'thumbnail', 'archive']);
        return [
            'track' => $track,
            'background' => $track->background,
            'loss' => $track->audio()->where('type','=', 'mp3')
                ->where('is_hq', '=', 0)->first(),
            'hq' => $track->audio()->where('type', '=', 'mp3')
                ->where('is_hq', '=', 1)->first(),
            'wav' => $track->audio()->where('type', '=', 'wav')->first(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Edit Track';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Change associated with the track.';
    }

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
            Layout::block([
                TrackEditListener::class,
            ])->title('Track'),

            Layout::block([
                TrackAuthorLayout::class
            ])->title('Author'),

            Layout::block([
                TrackTempoLayout::class
            ])->title('Tempo'),

            Layout::block([
                TrackTagLayout::class
            ])->title('Tags'),

            Layout::block([
                TrackMetaLayout::class
            ])->title('Meta'),

            Layout::block([
                TrackImagesLayout::class
            ])->title('Picture'),

            Layout::block([
                TrackFileLayout::class
            ])->title('File'),

            Layout::block([
                TrackChexboxLayout::class
            ])->title('_'),
        ];
    }

    /**
     * @param Request $request
     * @param Track $track
     * @return RedirectResponse
     */
    public function save(Request $request, Track $track): RedirectResponse
    {
        $data = $request->toArray();
        $data['track']['slug'] = Str::slug($request->input('track.slug'));
        $request->replace($data);

        try {
            $request->validate([
                'track.slug' => [
                    'required',
                    Rule::unique(Track::class, 'slug')->ignore($track),
                ],
                'loss' => [
                    'required'
                ]
            ],[
                'loss.required' => 'The Loss MP3 is required.',
            ]);
        } catch (ValidationException $e) {
            Alert::error($e->getMessage());
            return redirect()->back()->withInput();
        }

        $requestArray = $request->get('track');
        $track->fill($request->get('track'))->save();

        $this->saveTrackAudio($request, $track);

        AttachTagEvent::dispatch(Tag::class, $requestArray['tags'] ?? [], $track->id, Track::class);
        AttachTagEvent::dispatch(Genre::class, $requestArray['genres'] ?? [], $track->id, Track::class);
        AttachTagEvent::dispatch(Mood::class, $requestArray['moods'] ?? [], $track->id, Track::class);
        AttachTagEvent::dispatch(Instrument::class, $requestArray['instruments'] ?? [], $track->id, Track::class);
        AttachTagEvent::dispatch(Type::class, $requestArray['types'] ?? [], $track->id, Track::class);
        Mixify::dispatch($track);

        if ($request->has('background'))
            Images::updateOrCreate([
                'type' => Track::class,
                'type_id' => $track->id,
                'type_key' => 'background'
            ], [
                'url' => $request->get('background')['url'],
            ]);

        if (array_key_exists('archive', $requestArray))
        {
            $attachment = Attachment::where('id', $requestArray['archive']['attachment_id'])->first();

            TrackArchive::updateOrCreate([
                'track_id' => $track->id,
            ], [
                'path' => '/'.$attachment->path.
                    $attachment->name.'.'.$attachment->extension,
                'attachment_id' => $attachment->id
            ]);
        }

        UpdateTrackCoefficientJobs::dispatch($track->id);

        Toast::info(__('Track was saved'));
		ResponseCache::clear();
        return redirect()->route('platform.systems.tracks');
    }

    /**
     * @param Track $track
     * @return RedirectResponse
     */
    public function remove(Track $track): RedirectResponse
    {
        $track->delete();

        Toast::info(__('Track was removed'));

        return redirect()->route('platform.systems.tracks');
    }

    /**
     * @param Request $request
     * @param Track $track
     * @return void
     */
    private function saveTrackAudio(Request $request, Track $track): void
    {
        $lossId = $request->input('loss.attachment_id')[0] ?? null;
        $hqId = $request->input('hq.attachment_id')[0] ?? null;
        $wavId = $request->input('wav.attachment_id')[0] ?? null;

        $track->audio()->delete();
        $attachments = Attachment::whereIn('id', [$lossId, $hqId, $wavId])->get();

        foreach ($attachments as $attachment)
        {
            $trackAudio = TrackAudio::create([
                'track_id' => $track->id,
                'type' => $attachment->extension,
                'url' => '/storage/'.$attachment->path.
                    $attachment->name.'.'.$attachment->extension,
                'attachment_id' => $attachment->id,
                'is_hq' => $attachment->id == $hqId,
            ]);

            RunAudioWaveformGeneratorJobs::dispatch($trackAudio->id);
        }
    }
}
