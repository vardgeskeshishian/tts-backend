<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class CheckboxView extends Component
{
    public $boolean;

    /**
     * Create a new component instance.
     *
     * @param $boolean
     */
    public function __construct($boolean)
    {
        $this->boolean = $boolean;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.checkbox-view');
    }
}
