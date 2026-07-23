<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Actions;

use App\Library\Domains\Books\Contracts\BookRepositoryInterface;
use App\Library\Domains\Loans\Contracts\LoanRepositoryInterface;
use App\Library\Domains\Loans\DTOs\CheckoutData;
use App\Library\Domains\Loans\Events\LoanCreated;
use App\Library\Domains\Loans\Exceptions\BorrowerNotEligibleException;
use App\Library\Domains\Loans\Exceptions\DuplicateActiveLoanException;
use App\Library\Domains\Loans\Exceptions\LoanLimitReachedException;
use App\Library\Domains\Loans\Exceptions\MemberHasOverdueLoansException;
use App\Library\Domains\Loans\Exceptions\NoCopiesAvailableException;
use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Loans\ValueObjects\DueDate;
use App\Library\Domains\Users\Contracts\UserRepositoryInterface;
use App\Library\Domains\Users\Models\User;
use App\Library\Shared\Application\Clock;
use App\Library\Shared\Application\TransactionRunner;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Check-out: the transaction that carries every circulation rule (BR-LOAN-1..9).
 *
 * The book row is locked first (ADR-5). That lock does double duty: it stops
 * two check-outs from overselling the last copy, and it serialises the member's
 * rule checks against a concurrent check-out for the same member, so the limit
 * cannot be exceeded by two requests that each saw four active loans.
 */
final readonly class CheckoutBookAction
{
    public function __construct(
        private LoanRepositoryInterface $loans,
        private BookRepositoryInterface $books,
        private UserRepositoryInterface $users,
        private TransactionRunner $transaction,
        private Clock $clock,
        private Dispatcher $events,
    ) {}

    public function __invoke(CheckoutData $data): Loan
    {
        $loan = $this->transaction->run(function () use ($data): Loan {
            $book = $this->books->findForUpdate($data->bookId);

            if ($book === null) {
                throw new ModelNotFoundException;
            }

            $borrower = $this->users->findById($data->userId);

            if ($borrower === null) {
                throw new ModelNotFoundException;
            }

            $this->guardBorrower($borrower);

            // BR-LOAN-1: read after the lock, so the value cannot be stale.
            if ($book->available_copies < 1) {
                throw new NoCopiesAvailableException;
            }

            $this->guardMemberStanding($borrower->id, $book->id);

            $loanedAt = $this->clock->today();
            $dueDate = DueDate::fromLoanDate($loanedAt, $data->requestedDueDate);

            $created = $this->loans->create($borrower->id, $book->id, $loanedAt, $dueDate->value);

            // BR-LOAN-7: the loan row and the counter move together or not at all.
            $this->books->decrementAvailableCopies($book);

            return $created;
        });

        $this->events->dispatch(new LoanCreated($loan->id, $loan->user_id, $loan->book_id, $loan->due_date->toDateString()));

        return $loan;
    }

    /**
     * BR-LOAN-9.
     */
    private function guardBorrower(User $borrower): void
    {
        if ($borrower->isStaff()) {
            throw BorrowerNotEligibleException::notAMember();
        }

        if (! $borrower->is_active) {
            throw BorrowerNotEligibleException::inactive();
        }
    }

    private function guardMemberStanding(int $borrowerId, int $bookId): void
    {
        // BR-LOAN-4 first: the most specific answer is the most useful one.
        if ($this->loans->hasActiveLoanForBook($borrowerId, $bookId)) {
            throw new DuplicateActiveLoanException;
        }

        // BR-LOAN-3.
        $overdue = $this->loans->countOverdueForUser($borrowerId, $this->clock->now());

        if ($overdue > 0) {
            throw MemberHasOverdueLoansException::withCount($overdue);
        }

        // BR-LOAN-2.
        $limit = (int) config('library.loans.max_active_per_member');
        $activeLoans = $this->loans->countActiveForUser($borrowerId);

        if ($activeLoans >= $limit) {
            throw LoanLimitReachedException::make($limit, $activeLoans);
        }
    }
}
