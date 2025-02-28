<?php

namespace App\Orchid\Screens\Category\SFX\Tag;

use App\Models\Images;
use App\Orchid\Layouts\Category\CategoryPriorityLayout;
use App\Orchid\Listeners\Category\CategoryEditListener;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Screen;
use App\Models\SFX\SFXTag;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\RedirectResponse;
use App\Orchid\Layouts\Category\CategoryH1Layout;
use App\Orchid\Layouts\Category\CategoryNameLayout;
use App\Orchid\Layouts\Category\CategoryImageLayout;
use App\Orchid\Layouts\Category\CategoryGoogleUrlLayout;
use App\Orchid\Layouts\Category\CategoryMetaTitleLayout;
use App\Orchid\Layouts\Category\CategoryDescriptionLayout;
use App\Orchid\Layouts\Category\CategoryMetaDescriptionLayout;
use App\Orchid\Layouts\Category\CategoryIsBlackLayout;

class TagEditScreen extends Screen
{
    public $tag;

    public bool $exists = false;
    /**
     * @param SFXTag $tag
     * @return SFXTag[]
     */
    public function query(SFXTag $tag): array
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
     * @param SFXTag $tag
     * @return RedirectResponse
     */
    public function save(Request $request, SFXTag $tag): RedirectResponse
    {
        try {
            $request->validate([
                'tag.slug' => [
                    'required',
                    Rule::unique(SFXTag::class, 'slug')->ignore($tag),
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
                    'type' => SFXTag::class,
                    'type_id' => $tag->id,
                    'type_key' => 'icon'
                ]);
            }
        } else {
            $icon?->delete();
        }

        Toast::info(__('Tag was saved'));

        return redirect()->route('platform.systems.category.sfx.sfxTag');
    }

    /**
     * @param SFXTag $tag
     * @return RedirectResponse
     */
    public function remove(SFXTag $tag): RedirectResponse
    {
        $tag->delete();

        Toast::info(__('Tag was removed'));

        return redirect()->route('platform.systems.category.sfx.sfxTag');
    }
}
