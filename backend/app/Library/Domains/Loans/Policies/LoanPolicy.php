<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Policies;

use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Users\Enums\Permission;
use App\Library\Domains\Users\Models\User;

/**
 * PRD 8.3: whoever holds `loans.manage` runs circulation; everybody reads their
 * own history. BR-LOAN-6 — members never self-checkout in the MVP.
 */
final class LoanPolicy
{
    /**
     * Everyone may call the list; members are scoped to their own rows by the
     * controller (FR-LOAN-5).
     */
    public function viewAny(User $actor): bool
    {
        return true;
    }

    public function view(User $actor, Loan $loan): bool
    {
        return $loan->user_id === $actor->id || $actor->can(Permission::ViewAnyLoan->value);
    }

    public function create(User $actor): bool
    {
        return $actor->can(Permission::ManageLoans->value);
    }

    public function return(User $actor): bool
    {
        return $actor->can(Permission::ManageLoans->value);
    }

    /**
     * Reading somebody else's history is desk work.
     */
    public function viewHistoryOf(User $actor, User $member): bool
    {
        return $actor->is($member) || $actor->can(Permission::ViewAnyLoan->value);
    }
}
