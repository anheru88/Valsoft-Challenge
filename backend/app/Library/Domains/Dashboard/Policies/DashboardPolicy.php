<?php

declare(strict_types=1);

namespace App\Library\Domains\Dashboard\Policies;

use App\Library\Domains\Users\Enums\Permission;
use App\Library\Domains\Users\Models\User;

/**
 * PRD 8.3: the dashboard is staff-facing, the reports are an administrator
 * tool. Members hold neither capability and get 403.
 */
final class DashboardPolicy
{
    public function view(User $actor): bool
    {
        return $actor->can(Permission::ViewDashboard->value);
    }

    public function viewReports(User $actor): bool
    {
        return $actor->can(Permission::ViewReports->value);
    }
}
