<?php

namespace App\View\Components;

use Illuminate\View\View;
use Illuminate\View\Component;

class ClickableId extends Component
{
    public $baseUrl;
    public $itemId;
    public $postfix;

    /**
     * Create a new component instance.
     *
     * @param $baseUrl
     * @param $itemId
     * @param string $postfix
     */
    public function __construct($baseUrl, $itemId, $postfix = '')
    {
        $this->baseUrl = $baseUrl;
        $this->itemId = $itemId;
        $this->postfix = $postfix;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        $fullUrl = implode('/', [
            $this->baseUrl,
            $this->itemId,
            $this->postfix
        ]);
        return view('components.clickable-id', compact('fullUrl'));
    }
}
