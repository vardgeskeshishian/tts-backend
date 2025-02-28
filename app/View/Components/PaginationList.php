<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PaginationList extends Component
{
    public $list;

    /**
     * Create a new component instance.
     *
     * @param $list
     */
    public function __construct($list)
    {
        $this->list = $list;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.pagination-list');
    }
}
