<?php

namespace App\Actions;

use App\Models\Tags\Genre;
use App\Models\Tags\Instrument;
use App\Models\Tags\Mood;
use App\Models\Tags\Type;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GetAllCategoryTags
{
	public function handle()
	{
		return Cache::remember('category_slugs', 5500, function () {
			$models = [
				Genre::class,
				Mood::class,
				Type::class,
				Instrument::class,
			];
			
			$slugs = [];
			
			foreach ($models as $model) {
				/** @var Model $model */
				$slugs = array_merge($slugs, $model::query()->pluck('slug')->toArray());
			}
			
			return $slugs;
		});
	}
}
