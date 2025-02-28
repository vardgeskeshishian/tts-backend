<?php

namespace App\Orchid\Screens\TemplateMeta;

use App\Models\SFX\SFXCategory;
use App\Models\SFX\SFXTag;
use App\Models\Structure\TemplateMeta;
use App\Models\Tags\CuratorPick;
use App\Models\Tags\Genre;
use App\Models\Tags\Instrument;
use App\Models\Tags\Mood;
use App\Models\Tags\Tag;
use App\Models\Tags\Type;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Models\VideoEffects\VideoEffectTag;
use App\Orchid\Layouts\TemplateMeta\TemplateMetaDescriptionLayout;
use App\Orchid\Layouts\TemplateMeta\TemplateMetaH1Layout;
use App\Orchid\Layouts\TemplateMeta\TemplateMetaMetaDescriptionLayout;
use App\Orchid\Layouts\TemplateMeta\TemplateMetaMetaTitleLayout;
use App\Orchid\Layouts\TemplateMeta\TemplateMetaImageLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TemplateMetaEditScreen extends Screen
{
    public $category;

    public $categoryType;

    protected array $morphTo = [
        'tag' => [
            'class' => Tag::class,
            'type' => 'music',
        ],
        'genre' => [
            'class' => Genre::class,
            'type' => 'music',
        ],
        'instrument' => [
            'class' => Instrument::class,
            'type' => 'music'
        ],
        'usage-type' => [
            'class' => Type::class,
            'type' => 'music',
        ],
        'mood' => [
            'class' => Mood::class,
            'type' => 'music',
        ],
        'curator-pick' => [
            'class' => CuratorPick::class,
            'type' => 'music'
        ],
        'application' => [
            'class' => VideoEffectApplication::class,
            'type' => 'template',
        ],
        'category' => [
            'class' => VideoEffectCategory::class,
            'type' => 'template',
        ],
        'templateTag' => [
            'class' => VideoEffectTag::class,
            'type' => 'template',
        ],
        'sfxCategory' => [
            'class' => SFXCategory::class,
            'type' => 'sfx',
        ],
        'sfxTag' => [
            'class' => SFXTag::class,
            'type' => 'sfx',
        ],
    ];

    public function query(string $category): array
    {
        $categoryClass = $this->morphTo[$category];
        $templateMeta = TemplateMeta::where('type', $categoryClass['class'])->first();

        if (is_null($templateMeta))
        {
            $templateMeta = new TemplateMeta();
            $templateMeta->type = $categoryClass['class'];
            $templateMeta->save();
        }

        return [
            'templateMeta' => $templateMeta,
            'category' => $category,
            'categoryType' => $categoryClass['type']
        ];
    }

    public function name(): ?string
    {
        return 'Edit Template Meta '.ucfirst($this->category);
    }

    public function description(): ?string
    {
        return 'Use the %Category_Name% construction in the template so that when used, the category name will be substituted there';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.systems.users',
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save'),

            Link::make(__('Return'))
                ->icon('bs.reply-fill')
                ->route('platform.systems.category.'.($this->categoryType).'.'.($this->category)),
        ];
    }

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

    public function save(string $category, Request $request): RedirectResponse
    {
        $categoryClass = $this->morphTo[$category];

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

        TemplateMeta::where('type', $categoryClass)->update($data);
        Toast::info(__('Template Meta was saved'));

        return redirect()->route('platform.systems.category.'.($this->categoryType).'.'.($this->category));
    }
}