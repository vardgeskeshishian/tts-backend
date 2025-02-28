<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Site materials')),

            Menu::make(__('Roles'))
                ->icon('bs.person-bounding-box')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles'),

            Menu::make(__('Authors'))
                ->icon('bs.person-gear')
                ->route('platform.systems.authors')
                ->permission('platform.systems.users'),

            Menu::make(__('Payments'))
                ->icon('bs.currency-exchange')
                ->route('platform.systems.payments')
                ->permission('platform.systems.users'),

            Menu::make(__('Video Effects'))
                ->icon('bs.camera-video-fill')
                ->route('platform.systems.video')
                ->permission('platform.systems.users'),

            Menu::make(__('Tracks'))
                ->icon('bs.music-player-fill')
                ->route('platform.systems.tracks')
                ->permission('platform.systems.users'),

            Menu::make(__('SFX Track'))
                ->icon('bs.music-note-beamed')
                ->route('platform.systems.sfx')
                ->permission('platform.systems.users'),

            Menu::make(__('Pages'))
                ->icon('bs.journal-richtext')
                ->route('platform.systems.pages')
                ->permission('platform.systems.users'),

            Menu::make(__('FAQ'))
                ->icon('bs.question-square')
                ->list([
                    Menu::make(__('FAQ Category'))
                        ->icon('bs.patch-question')
                        ->route('platform.systems.faqs.categories')
                        ->permission('platform.systems.users'),

                    Menu::make(__('FAQ Section'))
                        ->icon('bs.patch-question-fill')
                        ->route('platform.systems.faqs.sections')
                        ->permission('platform.systems.users'),
                ])->divider(),

            Menu::make(__('Audio'))
                ->icon('bs.shield')
                ->list([
                    Menu::make(__('Standard'))
                        ->icon('bs.shield')
                        ->route('platform.systems.license', ['type' => 'standard', 'type_content' => 'audio'])
                        ->permission('platform.systems.users'),

                    Menu::make(__('Extended'))
                        ->icon('bs.shield')
                        ->route('platform.systems.license', ['type' => 'extended', 'type_content' => 'audio'])
                        ->permission('platform.systems.users'),

                    Menu::make(__('Creator'))
                        ->icon('bs.shield')
                        ->route('platform.systems.license', ['type' => 'creator', 'type_content' => 'audio'])
                        ->permission('platform.systems.users'),

                    Menu::make(__('Business'))
                        ->icon('bs.shield')
                        ->route('platform.systems.license', ['type' => 'business', 'type_content' => 'audio'])
                        ->permission('platform.systems.users'),

                ])->permission('platform.systems.users')
                ->title(__('Generate License')),

            Menu::make(__('Video'))
                ->icon('bs.shield')
                ->list([
                    Menu::make(__('Standard'))
                        ->icon('bs.shield')
                        ->route('platform.systems.license', ['type' => 'standard', 'type_content' => 'video'])
                        ->permission('platform.systems.users'),

                    Menu::make(__('Extended'))
                        ->icon('bs.shield')
                        ->route('platform.systems.license', ['type' => 'extended', 'type_content' => 'video'])
                        ->permission('platform.systems.users'),

                    Menu::make(__('Creator'))
                        ->icon('bs.shield')
                        ->route('platform.systems.license', ['type' => 'creator', 'type_content' => 'video'])
                        ->permission('platform.systems.users'),

                    Menu::make(__('Business'))
                        ->icon('bs.shield')
                        ->route('platform.systems.license', ['type' => 'business', 'type_content' => 'video'])
                        ->permission('platform.systems.users'),

                ])->permission('platform.systems.users')->divider(),

            Menu::make(__('Music'))
                ->icon('bs.shield')
                ->list([
                    Menu::make(__('Genres'))
                        ->icon('bs.shield')
                        ->route('platform.systems.category.music.genre'),

                    Menu::make(__('Moods'))
                        ->icon('bs.shield')
                        ->route('platform.systems.category.music.mood'),

                    Menu::make(__('Instruments'))
                        ->icon('bs.shield')
                        ->route('platform.systems.category.music.instrument'),

                    Menu::make(__('Usage Types'))
                        ->icon('bs.shield')
                        ->route('platform.systems.category.music.usage-type'),

                    Menu::make(__('Curator Pick'))
                        ->icon('bs.shield')
                        ->route('platform.systems.category.music.curator-pick'),

                    Menu::make(__('Tags'))
                        ->icon('bs.shield')
                        ->route('platform.systems.category.music.tag'),
                ])
                ->title(__('Category')),

            Menu::make(__('Template'))
                ->icon('bs.shield')
                ->list([
                    Menu::make(__('Applications'))
                        ->icon('bs.shield')
                        ->route('platform.systems.category.template.application'),

                    Menu::make(__('Categories'))
                        ->icon('bs.shield')
                        ->route('platform.systems.category.template.category'),

                    Menu::make(__('Tags'))
                        ->icon('bs.shield')
                        ->route('platform.systems.category.template.templateTag'),
                ]),

            Menu::make(__('SFX'))
                ->icon('bs.shield')
                ->list([
                    Menu::make(__('Categories'))
                        ->icon('bs.shield')
                        ->route('platform.systems.category.sfx.sfxCategory'),

                    Menu::make(__('Tags'))
                        ->icon('bs.shield')
                        ->route('platform.systems.category.sfx.sfxTag')
                ])->divider(),

            Menu::make(__('Search'))
                ->icon('bs.search')
                ->route('platform.systems.search.content')
                ->permission('platform.systems.users'),

            Menu::make(__('Upload Bulk'))
                ->icon('bs.upload')
                ->route('platform.systems.upload-bulk')
                ->permission('platform.systems.users'),

            Menu::make(__('Webhooks'))
                ->icon('bs.h-square')
                ->route('platform.systems.webhooks')
                ->permission('platform.systems.users'),

            Menu::make(__('Sort Categories'))
                ->icon('bs.h-square')
                ->route('platform.systems.sort-categories')
                ->permission('platform.systems.users'),

            Menu::make(__('Settings'))
                ->icon('bs.gear-fill')
                ->route('platform.systems.settings')
                ->permission('platform.systems.users'),

            Menu::make(__('Paddle Api Key'))
                ->icon('bs.key-fill')
                ->route('platform.systems.paddle.keys')
                ->permission('platform.systems.users'),

            Menu::make(__('Robots.txt'))
                ->icon('bs.file-earmark-easel-fill')
                ->route('platform.systems.edit.robots')
                ->divider(),

            Menu::make('Documentation')
                ->title('Docs')
                ->icon('bs.box-arrow-up-right')
                ->url('https://orchid.software/en/docs')
                ->target('_blank'),

            Menu::make('Changelog')
                ->icon('bs.box-arrow-up-right')
                ->url('https://github.com/orchidsoftware/platform/blob/master/CHANGELOG.md')
                ->target('_blank')
                ->badge(fn () => Dashboard::version(), Color::DARK),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
