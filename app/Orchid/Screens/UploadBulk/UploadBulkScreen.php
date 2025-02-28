<?php

namespace App\Orchid\Screens\UploadBulk;

use App\Enums\TypeContentEnum;
use App\Imports\ContentImport;
use App\Models\UploadBulk;
use App\Orchid\Listeners\UploadBulk\UploadBulkListener;
use App\Orchid\Layouts\UploadBulk\UploadBulkListLayout;
use App\Models\Orchid\Attachment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Action;
use Orchid\Screen\Layout;
use Orchid\Screen\Screen;

class UploadBulkScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        if (!file_exists(storage_path('app/public_html/'.date('Y').'/'.date('m').'/'.date('d'))))
            mkdir(storage_path('app/public_html/'.date('Y').'/'.date('m').'/'.date('d')), recursive: true);

        return [
            'uploads' => UploadBulk::filters()->defaultSort('id', 'desc')->paginate()
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Upload Bulk';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return '';
    }

    /**
     * @return iterable|null
     */
    public function permission(): ?iterable
    {
        return [
            'platform.systems.users',
        ];
    }

    /**
     * @return array|Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * @return array|Layout[]
     */
    public function layout(): iterable
    {
        return [
            UploadBulkListener::class,
            UploadBulkListLayout::class
        ];
    }

    public function upload(Request $request)
    {
        $attachment = Attachment::where('id', $request->input('file')[0])->first();

        $array = Excel::toArray(new ContentImport(), storage_path('app/public_html/'.$attachment->physicalPath()));
        $event = TypeContentEnum::getUploadEvent($request->input('typeContent'));

        foreach ($array[0] as $row)
        {
            $upload = UploadBulk::create([
                'file_name' => $attachment->original_name,
                'status' => 'send',
                'typeContent' => TypeContentEnum::getTypeContent($request->input('typeContent')),
                'name_content' => $row['name']
            ]);
            $event::dispatch($row, $upload);
        }
    }
}