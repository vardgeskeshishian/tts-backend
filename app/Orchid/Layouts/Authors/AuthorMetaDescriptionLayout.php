<?php

namespace App\Orchid\Layouts\Authors;

use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class AuthorMetaDescriptionLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            TextArea::make('author.metaDescription')
                ->rows(5)
                ->title(__('Meta Description'))
                ->value($this->query->get('metaDescription')),
        ];
    }
}