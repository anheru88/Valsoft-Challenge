<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Policies;

use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Models\User;

/**
 * The permission matrix of PRD 8.3, in one place. Policies answer "may this
 * actor do this?" only — whether the world allows it (last admin, active loans)
 * is a business rule and belongs to the actions (FR-ERR-2).
 */
final class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Librarians read accounts for desk service; members read their own.
     */
    public function view(User $actor, User $target): bool
    {
        return $actor->isStaff() || $actor->is($target);
    }

    /**
     * Admins create any role; librarians create members only, which is an
     * authorization decision and therefore a 403, not a validation error.
     */
    public function create(User $actor, UserRole $role = UserRole::Member): bool
    {
        if ($actor->isAdmin()) {
            return true;
        }

        return $actor->role === UserRole::Librarian && $role === UserRole::Member;
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->isAdmin() || $actor->is($target);
    }

    /**
     * FR-USER-3: only an administrator changes roles.
     */
    public function changeRole(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function changeStatus(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function delete(User $actor): bool
    {
        return $actor->isAdmin();
    }
}
