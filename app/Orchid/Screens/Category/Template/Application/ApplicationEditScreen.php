<?php

namespace App\Orchid\Screens\Category\Template\Application;

use App\Models\Images;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Orchid\Layouts\Category\CategoryPriorityLayout;
use App\Orchid\Listeners\Category\CategoryEditListener;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Screen;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\RedirectResponse;
use App\Orchid\Layouts\Category\CategoryH1Layout;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Orchid\Layouts\Category\CategoryNameLayout;
use App\Orchid\Layouts\Category\CategoryImageLayout;
use App\Orchid\Layouts\Category\CategoryGoogleUrlLayout;
use App\Orchid\Layouts\Category\CategoryMetaTitleLayout;
use App\Orchid\Layouts\Category\CategoryDescriptionLayout;
use App\Orchid\Layouts\Category\CategoryMetaDescriptionLayout;
use App\Orchid\Layouts\Category\CategoryIsBlackLayout;
use App\Orchid\Layouts\Category\Template\Application\ApplicationForegroundImageLayout;

class ApplicationEditScreen extends Screen
{
    public $tag;

    public bool $exists = false;

    /**
     * @param VideoEffectApplication $application
     * @return VideoEffectApplication[]
     */
    public function query(VideoEffectApplication $application): array
    {
        $this->exists = $application->exists;
        $application->load('icon', 'foreground');
        return [
            'tag' => $application,
            'foreground' => $application->foreground->first(),
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->tag->exists ? 'Edit Application' : 'Create Application';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Application profile';
    }

    public function commandBar(): array
    {
        return [
            Button::make(__('Remove'))
                ->icon('bs.trash3')
                ->method('remove')
                ->novalidate()
                ->canSee($this->tag->exists),

            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save')
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::block(
                new CategoryEditListener($this->exists)
            )->title('Name'),

            Layout::block([
                CategoryH1Layout::class
            ])->title('H1'),

            Layout::block([
                CategoryDescriptionLayout::class
            ])->title('Description'),

            Layout::block([
                CategoryMetaTitleLayout::class
            ])->title('Meta-title'),

            Layout::block([
                CategoryMetaDescriptionLayout::class
            ])->title('Meta-description'),

            Layout::block([
                CategoryGoogleUrlLayout::class
            ])->title('Google Bot Redirect URL'),

            Layout::block([
                CategoryImageLayout::class
            ])->title('Image'),

            Layout::block([
                ApplicationForegroundImageLayout::class
            ])->title('Foreground Image'),

            Layout::block([
                CategoryIsBlackLayout::class
            ])->title('Black text'),

            Layout::block([
                CategoryPriorityLayout::class
            ])->title('Sorting Priority'),
        ];
    }

    /**
     * @param Request $request
     * @param VideoEffectApplication $application
     * @return RedirectResponse
     */
    public function save(Request $request, VideoEffectApplication $application): RedirectResponse
    {
        try {
            $categories = VideoEffectCategory::pluck('slug')->toArray();
            $applications = VideoEffectApplication::where('slug', '!=', $application->slug)
                ->pluck('slug')->toArray();

            $slugs = array_merge($categories, $applications);

            $request->validate([
                'tag.slug' => [
                    'required',
                    Rule::notIn($slugs),
                ],
            ]);
        } catch (ValidationException $e) {
            Alert::error($e->getMessage());
            return redirect()->back()->withInput();
        }

        $data = $request->input('tag');

        $description = $data['description'];
        $newDescription = '';
        $rows = explode("\r\n", $description);
        foreach ($rows as $row)
        {
            $newDescription .= Str::ucfirst(Str::lower($row)).
                ($row != end($rows) ? "\r\n" : "");
        }
        $data['description'] = $newDescription;

        $application->fill($data)->save();

        $icon = $application->icon;
        $background = $data['icon']['url'];
        if ($background) {
            if ($icon) {
                $icon->update([
                    'url' => $background
                ]);
            } else {
                Images::create([
                    'url' => $background,
                    'type' => VideoEffectApplication::class,
                    'type_id' => $application->id,
                    'type_key' => 'icon'
                ]);
            }
        } else {
            $icon?->delete();
        }

        $foreground = $request->input('foreground');
        $foreground = $foreground['url'];
        $foregroundImage = $application->foreground->first();

        if ($foreground) {
            if ($foregroundImage) {
                $foregroundImage->update([
                    'url' => $foreground
                ]);
            } else {
                Images::create([
                    'url' => $foreground,
                    'type' => VideoEffectApplication::class,
                    'type_id' => $application->id,
                    'type_key' => 'foreground'
                ]);
            }
        } else {
            $foregroundImage?->delete();
        }


        Toast::info(__('Application was saved'));

        return redirect()->route('platform.systems.category.template.application');
    }

    /**
     * @param VideoEffectApplication $application
     * @return RedirectResponse
     */
    public function remove(VideoEffectApplication $application): RedirectResponse
    {
        $application->delete();

        Toast::info(__('Application was removed'));

        return redirect()->route('platform.systems.category.template.application');
    }
}
