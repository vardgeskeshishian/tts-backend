<?php

namespace App\Services;

use App\Http\Resources\Api\CoreResource;
use App\Models\CoreDefaults;
use App\Models\Structure\Core;
use App\Models\SystemAuthor;
use App\Models\Track;
use App\Models\VideoEffects\VideoEffect;
use Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CoreService extends AbstractModelService
{
    protected $modelClass = Core::class;

    private Request $request;
    private $questionsService;

    public function __construct(
        ImagesService    $imagesService,
        MetaService      $metaService,
        TaggingService   $taggingService,
        QuestionsService $questionsService,
    ) {
        parent::__construct($imagesService, $metaService, $taggingService);
        $this->questionsService = $questionsService;
    }

    public function getCore()
    {
        return Core::select(['id', 'type', 'tag', 'value', 'name'])->get();
    }

    public function fill(Request $request)
    {
        $modelData = [
            'type' => $request->get('type'),
            'tag' => $request->get('tag'),
            'value' => $request->get('value'),
        ];

        $model = Core::where($modelData)->first();

        $data = $this->buildDataFromRequest($request);

        if (!$model) {
            $model = new Core;
            $model->name = implode(":", Arr::flatten($modelData));
            $model->save();
        }

        return $this->fillInModel($model, $data);
    }

    /**
     * @param Model $model
     * @param $builtData
     *
     * @return CoreResource|mixed
     */
    protected function fillInModel($model, $builtData)
    {
        [$data, $meta] = $builtData;

        $model->fill($data);
        $model->save();

        $this->metaService->fillInForObject($model, $meta);

        $model->refresh();

        $className = Str::slug($model->getMorphClass());

        Cache::forget("{$className}:meta:{$model->id}");

        return new CoreResource($model);
    }

    public function saveFromArray(Core $core, array $meta, array $branding)
    {
        if (!$core->id) {
            $core->save();
        }

        $this->metaService->fillInForObject($core, array_merge($meta, $branding));

        $core->refresh();

        $className = Str::slug($core->getMorphClass());
        Cache::forget("$className:meta:$core->id");

        return $core;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function find()
    {
        /**
         * @var $modelData Collection
         */
        $modelData = [
            'type' => $this->request->get('type'),
            'tag' => $this->request->get('tag'),
            'value' => $this->request->get('value'),
        ];

        $modelName = strtolower(implode(':', $modelData));

        if (strpos($modelData['tag'], '[') > -1) {
            $tagModelData = explode('[', $modelData['tag']);
            $modelData['tag'] = $tagModelData[0];
        }
        if (strpos($modelData['tag'], '?q=') > -1) {
            $tagModelData = explode('?q=', $modelData['tag']);
            $modelData['tag'] = $tagModelData[0];
            $modelData['value'] = $tagModelData[1];
        }

        $defaults = $this->getDefaults([
            'type' => $modelData['type'] ?? "",
            'tag' => $modelData['tag'] ?? "",
            'value' => $modelData['value'] ?? "",
        ], $modelName);

        $hasCoreId = $this->request->has('core_id');
        $coreId = $this->request->get('core_id');

        $core = Core::when($hasCoreId, fn ($q) => $q->where('id', $coreId))
            ->when(!$hasCoreId, function ($q) use ($modelName, $modelData) {
                $replacedModelName = match ($modelName) {
                    'mainpage::' => '0::',
                    'video-templates::' => 'video-templates:0:',
                    default => $modelName,
                };

                $q->where(
                    fn ($q) => $q->where('name', $replacedModelName)
                        ->orWhere('name', rtrim($replacedModelName, ':'))
                        ->orWhere('name', $modelName)
                        ->orWhere('name', rtrim($modelName, ':'))
                )->orWhere(fn ($q) => $q->where($modelData));
            })
            ->first();

        if (!$core && !empty($defaults)) {
            if (isset($defaults["text2"])) {
                $defaults["text2"] = ' ' . $defaults["text2"] . ' ';
            }

            return [
                'name' => $modelName,
                'meta' => $defaults,
                'questions' => [],
            ];
        }

        if (!$core) {
            $core = $this->findClosestCore($modelData);
            $modelName = $core->name;
        }

        $questions = $this->questionsService->getForModel($core);

        $coreMeta = $core->getMeta();
        foreach ($defaults as $key => $value) {
            if (isset($coreMeta[$key])) {
                continue;
            }

            if ($key === "text2") {
                $value = ' ' . $value . ' ';
            }

            $coreMeta[$key] = $value;
        }

        return [
            'name' => $modelName,
            'meta' => $coreMeta,
            'questions' => $questions,
        ];
    }

    protected function getDefaults($modelData, $modelName)
    {
        $coreDefaults = CoreDefaults::all()->mapWithKeys(function ($default) {
            /**
             * @var $default CoreDefaults
             */
            $meta = $default->getMeta();
            return [
                $default['name'] => array_merge($meta, [
                    'page-description' => $meta['page-description'] ?? null,
                    'brand_title' => $meta['brand_title'] ?? null,
                    'brand_description_1' => $meta['brand_description_1'] ?? null,
                    'brand_description_2' => $meta['brand_description_2'] ?? null,
                ]),
            ];
        })->toArray();

        foreach ($modelData as $key => $val) {
            if ($key === 'type') {
                $modelData[$key] = Str::slug(Str::singular(strtolower($val)));
            }

            if ($key !== 'value') {
                $modelData[$key] = Str::slug($modelData[$key]);
            }
        }

        $type = trim($modelData['type'], ':');
        $absoluteDefaultFields = [
            'h1' => null,
            'h2' => null,
            'title' => null,
            'description' => null,
            'text2' => null,
            'page-description' => $meta['page-description'] ?? null,
            'brand_title' => $meta['brand_title'] ?? null,
            'brand_description_1' => $meta['brand_description_1'] ?? null,
            'brand_description_2' => $meta['brand_description_2'] ?? null,
    ];
        switch ($type) {
            case 'sfx':
                switch ($modelData['tag']) {
                    case 'categories':
                        $value = ucfirst($modelData['value']);
                        $defaults = $coreDefaults['sfx:categories'];
                        return $this->replaceValuePlaceholders($defaults, $value);
                    case 'tags':
                        $value = ucfirst($modelData['value']);
                        $defaults = $coreDefaults['sfx:tags'];
                        return $this->replaceValuePlaceholders($defaults, $value);
                    case 'search':
                        $value = isset($modelData['value']) ? ucfirst($modelData['value']) : '';
                        $defaults = $coreDefaults['sfx:search'];
                        return $this->replaceValuePlaceholders($defaults, $value);
                }
		return $coreDefaults['sfx0'];
	case 'video-template':
	switch ($modelData['tag']) {
		case 'tags':
                        $value = ucfirst($modelData['value']);
                        $defaults = $coreDefaults['video-templates:tag'];
                        return $this->replaceValuePlaceholders($defaults, $value);
                    case 'applications':
                        $value = ucfirst($modelData['value']);
                        $defaults = $coreDefaults['video-templates:applications'];
                        return $this->replaceValuePlaceholders($defaults, $value);
                    case 'categories':
                        $value = ucfirst($modelData['value']);
                        $defaults = $coreDefaults['video-templates:categories'];
                        return $this->replaceValuePlaceholders($defaults, $value);
                    case 'search':
                        $value = isset($modelData['value']) ? ucfirst($modelData['value']) : '';
                        $defaults = $coreDefaults['video-templates:search'];
                        return $this->replaceValuePlaceholders($defaults, $value);
                    case '0':
                        return $coreDefaults['video-templates'];
                }


	                    $trackName = Str::slug($modelData['tag']);

                /**
                 * @var $effect VideoEffect
                 */
                $effect = VideoEffect::where('slug', $trackName)->first();

                if (!$effect) {
                    return [];
                }

                $meta = $effect->getMeta();

                $coreDefaults['video-template']['h1'] = $effect->name;
                $coreDefaults['video-template']['h2'] = optional($effect->author)->name;

                foreach ($meta as $key => $value) {
                    $coreDefaults['video-template'][$key] = $value;
                }

                return $this->replaceValuePlaceholders($coreDefaults['video-template'], $effect->name);
            case 'search':
                if ($modelData['tag'] === 'query') {
                    $value = ucfirst($modelData['value']);
                    $defaults = $coreDefaults['search:query'];
                    return $this->replaceValuePlaceholders($defaults, $value);
                }

                if (in_array($modelName, ['search', 'search::'])) {
                    $modelName = 'search:browse';
                }

                unset($modelData['value']);

                $modelName = trim($modelName, ':');

                return $coreDefaults[$modelName] ?? [];
            case 'genre':
            case 'instrument':
            case 'usage-type':
            case 'mood':
                $value = ucfirst($modelData['tag']);
                $value = str_replace("-", " ", $value);
                $defaults = $coreDefaults[$modelData['type']];
                return $this->replaceValuePlaceholders($defaults, $value);
            case 'tag':
                $value = trim(
                    ucfirst(
                        str_replace(['Music', 'music'], '', $modelData['tag'])
                    ),
                    " \t\n\r\0\x0B\-"
                );
                $value = str_replace("-", " ", $value);
                $defaults = $coreDefaults['search:tag'];
                return $this->replaceValuePlaceholders($defaults, $value);
            case 'track':
                if ($modelData['tag'] === "ite28099s-time") {
                    $modelData['tag'] = "its-time";
                }

                $trackName = Str::slug($modelData['tag']);

                /**
                 * @var $track Track
                 */
                $track = Track::where('slug', $trackName)->first();

                if (!$track) {
                    return [];
                }

                $meta = $track->getMeta();

                $coreDefaults['track']['h1'] = $track->name;
                $coreDefaults['track']['h2'] = optional($track->author)->name;

                foreach ($meta as $key => $value) {
                    $coreDefaults['track'][$key] = $value;
                }

                return $this->replaceValuePlaceholders($coreDefaults['track'], $track->name);
            case 'author':
                $authorName = Str::slug($modelData['tag']);
                $author = SystemAuthor::where('slug', $authorName)->first();
                if (!$author) {
                    return [];
                }

                $meta = $author->getMeta();

                foreach ($meta as $key => $value) {
                    $coreDefaults['track'][$key] = $value;
                }

                $defaults = $this->replaceValuePlaceholders($coreDefaults['author'], $author->name);
                $defaults['text2'] = str_replace("%AUTHOR:DESCRIPTION%", $author->description, $defaults['text2']);

                return $defaults;
            case 'royalty-free-music':
                $tags = explode('-', $modelData['tag']);
                return $this->replaceMultipleValuePlaceholders($coreDefaults[$modelData['type']], $tags);
            case 'profile':
            case 'checkout':
                unset($modelData['value']);
                return $coreDefaults[$modelName] ?? $absoluteDefaultFields;
            default:
                return $coreDefaults[$modelData['type']] ?? $absoluteDefaultFields;
        }
    }

    protected function replaceValuePlaceholders($defaults, $value)
    {
        foreach ($defaults as $key => $string) {
            $defaults[$key] = str_replace("%VALUE%", $value, $string);
        }

        return $defaults;
    }

    protected function replaceMultipleValuePlaceholders($defaults, $values)
    {
        $amount = count($values);

        for ($i = 0; $i < $amount; $i++) {
            $valueKey = $i + 1;

            foreach ($defaults as $key => $string) {
                $defaults[$key] = str_replace("%VALUE{$valueKey}%", $values[$i], $string);
            }
        }

        return $defaults;
    }

    /**
     * find closest core meta data
     *
     * @param $modelData
     *
     * @return Core|array
     */
    protected function findClosestCore($modelData)
    {
        if ($this->findNextNonNullKey($modelData) === null) {
            return Core::where('name', '0::')->orWhere('name', 'default::')->first();
        }

        $latestKey = $this->findNextNonNullKey($modelData);
        $modelData[$latestKey] = null;

        $model = Core::where($modelData)->first();

        if (!$model) {
            return $this->findClosestCore($modelData);
        }

        return $model;
    }

    private function findNextNonNullKey($modelData)
    {
        foreach ($modelData as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            return $key;
        }

        return null;
    }
}
