<?php

declare(strict_types=1);

namespace App\Library\Domains\Authors\Policies;

use App\Library\Domains\Users\Models\User;

final class AuthorPolicy
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
