<?php

namespace App\Orchid\Layouts\Tracks;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Actions\Link;

class TrackLinkArchiveLayout extends Rows
{
    public function __construct(
        public ?string $url
    )
    {}

    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Link::make('Zip Archive')
                ->href('/storage'.$this->url)
        ];
    }
}