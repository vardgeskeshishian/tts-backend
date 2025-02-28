<?php


namespace App\View\Components;

use Illuminate\View\Component;

class CreateNewButton extends Component
{
    public $url;
    public $title;

    public function __construct($url, $title)
    {
        $this->url = $url;
        $this->title = $title;
    }

    public function render()
    {
        return view('components.create-new-button');
    }
}
