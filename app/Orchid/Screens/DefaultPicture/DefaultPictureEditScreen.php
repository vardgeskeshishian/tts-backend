<?php

namespace App\Orchid\Screens\DefaultPicture;

use App\Models\SettingText;
use App\Orchid\Layouts\DefaultPicture\DefaultPictureLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class DefaultPictureEditScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'picture' => SettingText::firstOrCreate([
                'key' => 'default_picture'
            ], [
                'value' => ''
            ])
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Default Picture';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Default Picture';
    }

    /**
     * @return iterable|null
     */
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
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            Layout::block([
                DefaultPictureLayout::class
            ])->title('Default Picture'),
        ];
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function save(Request $request): RedirectResponse
    {
        $picture = SettingText::where('key', 'default_picture')->first();
        $picture->update([
            'value' => $request->input('picture.value')
        ]);

        Cache::forget('default_picture');
        Cache::put('default_picture', $picture->value, Carbon::now()->addDay());

        Toast::info(__('Default Picture was saved'));

        return redirect()->route('platform.systems.default_picture');
    }
}