<?php

namespace App\Orchid\Screens\Category\Music\Genre;

use App\Models\Images;
use App\Models\Tags\CuratorPick;
use App\Models\Tags\Genre;
use App\Models\Tags\Instrument;
use App\Models\Tags\Mood;
use App\Models\Tags\Type;
use App\Orchid\Layouts\Category\CategoryPriorityLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
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
use App\Orchid\Listeners\Category\CategoryEditListener;

class GenreEditScreen extends Screen
{
    public $tag;

    public bool $exists = false;

    /**
     * @param Genre $genre
     * @return Genre[]
     */
    public function query(Genre $genre): array
    {
        $this->exists = $genre->exists;
        $genre->load('icon');
        return [
            'tag' => $genre
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->tag->exists ? 'Edit Genre' : 'Create Genre';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Genre profile';
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
     * @param Genre $genre
     * @return RedirectResponse
     */
    public function save(Request $request, Genre $genre): RedirectResponse
    {
        try {
            $genres = Genre::where('slug', '!=', $genre->slug)->pluck('slug')->toArray();
            $customPicks = CuratorPick::pluck('slug')->toArray();
            $instruments = Instrument::pluck('slug')->toArray();
            $moods = Mood::pluck('slug')->toArray();
            $types = Type::pluck('slug')->toArray();

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

        $genre->fill($data)->save();

        $icon = $genre->icon;
        $background = $data['icon']['url'];
        if ($background) {
            if ($icon) {
                $icon->update([
                    'url' => $background
                ]);
            } else {
                Images::create([
                    'url' => $background,
                    'type' => Genre::class,
                    'type_id' => $genre->id,
                    'type_key' => 'icon'
                ]);
            }
        } else {
            $icon?->delete();
        }

        Toast::info(__('Genre was saved'));

        return redirect()->route('platform.systems.category.music.genre');
    }

    /**
     * @param Genre $genre
     * @return RedirectResponse
     */
    public function remove(Genre $genre): RedirectResponse
    {
        $genre->delete();

        Toast::info(__('Genre was removed'));

        return redirect()->route('platform.systems.category.music.genre');
    }
}
