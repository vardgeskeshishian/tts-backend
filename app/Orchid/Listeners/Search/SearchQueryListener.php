<?php

namespace App\Orchid\Listeners\Search;

use App\Models\Authors\AuthorProfile;
use App\Models\Tags\Genre;
use App\Models\Tags\Mood;
use App\Models\Tags\Instrument;
use App\Models\Tags\Tag;
use App\Models\Tags\Type;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Models\VideoEffects\VideoEffectPlugin;
use App\Models\VideoEffects\VideoEffectResolution;
use App\Models\SFX\SFXCategory;
use App\Models\VideoEffects\VideoEffectTag;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\RadioButtons;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class SearchQueryListener extends Listener
{
    /**
     * @var array
     */
    public array $typeCategorySee;

    /**
     * @var array
     */
    public array $authors;

    /**
     * @var array
     */
    public array $genres;

    /**
     * @var array
     */
    public array $moods;

    /**
     * @var array
     */
    public array $instruments;

    /**
     * @var array
     */
    public array $usageTypes;

    /**
     * @var array
     */
    public array $applications;

    /**
     * @var array
     */
    public array $templateCategories;

    /**
     * @var array
     */
    public array $plugins;

    /**
     * @var array
     */
    public array $resolutions;

    /**
     * @var array
     */
    public array $sfxCategores;

    /**
     * @var array
     */
    public array $templateTags;

    /**
     * @var array
     */
    public array $tags;

    public function __construct(
        ?array $typeCategorySee = null,
    )
    {
        $this->authors = AuthorProfile::pluck('name', 'slug')->toArray();
        $this->genres = Genre::pluck('name', 'slug')->toArray();
        $this->moods = Mood::pluck('name', 'slug')->toArray();
        $this->instruments = Instrument::pluck('name', 'slug')->toArray();
        $this->usageTypes = Type::pluck('name', 'slug')->toArray();
        $this->applications = VideoEffectApplication::pluck('name', 'slug')->toArray();
        $this->templateCategories = VideoEffectCategory::pluck('name', 'slug')->toArray();
        $this->plugins = VideoEffectPlugin::pluck('name', 'id')->toArray();
        $this->resolutions = VideoEffectResolution::pluck('name', 'id')->toArray();
        $this->sfxCategores = SFXCategory::pluck('name', 'slug')->toArray();
        $this->templateTags = VideoEffectTag::pluck('name', 'slug')->toArray();
        $this->tags = Tag::pluck('name', 'slug')->toArray();

        if(is_null($typeCategorySee))
            $this->typeCategorySee = [
                'track' => true,
                'template' => false,
                'sfx' => false,
            ];
        else
            $this->typeCategorySee = $typeCategorySee;
    }

    /**
     * @var array
     */
    protected $targets = [
        'typeContent',
    ];

    protected function layouts(): iterable
    {
        return [
            Layout::block([
                Layout::rows([
                    RadioButtons::make('typeContent')
                        ->value(request()->input('typeContent') ?? 'track')
                        ->options([
                            'track' => 'Track',
                            'template' => 'Template',
                            'sfx' => 'SFX',
                        ])
                ]),
            ]),

            Layout::block([
                Layout::rows([
                    Select::make('author')
                        ->options($this->authors)
                        ->title(__('Author'))
                        ->empty('Null')
                        ->value(request()->input('author'))
                ])->canSee($this->typeCategorySee['track'] || $this->typeCategorySee['template']),

                Layout::rows([
                    Select::make('genre')
                        ->options($this->genres)
                        ->title(__('Genre'))
                        ->empty('Null')
                        ->value(request()->input('genre'))
                ])->canSee($this->typeCategorySee['track']),

                Layout::rows([
                    Select::make('mood')
                        ->options($this->moods)
                        ->title(__('Mood'))
                        ->empty('Null')
                        ->value(request()->input('mood'))
                ])->canSee($this->typeCategorySee['track']),

                Layout::rows([
                    Select::make('instrument')
                        ->options($this->instruments)
                        ->title(__('Instrument'))
                        ->empty('Null')
                        ->value(request()->input('instrument'))
                ])->canSee($this->typeCategorySee['track']),

                Layout::rows([
                    Select::make('usageType')
                        ->options($this->usageTypes)
                        ->title(__('Usage Type'))
                        ->empty('Null')
                        ->value(request()->input('usageType'))
                ])->canSee($this->typeCategorySee['track']),

                Layout::rows([
                    Select::make('tag')
                        ->options($this->tags)
                        ->title(__('Tag'))
                        ->empty('Null')
                        ->value(request()->input('tag'))
                ])->canSee($this->typeCategorySee['track']),

                Layout::rows([
                    Select::make('templateCategory')
                        ->options($this->templateCategories)
                        ->title(__('Template Category'))
                        ->empty('Null')
                        ->value(request()->input('templateCategory')),
                ])->canSee($this->typeCategorySee['template']),

                Layout::rows([
                    Select::make('sfxCategory')
                        ->options($this->sfxCategores)
                        ->title(__('SFX Category'))
                        ->empty('Null')
                        ->value(request()->input('sfxCategory')),
                ])->canSee($this->typeCategorySee['sfx']),

                Layout::rows([
                    Input::make('bpmMin')
                        ->type('number')
                        ->title('BPM Min')
                        ->value(request()->input('bpmMin'))
                ])->canSee($this->typeCategorySee['track']),

                Layout::rows([
                    Input::make('bpmMax')
                        ->type('number')
                        ->title('BPM Max')
                        ->value(request()->input('bpmMax'))
                ])->canSee($this->typeCategorySee['track']),

                Layout::rows([
                    Input::make('durationMix')
                        ->type('number')
                        ->title('Duration Min')
                        ->value(request()->input('durationMix'))
                ])->canSee($this->typeCategorySee['track']),

                Layout::rows([
                    Input::make('durationMax')
                        ->type('number')
                        ->title('Duration Max')
                        ->value(request()->input('durationMax'))
                ])->canSee($this->typeCategorySee['track']),

                Layout::rows([
                    Select::make('applications')
                        ->options($this->applications)
                        ->multiple()
                        ->title(__('Template Application'))
                        ->value(explode(' ', request()->input('applications')))
                ])->canSee($this->typeCategorySee['template']),

                Layout::rows([
                    Select::make('plugins')
                        ->options($this->plugins)
                        ->multiple()
                        ->title(__('Plugins'))
                        ->value(request()->input('plugins')),
                ])->canSee($this->typeCategorySee['template']),

                Layout::rows([
                    Select::make('resolutions')
                        ->options($this->resolutions)
                        ->multiple()
                        ->title(__('Resolutions'))
                        ->value(request()->input('resolutions')),
                ])->canSee($this->typeCategorySee['template']),

                Layout::rows([
                    Select::make('tag')
                        ->options($this->templateTags)
                        ->title(__('Tag'))
                        ->empty('Null')
                        ->value(request()->input('tag'))
                ])->canSee($this->typeCategorySee['template']),

                Layout::rows([
                    CheckBox::make('onlyPremium')
                        ->title(' Only Premium')
                        ->value(request()->input('onlyPremium')),
                ]),

                Layout::rows([
                    Select::make('sort')
                        ->options([
                            'trending' => 'Trending',
                            'downloads' => 'Top Downloads',
                            'new' => 'Newest'
                        ])->value(request()->input('sort'))
                ]),

                Layout::rows([
                    Input::make('q')
                        ->title('Search Query')
                        ->value(request()->input('q')),
                ]),

                Layout::rows([
                    Button::make(__('Search'))
                        ->type(Color::BASIC)
                        ->icon('bs.search')
                        ->route('platform.systems.search.content.search')
                ])
            ]),
        ];
    }

    /**
     * @param Repository $repository
     * @param Request $request
     * @return Repository
     */
    public function handle(Repository $repository, Request $request): Repository
    {
        switch ($request->input('typeContent')) {
            case 'template':
                $this->typeCategorySee = array_replace(
                    $this->typeCategorySee,
                    [
                        'track' => false,
                        'template' => true,
                        'sfx' => false
                    ]
                );

                return $repository
                    ->set('typeContent', 'template')
                    ->set('q', '');

            case 'sfx':
                $this->typeCategorySee = array_replace(
                    $this->typeCategorySee,
                    [
                        'track' => false,
                        'template' => false,
                        'sfx' => true
                    ]
                );

                return $repository
                    ->set('typeContent', 'sfx')
                    ->set('q', '');

            default:
                $this->typeCategorySee = array_replace(
                    $this->typeCategorySee,
                    [
                        'track' => true,
                        'template' => false,
                        'sfx' => false
                    ]
                );

                return $repository
                    ->set('typeContent', 'track')
                    ->set('q', '');
        }
    }
}