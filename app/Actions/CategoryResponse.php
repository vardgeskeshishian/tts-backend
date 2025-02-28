<?php

namespace App\Actions;

use App\Http\Resources\Any\AbstractTagResource;
use App\Models\Structure\TemplateMeta;
use Illuminate\Database\Eloquent\Model;

class CategoryResponse
{
	public function __construct(public Model $category)
	{
	}
	
	public function handle()
	{
		$templates = TemplateMeta::query()->get();
		$class = get_class($this->category);
		return (new AbstractTagResource(
			$this->category,
			$templates->where('type', $class)
				->values()
				->first()
		));
	}
}
