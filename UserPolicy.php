<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

use App\Enums\Permissions\UserPermissions;
use App\Models\User;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(UserPermissions::VIEW);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function view(User $user, User $model)
    {
        if($user->hasPermissionTo(UserPermissions::VIEW)) {
            return true;
        }

        if($user->hasPermissionTo(UserPermissions::CABINET_VIEW)) {
            return $user->id === $model->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo(UserPermissions::CREATE);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function update(User $user, User $model)
    {
        if($user->hasPermissionTo(UserPermissions::UPDATE)) {
            return true;
        }

        if($user->hasPermissionTo(UserPermissions::CABINET_UPDATE)) {
            return $user->id === $model->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function delete(User $user, User $model)
    {
        //Нельзя удалить суперадмина
        if($model->id == 1) {
            return false;
        }

        //Нельзя удалить самого себя
        if($user->id == $model->id) {
            return false;
        }

        if($user->hasPermissionTo(UserPermissions::DELETE)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function restore(User $user, User $model)
    {
        if($user->hasPermissionTo(UserPermissions::CREATE)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function forceDelete(User $user, User $model)
    {
        //Нельзя удалить суперадмина
        if($model->id == 1) {
            return false;
        }

        //Нельзя удалить самого себя
        if($user->id == $model->id) {
            return false;
        }

        if($user->hasPermissionTo(UserPermissions::DELETE)) {
            return true;
        }

        return false;
    }
}
