<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Repositories;

use App\Library\Domains\Loans\Contracts\LoanRepositoryInterface;
use App\Library\Domains\Loans\Models\Loan;

final class EloquentLoanRepository implements LoanRepositoryInterface
{
    public function countActiveForUser(int $userId): int
    {
        return Loan::query()
            ->where('user_id', $userId)
            ->whereNull('returned_at')
            ->count();
    }
}
