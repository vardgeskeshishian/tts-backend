<?php

namespace App\Orchid\Screens\VideoEffect;

use App\Constants\VideoEffects;
use App\Jobs\Mixify;
use App\Models\Orchid\Attachment;
use App\Models\VideoEffects\VideoEffect;
use App\Orchid\Layouts\VideoEffect\VideoEffectAuthorLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectMetaLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectApplicationsLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectPluginsLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectResolutionsLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectCategoriesLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectTagLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectVersionLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectPreviewPhotoLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectPreviewLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectPreviewVideoLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectZipFileLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectChexboxLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectDescriptionLayout;
use App\Orchid\Layouts\VideoEffect\VideoEffectAssociatedTrackLayout;
use App\Orchid\Listeners\VideoEffect\VideoEffectNameListener;
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
use Spatie\ResponseCache\Facades\ResponseCache;
use App\Jobs\UpdateVideoCoefficientJobs;

class VideoEffectEditScreen extends Screen
{
    public $video;

    /**
     * @param VideoEffect $video
     * @return iterable
     */
    public function query(VideoEffect $video): iterable
    {
        return [
            'video' => $video,
            'videoTags' => $video->tags->pluck('id')->toArray()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->video->exists ? 'Edit Video Effect' : 'Create Video Effect';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Change associated with the video.';
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

    public function layout(): iterable
    {
        return [
            Layout::block([
                VideoEffectNameListener::class,
            ])->title('Name'),

            Layout::block([
                VideoEffectDescriptionLayout::class
            ])->title('Description'),

            Layout::block([
                VideoEffectAuthorLayout::class
            ])->title('Author Video'),

            Layout::block([
                VideoEffectAssociatedTrackLayout::class
            ])->title('Associated Music'),
			
			Layout::block([
				VideoEffectMetaLayout::class
			])->title('Meta'),

            Layout::block([
                VideoEffectPreviewPhotoLayout::class
            ])->title('Preview Photo'),

            Layout::block([
                VideoEffectPreviewLayout::class
            ])->title('Preview Video')
                ->description('Maximum file size 100mb'),

            Layout::block([
                VideoEffectPreviewVideoLayout::class
            ])->title('Video')
                ->description('Maximum file size 100mb'),

            Layout::block([
                VideoEffectZipFileLayout::class
            ])->title('Zip'),

            Layout::block([
                VideoEffectApplicationsLayout::class
            ])->title('Application'),

            Layout::block([
                VideoEffectTagLayout::class
            ])->title('Tags'),

            Layout::block([
                VideoEffectPluginsLayout::class
            ])->title('Plugin'),

            Layout::block([
                VideoEffectResolutionsLayout::class
            ])->title('Resolutions'),

            Layout::block([
                VideoEffectCategoriesLayout::class
            ])->title('Categories'),

            Layout::block([
                VideoEffectVersionLayout::class
            ])->title('Version'),

            Layout::block([
                VideoEffectChexboxLayout::class
            ])->title('_')
        ];
    }

    public function save(Request $request, VideoEffect $video): RedirectResponse
    {
        $data = $request->toArray();
        $data['video']['slug'] = Str::slug($request->input('video.slug'));
        $request->replace($data);

        try {
            $request->validate([
				'video.meta_description' => [
					'nullable',
					'max:400',
				],
				'video.meta_title' => [
					'nullable',
					'max:255',
				],
                'video.slug' => [
                    'required',
                    Rule::unique(VideoEffect::class, 'slug')->ignore($video),
                ]
            ]);
        } catch (ValidationException $e) {//ToDo redirect()->back()->withInput(); ?
            Alert::error($e->getMessage());
            if ($video->exists)
                return redirect()->route('platform.systems.video.edit', $video->id);
            else
                return redirect()->route('platform.systems.video.create');
        }

        $previewVideoId = $request->input('video.preview_video_id');
        $previewId = $request->input('video.preview_id');
        $zipId = $request->input('video.zip_id');

        $previewVideoId = is_array($previewVideoId) ?
            $previewVideoId[0] : null;
        $zipId = is_array($zipId) ?
            $zipId[0] : null;

        $data = [
			'meta_title' => $request->input('video.meta_title'),
			'meta_description' => $request->input('video.meta_description'),
            'author_profile_id' => $request->input('video.author_profile_id'),
            'name' => $request->input('video.name'),
            'slug' => $request->input('video.slug'),
            'description' => $request->input('video.description'),
            'user_id' => auth()->user()->id,
            'status' => VideoEffects::STATUS_NEW,
            'application_id' => $request->input('video.application_id'),
            'version_id' => $request->input('video.version_id'),
            'preview_photo' => $request->input('video.preview_photo'),
            'preview_video_id' => $previewVideoId,
            'preview_id' => $previewId,
            'zip_id' => $zipId,
            'hidden' => $request->input('video.hidden'),
            'exclusive' => $request->input('video.exclusive'),
            'has_content_id' => $request->input('video.has_content_id'),
            'is_featured' => $request->input('video.is_featured'),
            'new' => $request->input('video.new'),
        ];

        $attachments = Attachment::whereIn('id', [$previewVideoId, $zipId, $previewId])->get();
        foreach ($attachments as $attachment)
        {
            if ($attachment->id == $previewVideoId)
                $data['preview_video'] = '/storage/'.$attachment->path.
                    $attachment->name.'.'.$attachment->extension;
            if ($attachment->id == $zipId)
                $data['zip'] = '/storage/'.$attachment->path.
                    $attachment->name.'.'.$attachment->extension;
            if ($attachment->id == $previewId)
                $data['preview'] = '/storage/'.$attachment->path.
                    $attachment->name.'.'.$attachment->extension;
        }
        $video->fill($data)->save();
        $video->plugins()->detach();
        $video->resolutions()->detach();
        $video->categories()->detach();
        $video->tags()->detach();

        $video->plugins()->attach($request->input('video.plugins'));
        $video->resolutions()->attach($request->input('video.resolutions'));
        $video->categories()->attach($request->input('video.categories'));
        $video->tags()->attach($request->input('videoTags'));

        Mixify::dispatch($video);
        UpdateVideoCoefficientJobs::dispatch($video->id);

        Toast::info(__('Video Effect was saved'));
		ResponseCache::clear();
        return redirect()->route('platform.systems.video');
    }

    /**
     * @param VideoEffect $video
     * @return RedirectResponse
     */
    public function remove(VideoEffect $video): RedirectResponse
    {
        $video->delete();

        Toast::info(__('Template was removed'));
		ResponseCache::clear();
        return redirect()->route('platform.systems.video');
    }
}
