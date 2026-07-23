<?php

declare(strict_types=1);

namespace App\Library\Domains\Dashboard\Policies;

use App\Library\Domains\Users\Models\User;

/**
 * PRD 8.3: the dashboard is staff-facing; the reports are an administrator
 * tool. Members get 403 on all of it.
 */
final class DashboardPolicy
{
    public function view(User $actor): bool
    {
        return $actor->isStaff();
    }

    public function viewReports(User $actor): bool
    {
        return $actor->isAdmin();
    }
}
