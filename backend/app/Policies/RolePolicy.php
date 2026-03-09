<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('role_access.read');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('role_access.read');
    }

    public function create(User $user): bool
    {
        return $user->can('role_access.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('role_access.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('role_access.delete');
    }
}
