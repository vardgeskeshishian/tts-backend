<?php

namespace App\Orchid\Screens\Category\Template\Tag;

use App\Models\Images;
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
use App\Models\VideoEffects\VideoEffectTag;
use App\Orchid\Layouts\Category\CategoryH1Layout;
use App\Orchid\Layouts\Category\CategoryNameLayout;
use App\Orchid\Layouts\Category\CategoryImageLayout;
use App\Orchid\Layouts\Category\CategoryGoogleUrlLayout;
use App\Orchid\Layouts\Category\CategoryMetaTitleLayout;
use App\Orchid\Layouts\Category\CategoryDescriptionLayout;
use App\Orchid\Layouts\Category\CategoryMetaDescriptionLayout;
use App\Orchid\Layouts\Category\CategoryIsBlackLayout;
use App\Orchid\Layouts\Category\Template\Tag\TagPositionLayout;

class TagEditScreen extends Screen
{
    public $tag;

    public bool $exists = false;

    /**
     * @param VideoEffectTag $tag
     * @return VideoEffectTag[]
     */
    public function query(VideoEffectTag $tag): array
    {
        $this->exists = $tag->exists;
        return [
            'tag' => $tag
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->tag->exists ? 'Edit Tag' : 'Create Tag';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Tag profile';
    }

    public function commandBar(): array
    {
        return [
            Button::make(__('Remove'))
                ->icon('bs.trash3')
                ->confirm(__('Are you sure you want to delete the tag?'))
                ->method('remove')
                ->novalidate()
                ->canSee($this->tag->exists),

            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save'),
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
                CategoryIsBlackLayout::class
            ])->title('Black text'),

            Layout::block([
                CategoryPriorityLayout::class
            ])->title('Sorting Priority'),
        ];
    }

    /**
     * @param Request $request
     * @param VideoEffectTag $tag
     * @return RedirectResponse
     */
    public function save(Request $request, VideoEffectTag $tag): RedirectResponse
    {
        try {
            $request->validate([
                'tag.slug' => [
                    'required',
                    Rule::unique(VideoEffectTag::class, 'slug')->ignore($tag),
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

        $tag->fill($data)->save();

        $icon = $tag->icon;
        $background = $data['icon']['url'];
        if ($background) {
            if ($icon) {
                $icon->update([
                    'url' => $background
                ]);
            } else {
                Images::create([
                    'url' => $background,
                    'type' => VideoEffectTag::class,
                    'type_id' => $tag->id,
                    'type_key' => 'icon'
                ]);
            }
        } else {
            $icon?->delete();
        }

        Toast::info(__('Tag was saved'));

        return redirect()->route('platform.systems.category.template.templateTag');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function remove(Request $request): RedirectResponse
    {
        $id = $request->route('application');
        $videoEffectTag = VideoEffectTag::find($id);

        $background = $request->input('image');
        if ($background)
            $background->update([
                'url' => $background
            ]);
        else
            Images::create([
                'url' => $background,
                'type' => VideoEffectTag::class,
                'type_id' => $id,
                'type_key' => 'background'
            ]);

        $videoEffectTag->delete();

        Toast::info(__('Tag was removed'));

        return redirect()->route('platform.systems.category.template.templateTag');
    }
}
