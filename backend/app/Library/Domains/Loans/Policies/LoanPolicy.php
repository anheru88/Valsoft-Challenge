<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Policies;

use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Users\Models\User;

/**
 * PRD 8.3: staff run circulation; members read their own history and nothing
 * else. BR-LOAN-6 — members never self-checkout in the MVP.
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
        return $actor->isStaff() || $loan->user_id === $actor->id;
    }

    public function create(User $actor): bool
    {
        return $actor->isStaff();
    }

    public function return(User $actor): bool
    {
        return $actor->isStaff();
    }

    /**
     * Reading somebody else's history is desk work.
     */
    public function viewHistoryOf(User $actor, User $member): bool
    {
        return $actor->isStaff() || $actor->is($member);
    }
}
