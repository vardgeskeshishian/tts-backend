<?php

namespace App\View\Components\Forms;

use Illuminate\View\Component;

class DeleteButton extends Component
{
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function render()
    {
        return view('components.forms.delete-button');
    }
}
