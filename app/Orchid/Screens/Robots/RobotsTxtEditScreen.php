<?php

namespace App\Orchid\Screens\Robots;

use App\Orchid\Layouts\Robots\RobotsTxtFileEditLayout;
use Artisan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\Log;

class RobotsTxtEditScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        $text = null;
        $filePath = base_path().'/public_html/robots.txt';

        if(file_exists($filePath))
            $text = file_get_contents(base_path().'/public_html/robots.txt');

        return [
            'text' => $text
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return file_exists(base_path().'/public_html/robots.txt') ? 'Edit File robots.txt': 'File does not exist';
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
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
			Button::make(__('Regenerate Sitemap'))
				->icon('bs.repeat')
				->method('regenerate'),
			
			Link::make('sitemap.xml')
				->icon('bs.reply')
				->target('blank')
				->href(url('/storage/sitemap.xml')),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            Layout::block([
                RobotsTxtFileEditLayout::class
            ])->title('Text')
            ->commands(
                Button::make(__('Save'))
                    ->icon('bs.check-circle')
                    ->method('save'),
            ),
        ];
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function save(Request $request): RedirectResponse
    {
        $filePath = base_path().'/public_html/robots.txt';

        if(file_exists($filePath)) {
            file_put_contents(base_path().'/public_html/robots.txt', $request->input('text'), LOCK_EX);
            Toast::info(__('File robots.txt was saved'));
        } else {
            Toast::error(__('File does not exist'));
        }

        return redirect()->route('platform.systems.edit.robots');
    }
	
	public function regenerate(): RedirectResponse
	{
		try{
			Artisan::call('sitemap:generate');
			Toast::info(__('Sitemap is regenerated.'));
		}catch(\Exception $e){
			Log::info($e->getMessage());
			Toast::error(__('Sitemap is not regenerated.'));
		}
		
		return redirect()->back();
	}
}
