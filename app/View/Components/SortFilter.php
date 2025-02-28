<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SortFilter extends Component
{
    private $sortingFields;
    private $filteringFields;

    /**
     * Create a new component instance.
     *
     * @param $sortingFields
     * @param $filteringFields
     */
    public function __construct($sortingFields, $filteringFields)
    {
        $this->sortingFields = $sortingFields;
        $this->filteringFields = $filteringFields;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.sort-filter');
    }
}
