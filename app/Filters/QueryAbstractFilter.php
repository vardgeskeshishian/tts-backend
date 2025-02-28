<?php

namespace App\Filters;

use App\Exceptions\EmptySearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class QueryAbstractFilter
{
    protected Builder $builder;

    public function __construct(
        protected Request $request
    ) {}
	
	public function excludeList (): array
	{
		return [
			'q',
			'sort',
			'page',
			'perpage',
			'typeContent',
		];
	}
	
	/**
	 * @param Builder $builder
	 * @throws EmptySearchResult
	 */
    public function apply(Builder $builder): void
    {
        $this->builder = $builder;

        foreach ($this->request->except($this->excludeList()) as $field => $value) {
            $method = Str::camel($field);
            if (!method_exists($this, $method)) {
            	throw new EmptySearchResult('Empty result search', 404);
			}
			call_user_func([$this, $method],$value);
        }
    }

    /**
     * @param Builder $builder
     * @return void
     */
    public function applyWithCategories(Builder $builder): void
    {
        if (method_exists($this, 'noCategories'))
        {
            $this->builder = $builder;

            foreach ($this->request->except(array_merge($this->excludeList(), $this->noCategories()))
                     as $field => $value)
            {
                $method = Str::camel($field);
                if (method_exists($this, $method))
                    call_user_func([$this, $method],$value);
            }
        }
    }

    /**
     * @return array
     */
    public function getRequest(): array
    {
        return $this->request->toArray();
    }

    public function validate()
    {
        return true;
    }
}
