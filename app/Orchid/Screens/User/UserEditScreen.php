<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Models\SFX\SFXTrack;
use App\Models\Track;
use App\Models\UserFavoritesFolder;
use App\Models\VideoEffects\VideoEffect;
use App\Orchid\Listeners\User\UserRoleAuthorListener;
use App\Orchid\Listeners\User\UserEditListener;
use App\Orchid\Layouts\User\UserPasswordLayout;
use App\Orchid\Layouts\User\UserPayoneerAccountLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Orchid\Access\Impersonation;
use App\Models\Authors\AuthorProfile;
use App\Models\User;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Facades\Alert;

class UserEditScreen extends Screen
{
    /**
     * @var User
     */
    public $user;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(User $user): iterable
    {
        $user->load(['roles', 'authorsMusic', 'authorsVideo']);

        return [
            'user'       => $user,
            'permission' => $user->getStatusPermission(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->user->exists ? 'Edit User' : 'Create User';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'User profile and privileges, including their associated role.';
    }

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
            Button::make(__('Impersonate user'))
                ->icon('bg.box-arrow-in-right')
                ->confirm(__('You can revert to your original state by logging out.'))
                ->method('loginAs')
                ->canSee($this->user->exists && $this->user->id !== \request()->user()->id),

            Button::make(__('Remove'))
                ->icon('bs.trash3')
                ->confirm(__('Once the account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.'))
                ->method('remove')
                ->novalidate()
                ->canSee($this->user->exists),

            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save')
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [

            Layout::block(UserEditListener::class)
                ->title(__('Profile Information'))
                ->description(__('Update your account\'s profile information and email address.')),

            Layout::block(UserPasswordLayout::class)
                ->title(__('Password'))
                ->description(__('Ensure your account is using a long, random password to stay secure.')),

            Layout::block(new UserRoleAuthorListener($this->user?->roles?->pluck('id')->toArray()))
                ->title(__('Roles'))
                ->description(__('A Role defines a set of tasks a user assigned the role is allowed to perform.')),

            Layout::block(UserPayoneerAccountLayout::class)
                ->title(__('Payoneer Account')),
        ];
    }

    /**
     * @param User $user
     * @param Request $request
     * @return RedirectResponse
     */
    public function save(User $user, Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'user.email' => [
                    'required',
                    Rule::unique(User::class, 'email')->ignore($user),
                ],
                'user.name' => [
                    'required'
                ]
            ]);
        } catch (ValidationException $e) {//ToDo redirect()->back()->withInput(); ?
            Alert::error($e->getMessage());
            return redirect()->route('platform.systems.users.edit', $user->id);
        }

        if (is_array($request->input('user.roles')) && in_array(1, $request->input('user.roles') ))
        {
            $permissions = [
                'platform.index' => 1,
                'platform.systems.roles' => 1,
                'platform.systems.users' => 1,
                'platform.systems.attachment' => 1,
            ];
        } else {
            $permissions = [];
        }

        $user->when($request->filled('user.password'), function (Builder $builder) use ($request) {
            $builder->getModel()->password = Hash::make($request->input('user.password'));
        });

        $data = $request->collect('user')->except(['password', 'permissions', 'roles'])->toArray();

        if (is_null($data['payout_email']))
            $data['payout_email'] = $data['email'];

        $user
            ->fill($data)
            ->forceFill(['permissions' => $permissions])
            ->save();

        $user->roles()->detach();
        $roles = collect($request->input('user.roles'))->push(2);
        $user->roles()->attach($roles->unique()->toArray());
        AuthorProfile::where('user_id', $user->id)
            ->update([
                'user_id' => null
            ]);

        if (is_array($request->input('user.roles')) && in_array(4, $request->input('user.roles') )) {
            $authorMusicIds = $request->input('user.authorsMusic') ?? [];
            $authorVideoIds = $request->input('user.authorsVideo') ?? [];
            AuthorProfile::whereIn('id', $authorMusicIds)
                ->orWhereIn('id', $authorVideoIds)
                ->update([
                    'user_id' => $user->id,
                ]);
        }

        UserFavoritesFolder::firstOrcreate([
            'folder_type' => Track::class,
            'user_id' => $user->id,
            'title' => 'Favorites',
        ]);

        UserFavoritesFolder::firstOrcreate([
            'folder_type' => VideoEffect::class,
            'user_id' => $user->id,
            'title' => 'Favorites',
        ]);

        UserFavoritesFolder::firstOrcreate([
            'folder_type' => SFXTrack::class,
            'user_id' => $user->id,
            'title' => 'Favorites',
        ]);

        Toast::info(__('User was saved.'));

        return redirect()->route('platform.systems.users');
    }

    /**
     * @param User $user
     * @return RedirectResponse
     * @throws \Exception
     */
    public function remove(User $user): RedirectResponse
    {
        $user->delete();

        Toast::info(__('User was removed'));

        return redirect()->route('platform.systems.users');
    }

    /**
     * @param User $user
     * @return RedirectResponse
     */
    public function loginAs(User $user): RedirectResponse
    {
        Impersonation::loginAs($user);

        Toast::info(__('You are now impersonating this user'));

        return redirect()->route(config('platform.index'));
    }
}
