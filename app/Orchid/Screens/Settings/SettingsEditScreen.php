<?php

namespace App\Orchid\Screens\Settings;

use App\Models\Setting;
use App\Models\SettingText;
use App\Orchid\Layouts\Settings\FreeDownloadTextLayout;
use App\Orchid\Layouts\Settings\SettingFreeDownloadsLayout;
use App\Orchid\Layouts\Settings\SettingFreeClaimsLayout;
use App\Orchid\Layouts\Settings\SettingFreeWhitelistsLayout;
use App\Orchid\Layouts\Settings\SettingCreatorClaimsLayout;
use App\Orchid\Layouts\Settings\SettingCreatorWhitelistsLayout;
use App\Orchid\Layouts\Settings\SettingBussinessClaimsLayout;
use App\Orchid\Layouts\Settings\SettingBussinessWhitelistsLayout;
use App\Orchid\Layouts\Settings\UnlimitedDownloadTextLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SettingsEditScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $textSettings = SettingText::pluck('value', 'key')->toArray();
        return array_merge($settings, $textSettings);
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Settings subscription';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Changes related to subscription rates.';
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
                SettingFreeDownloadsLayout::class,
                SettingFreeClaimsLayout::class,
                SettingFreeWhitelistsLayout::class,
            ])->title('Free Limits'),

            Layout::block([
                FreeDownloadTextLayout::class
            ])->title('Free download text'),

            Layout::block([
                SettingCreatorClaimsLayout::class,
                SettingCreatorWhitelistsLayout::class
            ])->title('Creator Limits'),

            Layout::block([
                SettingBussinessClaimsLayout::class,
                SettingBussinessWhitelistsLayout::class
            ])->title('Bussiness Claims'),

            Layout::block([
                UnlimitedDownloadTextLayout::class
            ])->title('Unlimited download text'),
        ];
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function save(Request $request): RedirectResponse
    {
        Setting::where('key', 'free_downloads')->update(['value' => $request->input('free_downloads')]);
        Setting::where('key', 'free_claims')->update(['value' => $request->input('free_claims')]);
        Setting::where('key', 'free_whitelists')->update(['value' => $request->input('free_whitelists')]);
        Setting::where('key', 'creator_claims')->update(['value' => $request->input('creator_claims')]);
        Setting::where('key', 'creator_whitelists')->update(['value' => $request->input('creator_whitelists')]);
        Setting::where('key', 'bussiness_claims')->update(['value' => $request->input('bussiness_claims')]);
        Setting::where('key', 'bussiness_whitelists')->update(['value' => $request->input('bussiness_whitelists')]);

        SettingText::where('key', 'free_download_text')->update(['value' => $request->input('free_download_text')]);
        SettingText::where('key', 'unlimited_download_text')->update(['value' => $request->input('unlimited_download_text')]);

        Toast::info(__('Settings was saved'));

        return redirect()->route('platform.systems.settings');

    }
}