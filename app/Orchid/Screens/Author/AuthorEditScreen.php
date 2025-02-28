<?php

namespace App\Orchid\Screens\Author;

use App\Models\Images;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Action;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Facades\Layout;
use App\Models\Authors\AuthorProfile;
use Illuminate\Http\RedirectResponse;
use App\Orchid\Layouts\Authors\AuthorMetaTitleLayout;
use App\Orchid\Layouts\Authors\AuthorBackgroundLayout;
use App\Orchid\Layouts\Authors\AuthorTypeContentLayout;
use App\Orchid\Layouts\Authors\AuthorMetaDescriptionLayout;
use App\Orchid\Listeners\Author\AuthorEditListener;

class AuthorEditScreen extends Screen
{
    public $author;

    /**
     * Fetch data to be displayed on the screen.
     *
     *
     * @return array
     */
    public function query(AuthorProfile $author): iterable
    {
        $author->load('background', 'user');
        return [
            'author' => $author,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->author->exists ? 'Edit Author' : 'Create Author';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Change associated with the author.';
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
                ->method('remove')
                ->novalidate(),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]|\Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block([
                AuthorEditListener::class,
            ])->title('Author'),

            Layout::block(AuthorTypeContentLayout::class)
                ->title('Type Content'),

            Layout::block(AuthorMetaTitleLayout::class)
                ->title('Meta Title'),

            Layout::block(AuthorMetaDescriptionLayout::class)
                ->title('Meta Description'),

            Layout::block(AuthorBackgroundLayout::class)
                ->title('Background')
        ];
    }

    /**
     * @param Request $request
     * @param AuthorProfile $author
     * @return RedirectResponse
     */
    public function save(Request $request, AuthorProfile $author): RedirectResponse
    {
        $data = $request->toArray();
        $data['author']['slug'] = Str::slug($request->input('author.slug'));
        $request->replace($data);

        try {
            $request->validate([
                'author.name' => [
                    'required',
                    Rule::unique(AuthorProfile::class, 'name')->ignore($author),
                ],
                'author.slug' => [
                    'required',
                    Rule::unique(AuthorProfile::class, 'slug')->ignore($author),
                ],
            ]);
        } catch (ValidationException $e) {
            Alert::error($e->getMessage());
            return redirect()->back()->withInput();
        }

        $data = $request->get('author');
        $author->fill($data)->save();

        $backgroundImage = $author->background;
        $background = $data['background']['url'];
        if ($background) {
            if ($backgroundImage) {
                $backgroundImage->update([
                    'url' => $background
                ]);
            } else {
                Images::create([
                    'url' => $background,
                    'type' => AuthorProfile::class,
                    'type_id' => $author->id,
                    'type_key' => 'background'
                ]);
            }
        } else {
            $backgroundImage?->delete();
        }
        
        Toast::info(__('Author was saved'));

        return redirect()->route('platform.systems.authors');
    }

    /**
     * @throws \Exception
     *
     * @return RedirectResponse
     */
    public function remove(AuthorProfile $author): RedirectResponse
    {
        $author->delete();

        Toast::info(__('Author was removed'));

        return redirect()->route('platform.systems.authors');
    }
}
