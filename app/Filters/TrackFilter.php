<?php

namespace App\Filters;

use App\Interfaces\FilterCategoriesInterface;

class TrackFilter extends QueryAbstractFilter implements FilterCategoriesInterface
{
    public function validate()
    {
        return $this->request->validate([
            'q' => 'regex:/[a-zA-Z0-9+&\-\s]/'
        ]);
    }

    /**
     * @return string[]
     */
    public function noCategories(): array
    {
        return [
            'bpmMin',
            'bpmMax',
            'durationMin',
            'durationMax',
            'onlyPremium',
        ];
    }
	
    /**
     * @param int $bpmMin
     * @return void
     */
    public function bpmMin(int $bpmMin): void
    {
        $this->builder->where('tempo', '>=', $bpmMin);
    }

    /**
     * @param int $bpmMax
     * @return void
     */
    public function bpmMax(int $bpmMax): void
    {
        $this->builder->where('tempo', '<=', $bpmMax);
    }

    /**
     * @param int $durationMin
     * @return void
     */
    public function durationMin(int $durationMin): void
    {
        $this->builder->whereHas('audio', function ($query) use ($durationMin) {
            $query->where('duration', '>=', $durationMin);
        });
    }

    /**
     * @param int $durationMax
     * @return void
     */
    public function durationMax(int $durationMax): void
    {
        $this->builder->whereHas('audio', function ($query) use ($durationMax) {
            $query->where('duration', '<=', $durationMax);
        });
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
     * @param string $author
     * @return void
     */
    public function author(string $author): void
    {
        $this->builder->whereHas('author', function ($query) use ($author) {
            $query->where('slug', $author);
        });
    }

    /**
     * @param string $genre
     * @return void
     */
    public function genre(string $genre): void
    {
        $this->builder->whereHas('genres', function ($query) use ($genre) {
            $query->where('slug', $genre);
        });
    }

    /**
     * @param string $mood
     * @return void
     */
    public function mood(string $mood): void
    {
        $this->builder->whereHas('moods', function ($query) use ($mood) {
            $query->where('slug', $mood);
        });
    }

    /**
     * @param string $instrument
     * @return void
     */
    public function instrument(string $instrument): void
    {
        $this->builder->whereHas('instruments', function ($query) use ($instrument) {
            $query->where('slug', $instrument);
        });
    }

    /**
     * @param string $type
     * @return void
     */
    public function usageType(string $type): void
    {
        $this->builder->whereHas('types', function ($query) use ($type) {
            $query->where('slug', $type);
        });
    }

    /**
     * @param string $tag
     * @return void
     */
    public function tag(string $tag): void
    {
        $this->builder->whereHas('tags', function ($query) use ($tag) {
            $query->where('slug', $tag);
        });
    }

    /**
     * @param string $curatorPick
     * @return void
     */
    public function curatorPick(string $curatorPick): void
    {
        $this->builder->whereHas('curatorPicks', function ($query) use ($curatorPick) {
            $query->where('slug', $curatorPick);
        });
    }
}
