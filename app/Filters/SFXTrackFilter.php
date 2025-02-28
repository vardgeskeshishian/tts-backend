<?php

namespace App\Filters;

use App\Interfaces\FilterCategoriesInterface;

class SFXTrackFilter extends QueryAbstractFilter implements FilterCategoriesInterface
{
    public function validate()
    {
        return $this->request->validate([
            'q' => 'regex:/[a-zA-Z0-9+&\-\s]/'
        ]);
    }

    /**
     * @param bool $onlyPremium
     * @return void
     */
    public function onlyPremium(bool $onlyPremium): void
    {
        if ($onlyPremium)
            $this->builder->where('premium', 1);
    }

    /**
     * @param string $category
     * @return void
     */
    public function category(string $category): void
    {
        $this->builder->whereHas('sfxCategories', function ($query) use ($category) {
            $query->where('slug', $category);
        });
    }

    /**
     * @param string $tag
     * @return void
     */
    public function tag(string $tag): void
    {
        $this->builder->whereHas('sfxTags', function ($query) use ($tag) {
            $query->where('slug', $tag);
        });
    }

    /**
     * @param int $durationMin
     * @return void
     */
    public function durationMin(int $durationMin): void
    {
        $this->builder->where('duration', '>=', $durationMin);
    }

    /**
     * @param int $durationMax
     * @return void
     */
    public function durationMax(int $durationMax): void
    {
        $this->builder->where('duration', '<=', $durationMax);
    }

    public function categories(): array
    {
        return [
            'category',
            'tag'
        ];
    }

    public function noCategories(): array
    {
        return [
            'onlyPremium',
            'durationMin',
            'durationMax',
        ];
    }
}