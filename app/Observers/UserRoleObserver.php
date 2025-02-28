<?php

namespace App\Observers;

use App\Models\UserRole;

class UserRoleObserver
{
    /**
     * Handle the user role "created" event.
     *
     * @param UserRole $userRole
     *
     * @return void
     */
    public function created(UserRole $userRole)
    {
        //
    }

    /**
     * Handle the user role "updated" event.
     *
     * @param UserRole $userRole
     *
     * @return void
     */
    public function updated(UserRole $userRole)
    {
        //
    }

    /**
     * Handle the user role "deleted" event.
     *
     * @param UserRole  $userRole
     *
     * @return void
     */
    public function deleted(UserRole $userRole)
    {
        //
    }

    /**
     * Handle the user role "restored" event.
     *
     * @param  UserRole  $userRole
     *
     * @return void
     */
    public function restored(UserRole $userRole)
    {
        //
    }

    /**
     * Handle the user role "force deleted" event.
     *
     * @param  UserRole  $userRole
     *
     * @return void
     */
    public function forceDeleted(UserRole $userRole)
    {
        //
    }
}
