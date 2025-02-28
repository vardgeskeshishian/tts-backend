<?php


namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Libs\Role;
use App\Models\Partner\Partner;
use App\Models\Finance\Balance;
use App\Services\Finance\FinanceService;

class UserRoleService
{
    public function assignRoleToUser(User $user, $roleName)
    {
        $role = Role::getByName($roleName);

        $currentRoles = $user->userRoles;

        foreach ($currentRoles as $currentRole) {
            if (!in_array($currentRole->role->name, Role::REPLACEABLE_ROLES)) {
                continue;
            }

            $currentRole->delete();
        }

        $user->userRoles()->create(['role_id' => $role->id]);

        if ($roleName === Role::ROLE_AUTHOR) {
            Balance::firstOrCreate([
                'date' => FinanceService::getFinanceDate(Carbon::now()),
                'status' => 'awaiting',
                'user_id' => $user->id,
            ]);

            Partner::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'user_id' => $user->id,
                'status' => Partner::STATUS_ACTIVATED,
            ]);
        }

        if ($roleName === Role::ROLE_PARTNER) {
            Partner::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'user_id' => $user->id,
                'status' => Partner::STATUS_NEW,
            ]);
        }

        if ($roleName === Role::ROLE_USER) {
            Partner::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'user_id' => $user->id,
                'status' => Partner::STATUS_DEACTIVATED,
            ]);
        }

        return true;
    }
}
