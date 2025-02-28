<?php

namespace App\Orchid\Screens\License;

use App\Models\License;
use App\Orchid\Layouts\License\LayoutDescriptionLayout;
use App\Orchid\Layouts\License\LicensePriceLayout;
use App\Orchid\Layouts\License\LicenseDownloadCountLayout;
use App\Orchid\Layouts\License\LicenseSampleLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Illuminate\Support\Str;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LicenseEditScreen extends Screen
{
    public ?License $license = null;

    /**
     * @param string $type
     * @param string $type_content
     * @return array
     */
    public function query(string $type, string $type_content): array
    {
        $type_content = $type_content == 'audio' ? 3 : 4;

        return [
            'license' => License::where('type', Str::ucfirst($type))
                ->where('license_type_id', $type_content)->first(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Edit License '.$this->license?->type;
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return '';
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

    public function layout(): iterable
    {
        return [
            Layout::block([
                LicenseSampleLayout::class
            ])->vertical()
            ->description('Use %Author_Name%, %Product_Name%, %Product_Link%, %Date%, and %Number_License% in your design template to have the author name, product name, product link, date, and license number populate when used.')
        ];
    }

    /**
     * @param string $type
     * @param string $type_content
     * @param Request $request
     * @return RedirectResponse
     */
    public function save(string $type, string $type_content, Request $request): RedirectResponse
    {
        $typeContentId = $type_content == 'audio' ? 3 : 4;

        License::where('type', Str::ucfirst($type))
            ->where('license_type_id', $typeContentId)->update($request->get('license'));

        Toast::info(__('License was saved.'));

        return redirect()->route('platform.systems.license', ['type' => $type, 'type_content' => $type_content]);
    }
}