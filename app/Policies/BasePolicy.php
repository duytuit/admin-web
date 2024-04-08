<?php

namespace App\Policies;

use App\Models\BoUser as User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Determine whether the user can view any branches.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function index($user, $model)
    {
        return $this->checkPermission($user, $model, 'index');
    }

    /**
     * Determine whether the user can view the branch.
     *
     * @param  \App\User  $user
     * @param  \App\Branch  $branch
     * @return mixed
     */
    public function view($user, $model)
    {
        if ($model->id) {
            $current = $user->id === $model->id;
            return $this->checkPermission($user, $model, 'view') || $current;
        }
        return $this->checkPermission($user, $model, 'view');
    }

    /**
     * Determine whether the user can create branches.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create($user, $model)
    {
        $current = $user->id === $model->id;

        if (!$model->id) {
            return $this->checkPermission($user, $model, 'create');
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can update the branch.
     *
     * @param  \App\User  $user
     * @param  \App\Branch  $branch
     * @return mixed
     */
    public function update($user, $model)
    {
        if ($model->id) {
            $current = $user->id === $model->id;
            return $this->checkPermission($user, $model, 'update') || $current;
        }
        return $this->checkPermission($user, $model, 'update');
    }

    /**
     * Determine whether the user can delete the branch.
     *
     * @param  \App\User  $user
     * @param  \App\Branch  $branch
     * @return mixed
     */
    public function delete($user, $model)
    {
        $current = $user->id === $model->id;
        return $this->checkPermission($user, $model, 'delete') || $current;
    }

    /**
     * Handle all requested permission checks.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return bool
     */
    public function __call($name, $arguments)
    {
        if (count($arguments) < 2) {
            throw new \InvalidArgumentException('not enough arguments');
        }
        /** @var \TCG\Voyager\Contracts\User $user */
        $user = $arguments[0];

        /** @var $model */
        $model = $arguments[1]->getTable();

        $permition = $model . '.' . $name;
        
        return $user->hasAccess([$permition]);
    }

    /**
     * Check if user has an associated permission.
     *
     * @param \App\Models\BoUser $user
     * @param object             $model
     * @param string             $action
     *
     * @return bool
     */
    protected function checkPermission($user, $model, $action)
    {
        $table_name = $model->getTable();
        $permition  = $table_name . '.' . $action;
        return $user->hasAccess([$permition]);
    }

}
