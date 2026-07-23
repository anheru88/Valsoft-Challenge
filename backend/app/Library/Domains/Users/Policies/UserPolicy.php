<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Policies;

use App\Library\Domains\Users\Enums\Permission;
use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Models\User;

/**
 * The permission matrix of PRD 8.3, asked as capabilities rather than as roles,
 * so which role carries what is data (see the roles migration) instead of being
 * hard-coded here.
 *
 * Policies answer "may this actor do this?" only — whether the world allows it
 * (last admin, active loans) is a business rule and belongs to the actions
 * (FR-ERR-2).
 */
final class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(Permission::ViewAnyUser->value);
    }

    /**
     * Staff read accounts for desk service; anybody reads their own.
     */
    public function view(User $actor, User $target): bool
    {
        return $actor->is($target) || $actor->can(Permission::ViewUser->value);
    }

    /**
     * The requested role is part of the question: a librarian may create
     * members and nothing else, which is an authorization decision and
     * therefore a 403.
     */
    public function create(User $actor, UserRole $role = UserRole::Member): bool
    {
        if ($actor->can(Permission::CreateAnyUser->value)) {
            return true;
        }

        return $role === UserRole::Member && $actor->can(Permission::CreateMember->value);
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->is($target) || $actor->can(Permission::ManageUsers->value);
    }

    /**
     * FR-USER-3: role and status changes are not self-service.
     */
    public function changeRole(User $actor): bool
    {
        return $actor->can(Permission::ManageUsers->value);
    }

    public function changeStatus(User $actor): bool
    {
        return $actor->can(Permission::ManageUsers->value);
    }

    public function delete(User $actor): bool
    {
        return $actor->can(Permission::ManageUsers->value);
    }
}
