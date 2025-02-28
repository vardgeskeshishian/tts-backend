<?php

namespace App\Orchid\Listeners\UploadBulk;

use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\RadioButtons;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class UploadBulkListener extends Listener
{
    public string $selectRadio = 'track';

    public array $textsFieldUpload = [
        'track' => 'Download CSV File with columns: name, author, bpm, description, genres, moods, instruments, usage-types, tags, is_premium, is_content_id, is_exclusive, is_new, is_featured',
        'template' => 'Download CSV File with columns: ID,name ,author, application, description, categories, resolutions, plugins, version, tags, is_exclusive, is_featured, is_new, has_content_id, hidden',
        'sfx' => 'Download CSV File with columns: name, is_premium, is_new',
    ];

    protected $targets = [
        'typeContent'
    ];

    protected function layouts(): iterable
    {
        return [
            Layout::block([
                Layout::rows([
                    RadioButtons::make('typeContent')
                        ->options([
                            'track' => 'Track',
                            'template' => 'Template',
                            'sfx' => 'SFX',
                        ])->value($this->selectRadio)
                ])
            ]),

            Layout::block([
                Layout::rows([
                    Upload::make('file')
                        ->maxFiles(1)
                        ->acceptedFiles('text/csv')
                        ->title('Upload File')
                ])
            ])->description($this->textsFieldUpload[$this->selectRadio])
            ->commands([
                Link::make('File template')
                    ->href(url('/'.$this->selectRadio.'.csv')),

                Button::make(__('Upload'))
                    ->type(Color::BASIC)
                    ->icon('bs.upload')
                    ->method('upload')
            ]),
        ];
    }

    public function handle(Repository $repository, Request $request): Repository
    {
        $this->selectRadio = $request->input('typeContent');
        return $repository;
    }
}
