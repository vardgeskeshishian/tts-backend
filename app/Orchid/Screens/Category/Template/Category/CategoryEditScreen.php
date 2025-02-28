<?php

namespace App\Orchid\Screens\Category\Template\Category;

use App\Models\Images;
use App\Models\VideoEffects\VideoEffectApplication;
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
use App\Models\VideoEffects\VideoEffectCategory;
use App\Orchid\Layouts\Category\CategoryH1Layout;
use App\Orchid\Layouts\Category\CategoryNameLayout;
use App\Orchid\Layouts\Category\CategoryImageLayout;
use App\Orchid\Layouts\Category\CategoryGoogleUrlLayout;
use App\Orchid\Layouts\Category\CategoryMetaTitleLayout;
use App\Orchid\Layouts\Category\CategoryDescriptionLayout;
use App\Orchid\Layouts\Category\CategoryMetaDescriptionLayout;
use App\Orchid\Layouts\Category\CategoryIsBlackLayout;
use App\Orchid\Layouts\Category\Template\Category\CategoryPriceExtendedLayout;
use App\Orchid\Layouts\Category\Template\Category\CategoryPriceStandardLayout;

class CategoryEditScreen extends Screen
{
    public $tag;

    public bool $exists = false;

    /**
     * @param VideoEffectCategory $category
     * @return VideoEffectCategory[]
     */
    public function query(VideoEffectCategory $category): array
    {
        $this->exists = $category->exists;
        return [
            'tag' => $category
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->tag->exists ? 'Edit Category' : 'Create Category';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Category profile';
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
     * @param VideoEffectCategory $category
     * @return RedirectResponse
     */
    public function save(Request $request, VideoEffectCategory $category): RedirectResponse
    {
        try {
            $categories = VideoEffectCategory::where('slug', '!=', $category->slug)
                ->pluck('slug')->toArray();
            $applications = VideoEffectApplication::pluck('slug')->toArray();

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

        $category->fill($data)->save();

        $icon = $category->icon;
        $background = $data['icon']['url'];
        if ($background) {
            if ($icon) {
                $icon->update([
                    'url' => $background
                ]);
            } else {
                Images::create([
                    'url' => $background,
                    'type' => VideoEffectCategory::class,
                    'type_id' => $category->id,
                    'type_key' => 'icon'
                ]);
            }
        } else {
            $icon?->delete();
        }

        Toast::info(__('Category was saved'));

        return redirect()->route('platform.systems.category.template.category');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function remove(Request $request, VideoEffectCategory $category): RedirectResponse
    {
        $category->delete();

        Toast::info(__('Category was removed'));

        return redirect()->route('platform.systems.category.template.category');
    }
}
