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
    /**
     * Reading the list is a desk capability; how much of it is returned is not
     * decided here.
     *
     * `users.view` opens the list scoped to members, which is what the
     * check-out screen needs to find the person at the counter; `users.view-any`
     * opens it whole. The controller applies that scope, because a policy
     * answers whether an action is allowed, not which rows it may see.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->can(Permission::ViewAnyUser->value)
            || $actor->can(Permission::ViewUser->value);
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
