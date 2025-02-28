<?php

namespace App\Console\Commands;

use App\Actions\GetAllCategoryTags;
use App\Models\Authors\Author;
use App\Models\Authors\AuthorProfile;
use App\Models\SFX\SFXCategory;
use App\Models\SFX\SFXTrack;
use App\Models\Structure\Page;
use App\Models\Tags\Genre;
use App\Models\Tags\Instrument;
use App\Models\Tags\Mood;
use App\Models\Tags\Tag;
use App\Models\Track;
use App\Models\VideoEffects\VideoEffect;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Models\VideoEffects\VideoEffectPlugin;
use App\Services\RobotsTXTService;
use Illuminate\Database\Eloquent\Builder;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Spatie\Sitemap\SitemapGenerator;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates sitemap';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
		public RobotsTXTService $service
	)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
		
		info('sitemap:generate is running -> '. now()->toDateTimeString());
		$disallowRules = $this->service->getDisallowRules();
		$siteMapData = [
			[
				"sitemap" => 'sitemap_services.xml',
				"data" => AuthorProfile::query()->where(function (Builder $query) {
					$query->has('tracks', '>', 0)
						->whereHas('tracks', function (Builder $q) {
							$q->where('hidden', '=', 0);
						});
					})->pluck('slug')
					->reduce(function ($acc, $url) use ($disallowRules) {
						$path = "/author/$url";
						if (!in_array($path, $disallowRules)) {
							$acc[] = config('app.front_url') . $path;
						}
						return $acc;
					}, []),
			],
			[
				"sitemap" => 'sitemap_services.xml',
				"data" => Page::query()
					->whereNotNull('path')
					->pluck('path')
					->reduce(function ($acc, $url) use ($disallowRules) {
						$path = "$url";
						if (!in_array($path, $disallowRules)) {
							$acc[] = config('app.front_url') . $path;
						}
						return $acc;
					}, []),
			],
			[
				"sitemap" => 'sitemap_video_effect_categories.xml',
				"data" => VideoEffectCategory::query()
					->pluck('slug')
					->reduce(function ($acc, $url) use ($disallowRules) {
						$path = "/video-templates/categories/$url";
						if (!in_array($path, $disallowRules)) {
							$acc[] = config('app.front_url') . $path;
						}
//						$otherPath = "/video-templates/search?q=$url";
//						if (!in_array($path, $disallowRules)) {
//							$acc[] = config('app.front_url') . $otherPath;
//						}
						return $acc;
					}, []),
			],
			[
				"sitemap" => 'sitemap_video_effects.xml',
				"data" => VideoEffect::query()
					->where('hidden', '=',0)
					->pluck('slug')
					->reduce(function ($acc, $url) use ($disallowRules) {
						$path = "/video-templates/$url";
						if (!in_array($path, $disallowRules)) {
							$acc[] = config('app.front_url') . $path;
						}
						return $acc;
					}, []),
			],
			[
				"sitemap" => 'sitemap_tracks.xml',
				"data" => Track::query()
					->where('hidden', '=',0)
					->pluck('slug')
					->reduce(function ($acc, $url) use ($disallowRules) {
						$path = "/royalty-free-music/$url";
						if (!in_array($path, $disallowRules)) {
							$acc[] = config('app.front_url') . $path;
						}
						return $acc;
					}, []),
			],
			[
				"sitemap" => 'sitemap_genres.xml',
				"data" => Genre::query()
					->pluck('slug')
					->reduce(function ($acc, $url) use ($disallowRules) {
						$path = "/genres/$url";
						if (!in_array($path, $disallowRules)) {
							$acc[] = config('app.front_url') . $path;
						}
						return $acc;
					}, []),
			],
			[
				"sitemap" => 'sitemap_genres.xml',
				"data" => Mood::query()
					->pluck('slug')
					->reduce(function ($acc, $url) use ($disallowRules) {
						$path = "/moods/$url";
						if (!in_array($path, $disallowRules)) {
							$acc[] = config('app.front_url') . $path;
						}
						return $acc;
					}, []),
			],
			[
				"sitemap" => 'sitemap_instruments.xml',
				"data" => Instrument::query()
					->pluck('slug')
					->reduce(function ($acc, $url) use ($disallowRules) {
						$path = "/instruments/$url";
						if (!in_array($path, $disallowRules)) {
							$acc[] = config('app.front_url') . $path;
						}
						return $acc;
					}, []),
			],
			[
				"sitemap" => 'sitemap_sfx_categories.xml',
				"data" => SFXCategory::query()
					->pluck('slug')
					->reduce(function ($acc, $url) use ($disallowRules) {
						$path = "/sfx/categories/$url";
						if (!in_array($path, $disallowRules)) {
							$acc[] = config('app.front_url') . $path;
						}
						return $acc;
					}, []),
			],
			[
				"sitemap" => 'sitemap_sfx_categories.xml',
				"data" => $this->getTagUrls($disallowRules),
			],
		];
		
		$siteMap = SitemapGenerator::create(config('app.front_url'))->getSitemap();
		foreach ($siteMapData as $page) {
			$siteMap->add($page['data']);
		}
		$siteMap->writeToDisk('public_html', 'sitemap.xml');
    }
	
	private function getTagUrls(array $disallowRules): array
	{
		$urls = [];
		$tagExcludeList = (new GetAllCategoryTags())->handle();
		
		// Using a direct query with a join to fetch tags associated with tracks
		DB::table('tags')
			->join('taggings', 'tags.id', '=', 'taggings.tag_id')
			->join('tracks', 'taggings.object_id', '=', 'tracks.id')
			->where('tracks.hidden', '=', 0)
			->where('taggings.object_type', '=', 'App\Models\Track')
			->select('tags.slug')
			->distinct()
			->orderBy('tags.slug')
			->chunk(100, function ($tags) use ($disallowRules, &$urls, $tagExcludeList) {
				foreach ($tags as $tag) {
					$path = "/tags/{$tag->slug}";
					if (!in_array($path, $disallowRules) && !in_array($tag->slug, $tagExcludeList)) {
						$urls[] = config('app.front_url') . $path;
					}
				}
			});
		
		return $urls;
	}
}
