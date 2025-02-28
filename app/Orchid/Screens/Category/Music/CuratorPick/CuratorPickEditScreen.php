<?php

namespace App\Orchid\Screens\Category\Music\CuratorPick;

use App\Models\Images;
use App\Models\Tags\CuratorPick;
use App\Models\Tags\Genre;
use App\Models\Tags\Instrument;
use App\Models\Tags\Mood;
use App\Models\Tags\Tagging;
use App\Models\Tags\Type;
use App\Models\Track;
use App\Orchid\Layouts\Category\CategoryDescriptionLayout;
use App\Orchid\Layouts\Category\CategoryH1Layout;
use App\Orchid\Layouts\Category\CategoryImageLayout;
use App\Orchid\Layouts\Category\CategoryIsBlackLayout;
use App\Orchid\Layouts\Category\CategoryMetaDescriptionLayout;
use App\Orchid\Layouts\Category\CategoryMetaTitleLayout;
use App\Orchid\Layouts\Category\Music\CuratorPick\CuratorPickTrackLayout;
use App\Orchid\Listeners\Category\CategoryEditListener;
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

class CuratorPickEditScreen extends Screen
{
    public bool $exists = false;

    public $tag;

    /**
     * @param CuratorPick $curatorPick
     * @return CuratorPick[]
     */
    public function query(CuratorPick $curatorPick): array
    {
        $this->exists = $curatorPick->exists;
        $curatorPick->load('icon');
        $curatorPick->load('tracks');
        return [
            'tag' => $curatorPick,
            'tracks' => $curatorPick->tracks->pluck('id')->toArray()
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->exists ? 'Edit Curator Pack' : 'Create Curator Pack';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Curator Pack profile';
    }

    /**
     * @return array|Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make(__('Remove'))
                ->icon('bs.trash3')
                ->method('remove')
                ->novalidate()
                ->canSee($this->exists),

            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    /**
     * @return iterable
     */
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
                CategoryImageLayout::class
            ])->title('Image'),

            Layout::block([
                CuratorPickTrackLayout::class
            ])->title('Tracks'),

            Layout::block([
                CategoryIsBlackLayout::class
            ])->title('Black text'),
        ];
    }

    /**
     * @param Request $request
     * @param CuratorPick $curatorPick
     * @return RedirectResponse
     */
    public function save(Request $request, CuratorPick $curatorPick): RedirectResponse
    {
        try {
            $curatorPicks = CuratorPick::where('slug', '!=', $curatorPick->slug)
                ->pluck('slug')->toArray();
            $genres = Genre::pluck('slug')->toArray();
            $instruments = Instrument::pluck('slug')->toArray();
            $moods = Mood::pluck('slug')->toArray();
            $types = Type::pluck('slug')->toArray();

            $slugs = array_merge($curatorPicks, $genres, $instruments, $moods, $types);

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

        $curatorPick->fill($data)->save();

        $icon = $curatorPick->icon;
        $background = $data['icon']['url'];
        if ($background) {
            if ($icon) {
                $icon->update([
                    'url' => $background
                ]);
            } else {
                Images::create([
                    'url' => $background,
                    'type' => CuratorPick::class,
                    'type_id' => $curatorPick->id,
                    'type_key' => 'icon'
                ]);
            }
        } else {
            $icon?->delete();
        }

        Tagging::where('tag_type', CuratorPick::class)
            ->where('tag_id', $curatorPick->id)->delete();

        $tracks = $request->input('tracks');

        if (is_array($tracks))
        {
            foreach ($tracks as $id)
            {
                Tagging::firstOrCreate([
                    'object_type' => Track::class,
                    'object_id' => $id,
                    'tag_type' => CuratorPick::class,
                    'tag_id' => $curatorPick->id
                ]);
            }
        }

        Toast::info(__('Curator Pick was saved'));

        return redirect()->route('platform.systems.category.music.curator-pick');
    }

    /**
     * @param CuratorPick $curatorPick
     * @return RedirectResponse
     */
    public function remove(CuratorPick $curatorPick): RedirectResponse
    {
        $curatorPick->delete();

        Toast::info(__('Curator Pick was removed'));

        return redirect()->route('platform.systems.category.music.curator-pick');
    }
}
