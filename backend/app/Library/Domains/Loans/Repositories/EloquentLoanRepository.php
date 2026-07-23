<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Repositories;

use App\Library\Domains\Loans\Contracts\LoanRepositoryInterface;
use App\Library\Domains\Loans\DTOs\LoanFilters;
use App\Library\Domains\Loans\Enums\LoanStatus;
use App\Library\Domains\Loans\Models\Loan;
use App\Library\Shared\Infrastructure\EloquentRepository;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends EloquentRepository<Loan>
 */
final class EloquentLoanRepository extends EloquentRepository implements LoanRepositoryInterface
{
    public function countActiveForUser(int $userId): int
    {
        return Loan::query()
            ->where('user_id', $userId)
            ->whereNull('returned_at')
            ->count();
    }

    public function countActiveForBook(int $bookId): int
    {
        return Loan::query()
            ->where('book_id', $bookId)
            ->whereNull('returned_at')
            ->count();
    }

    public function countOverdueForUser(int $userId, CarbonImmutable $now): int
    {
        return Loan::query()
            ->where('user_id', $userId)
            ->whereNull('returned_at')
            ->whereDate('due_date', '<', $now->startOfDay())
            ->count();
    }

    public function hasActiveLoanForBook(int $userId, int $bookId): bool
    {
        return Loan::query()
            ->where('user_id', $userId)
            ->where('book_id', $bookId)
            ->whereNull('returned_at')
            ->exists();
    }

    /**
     * @return LengthAwarePaginator<int, Loan>
     */
    public function paginate(LoanFilters $filters): LengthAwarePaginator
    {
        $query = $this->withListRelations(Loan::query());
        $today = CarbonImmutable::today();

        if ($filters->status !== null) {
            match ($filters->status) {
                LoanStatus::Returned => $query->whereNotNull('returned_at'),
                LoanStatus::Overdue => $query->whereNull('returned_at')->whereDate('due_date', '<', $today),
                LoanStatus::Active => $query->whereNull('returned_at')->whereDate('due_date', '>=', $today),
            };
        }

        if ($filters->overdue === true) {
            $query->whereNull('returned_at')->whereDate('due_date', '<', $today);
        }

        if ($filters->userId !== null) {
            $query->where('user_id', $filters->userId);
        }

        if ($filters->bookId !== null) {
            $query->where('book_id', $filters->bookId);
        }

        if ($filters->loanedFrom !== null) {
            $query->whereDate('loaned_at', '>=', $filters->loanedFrom);
        }

        if ($filters->loanedTo !== null) {
            $query->whereDate('loaned_at', '<=', $filters->loanedTo);
        }

        return $this->paginateQuery($this->applySort($query, $filters->sort), $filters->pagination);
    }

    public function findById(int $id): ?Loan
    {
        return $this->withListRelations(Loan::query())->find($id);
    }

    public function create(int $userId, int $bookId, CarbonImmutable $loanedAt, CarbonImmutable $dueDate): Loan
    {
        $loan = Loan::query()->create([
            'user_id' => $userId,
            'book_id' => $bookId,
            'loaned_at' => $loanedAt,
            'due_date' => $dueDate,
            'returned_at' => null,
        ]);

        return $this->findById($loan->id) ?? $loan;
    }

    public function markReturned(Loan $loan, CarbonImmutable $returnedAt): Loan
    {
        $loan->returned_at = $returnedAt;
        $loan->save();

        return $this->findById($loan->id) ?? $loan;
    }

    /**
     * @param  Builder<Loan>  $query
     * @return Builder<Loan>
     */
    private function withListRelations(Builder $query): Builder
    {
        return $query->with(['user:id,name,email', 'book:id,title,isbn']);
    }
}
