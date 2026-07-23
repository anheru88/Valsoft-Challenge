<?php

declare(strict_types=1);

namespace App\Library\Domains\Categories\Policies;

use App\Library\Domains\Users\Enums\Permission;
use App\Library\Domains\Users\Models\User;

/**
 * PRD 8.3: everyone browses the catalogue, whoever holds `catalog.manage`
 * curates it.
 */
final class CategoryPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(Permission::ViewCatalog->value);
    }

    public function view(User $actor): bool
    {
        return $actor->can(Permission::ViewCatalog->value);
    }

    public function create(User $actor): bool
    {
        return $actor->can(Permission::ManageCatalog->value);
    }

    public function update(User $actor): bool
    {
        return $actor->can(Permission::ManageCatalog->value);
    }

    public function delete(User $actor): bool
    {
        return $actor->can(Permission::ManageCatalog->value);
    }
}
