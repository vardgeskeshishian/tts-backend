<?php

namespace App\Orchid\Layouts\Authors;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class AuthorMetaTitleLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            Input::make('author.metaTitle')
                ->type('Meta Title')
                ->title(__('Meta Title'))
                ->value($this->query->get('metaTitle')),
        ];
    }
}