<?php

namespace App\Filters;

class VideoEffectFilter extends QueryAbstractFilter
{
    public function validate()
    {
        return $this->request->validate([
            'q' => 'regex:/[a-zA-Z0-9+&\-\s]/'
        ]);
    }

    /**
     * @param string $applications
     * @return void
     */
    public function applications(string $applications): void
    {
        $applications = explode(' ', $applications);
        $this->builder->whereHas('application', function($query) use ($applications) {
            $query->whereIn('slug', $applications);
        });
    }

    /**
     * @param string $application
     * @return void
     */
    public function application(string $application): void
    {
        $this->builder->whereHas('application', function ($query) use ($application) {
            $query->where('slug', $application);
        });
    }

    /**
     * @param string|array $plugins
     * @return void
     */
    public function plugins(array|string $plugins): void
    {
		$plugins = is_array($plugins)
			? $plugins :
			explode(' ', $plugins);
		
        $this->builder->whereHas('plugins', function ($query) use ($plugins) {
			$query->whereIn('slug', $plugins);
        });
    }

    /**
     * @param string $resolutions
     * @return void
     */
    public function resolutions(array|string $resolutions): void
    {
		$resolutions = is_array($resolutions)
			? $resolutions :
			explode(' ', $resolutions);
		
        $this->builder->whereHas('resolutions', function ($query) use ($resolutions) {
            $query->whereIn('slug', $resolutions);
        });
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
     * @param string $category
     * @return void
     */
    public function category(string $category): void
    {
        $this->builder->whereHas('categories', function ($query) use ($category) {
            $query->where('slug', $category);
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
}
