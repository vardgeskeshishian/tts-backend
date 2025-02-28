<?php

namespace App\Orchid\Screens\TemplateMeta;

use App\Enums\TypeContentEnum;
use App\Models\Structure\TemplateMeta;
use App\Orchid\Layouts\TemplateMeta\TemplateMetaDescriptionLayout;
use App\Orchid\Layouts\TemplateMeta\TemplateMetaH1Layout;
use App\Orchid\Layouts\TemplateMeta\TemplateMetaImageLayout;
use App\Orchid\Layouts\TemplateMeta\TemplateMetaMetaDescriptionLayout;
use App\Orchid\Layouts\TemplateMeta\TemplateMetaMetaTitleLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\Log;

class TemplateMetaContentEditScreen extends Screen
{
    private TypeContentEnum $contentType = TypeContentEnum::TRACK;

    /**
     * @param TypeContentEnum $contentType
     * @return array
     */
    public function query(TypeContentEnum $contentType): array
    {
        $this->contentType = $contentType;

        $class = $contentType->getClass();

        return [
            'templateMeta' => TemplateMeta::firstOrCreate(['type' => $class])
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Edit Template '.ucfirst($this->contentType->value);
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return null;
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
     * @return iterable
     */
    public function commandBar(): iterable
    {
        $link = match ($this->contentType)
        {
            TypeContentEnum::TRACK => Link::make(__('Return'))
                ->icon('bs.reply-fill')
                ->route('platform.systems.tracks'),
            TypeContentEnum::VIDEO_EFFECT => Link::make(__('Return'))
                ->icon('bs.reply-fill')
                ->route('platform.systems.video'),
            TypeContentEnum::SFX => Link::make(__('Return'))
                ->icon('bs.reply-fill')
                ->route('platform.systems.sfx'),
        };
        return [
            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save'),

            $link
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            Layout::block([
                TemplateMetaH1Layout::class
            ])->title('H1')
                ->description('For automatic H1 generation'),

            Layout::block([
                TemplateMetaDescriptionLayout::class
            ])->title('Description')
                ->description('For description under H1'),

            Layout::block([
                TemplateMetaMetaTitleLayout::class
            ])->title('Meta-title')
                ->description('Meta title'),

            Layout::block([
                TemplateMetaMetaDescriptionLayout::class
            ])->title('Meta-description')
                ->description('Meta description'),

            Layout::block([
                TemplateMetaImageLayout::class
            ])->title('Image')
                ->description('Image')
        ];
    }

    /**
     * @param TypeContentEnum $contentType
     * @param Request $request
     * @return RedirectResponse
     */
    public function save(TypeContentEnum $contentType, Request $request): RedirectResponse
    {
        $template = TemplateMeta::where('type', $contentType->getClass())->first();

        $data = $request->get('templateMeta');

        $description = $data['description'];
        $newDescription = '';
        $rows = explode("\r\n", $description);
        foreach ($rows as $row)
        {
            $newDescription .= Str::ucfirst(Str::lower($row)).
                ($row != end($rows) ? "\r\n" : "");
        }
        $data['description'] = $newDescription;

        $template->update($data);

        Cache::forget($contentType->value);
        Cache::put($contentType->value, $template, Carbon::now()->addDay());
        Toast::info(__('Template Meta was saved'));

        return redirect()->route('platform.systems.template', ['contentType' => $contentType]);
    }
}