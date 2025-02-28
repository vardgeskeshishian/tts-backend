<?php

namespace App\Orchid\Listeners\User;

use App\Models\Libs\Role;
use Illuminate\Http\Request;
use Orchid\Screen\Repository;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Layout;
use App\Models\Authors\AuthorMusic;
use App\Models\Authors\AuthorVideo;
use Orchid\Screen\Layouts\Listener;

class UserRoleAuthorListener extends Listener
{
    protected bool $display;

    public function __construct(?array $roles = null)
    {
        $this->display = is_array($roles) && in_array(4, $roles);
    }

    protected $targets = [
        'user.roles.',
    ];

    /**
     * @return Layout[]
     */
    protected function layouts(): iterable
    {
        return [
            Layout::rows([
                Select::make('user.roles.')
                    ->fromModel(Role::class, 'name')
                    ->multiple()
                    ->title(__('Name role'))
                    ->help('Specify which groups this account should belong to'),

                Select::make('user.authorsMusic.')
                    ->fromModel(AuthorMusic::class, 'name')
                    ->multiple()
                    ->title(__('Author Music'))
                    ->canSee($this->display),

                Select::make('user.authorsVideo.')
                    ->fromModel(AuthorVideo::class, 'name')
                    ->multiple()
                    ->title(__('Author Video'))
                    ->canSee($this->display)
            ]),
        ];
    }

    /**
     * @param Repository $repository
     * @param Request $request
     *
     * @return Repository
     */
    public function handle(Repository $repository, Request $request): Repository
    {
        $roles = $request->input('user.roles');

        if (in_array(4, $roles)) {
            $this->display = true;
            return $repository
                ->set('user.roles', $roles);
        } else {
            $this->display = false;
            return $repository
                ->set('user.roles', $roles)
                ->set('user.authorsMusic', [])
                ->set('user.authorsVideo', []);
        }
    }
}
