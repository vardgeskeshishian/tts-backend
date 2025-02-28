<?php

namespace App\Orchid\Screens\Category\Music\UsageType;

use App\Models\Images;
use App\Models\Tags\CuratorPick;
use App\Models\Tags\Genre;
use App\Models\Tags\Instrument;
use App\Models\Tags\Mood;
use App\Models\Tags\Type;
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
use App\Orchid\Layouts\Category\CategoryImageLayout;
use App\Orchid\Layouts\Category\CategoryGoogleUrlLayout;
use App\Orchid\Layouts\Category\CategoryMetaTitleLayout;
use App\Orchid\Layouts\Category\CategoryDescriptionLayout;
use App\Orchid\Layouts\Category\CategoryMetaDescriptionLayout;
use App\Orchid\Layouts\Category\CategoryIsBlackLayout;

class UsageTypeEditScreen extends Screen
{
    public $tag;

    public bool $exists = false;

    /**
     * @param Type $type
     * @return Type[]
     */
    public function query(Type $type): array
    {
        $this->exists = $type->exists;
        return [
            'tag' => $type
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->tag->exists ? 'Edit Usage Type' : 'Create Usage Type';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Usage Type profile';
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
     * @param Type $type
     * @return RedirectResponse
     */
    public function save(Request $request, Type $type): RedirectResponse
    {
        try {
            $genres = Genre::pluck('slug')->toArray();
            $customPicks = CuratorPick::pluck('slug')->toArray();
            $instruments = Instrument::pluck('slug')->toArray();
            $moods = Mood::pluck('slug')->toArray();
            $types = Type::where('slug', '!=', $type->slug)
                ->pluck('slug')->toArray();

            $slugs = array_merge($genres, $instruments, $moods, $types, $customPicks);

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

        $type->fill($data)->save();

        $icon = $type->icon;
        $background = $data['icon']['url'];
        if ($background) {
            if ($icon) {
                $icon->update([
                    'url' => $background
                ]);
            } else {
                Images::create([
                    'url' => $background,
                    'type' => Type::class,
                    'type_id' => $type->id,
                    'type_key' => 'icon'
                ]);
            }
        } else {
            $icon?->delete();
        }

        Toast::info(__('Usage Type was saved'));

        return redirect()->route('platform.systems.category.music.usage-type');
    }

    /**
     * @param Type $type
     * @return RedirectResponse
     */
    public function remove(Type $type): RedirectResponse
    {
        $type->delete();

        Toast::info(__('Usage Type was removed'));

        return redirect()->route('platform.systems.category.music.mood');
    }
}
