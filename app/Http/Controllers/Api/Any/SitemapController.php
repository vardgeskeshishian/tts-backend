<?php


namespace App\Http\Controllers\Api\Any;

use App\Models\Track;
use App\Models\SystemAuthor;
use App\Models\Tags\Tag;
use App\Models\Tags\Mood;
use App\Models\Tags\Type;
use App\Models\SFX\SFXTag;
use App\Models\Tags\Genre;
use Illuminate\Support\Str;
use App\Models\Structure\Blog;
use App\Models\SFX\SFXCategory;
use App\Models\Tags\Instrument;
use App\Models\Structure\Collection;
use App\Models\Structure\DynamicTags;
use App\Http\Controllers\Api\ApiController;

use App\Models\VideoEffects\VideoEffect;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Models\VideoEffects\VideoEffectTag;

class SitemapController extends ApiController
{
    private $classes = [
        'genres' => Genre::class,
        'instruments' => Instrument::class,
        'moods' => Mood::class,
        'tags' => Tag::class,
        'usage-types' => Type::class,
        'collections' => Collection::class,
        'tracks' => Track::class,
        'authors' => SystemAuthor::class,
        'dynamic-tags' => DynamicTags::class,
        'blogs' => Blog::class,
        'sfx/categories' => SFXCategory::class,
	'sfx/tags' => SFXTag::class,
	'video-templates' => VideoEffect::class,
        'video-templates/applications' => VideoEffectApplication::class,
        'video-templates/categories' => VideoEffectCategory::class,
	'video-templates/tags' => VideoEffectTag::class,
    ];

    public function getInfo()
    {
        $ogClass = request()->get('class');
        $className = Str::plural($ogClass);

        $class = resolve($this->classes[$className]);

        /**
         * @var $all \Illuminate\Database\Eloquent\Collection
         */
        $all = $class::all();

        $map = [];

        foreach ($all as $item) {
            $updated = $item->updated_at ? $item->updated_at->timestamp : time() + $item->id;
            $updated = $this->incrementUpdatedAt($map, $updated);

            $map[$updated] = $item->slug;
        }

        return $this->success([
            $ogClass => $map,
        ]);
    }

    protected function incrementUpdatedAt($map, $updated)
    {
        if (!isset($map[$updated])) {
            return $updated;
        }

        $updated++;

        return $this->incrementUpdatedAt($map, $updated);
    }
}
