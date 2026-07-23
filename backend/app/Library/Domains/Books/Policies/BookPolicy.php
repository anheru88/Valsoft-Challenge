<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Policies;

use App\Library\Domains\Users\Models\User;

/**
 * PRD 8.3: everyone browses the catalogue, staff curate it.
 */
final class BookPolicy
{
    public function viewAny(User $actor): bool
    {
        return true;
    }

    public function view(User $actor): bool
    {
        return true;
    }

    public function create(User $actor): bool
    {
        return $actor->isStaff();
    }

    public function update(User $actor): bool
    {
        return $actor->isStaff();
    }

    public function delete(User $actor): bool
    {
        return $actor->isStaff();
    }
}
