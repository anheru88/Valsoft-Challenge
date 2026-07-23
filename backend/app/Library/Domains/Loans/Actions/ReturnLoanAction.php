<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Actions;

use App\Library\Domains\Books\Contracts\BookRepositoryInterface;
use App\Library\Domains\Loans\Contracts\LoanRepositoryInterface;
use App\Library\Domains\Loans\Events\LoanReturned;
use App\Library\Domains\Loans\Exceptions\LoanAlreadyReturnedException;
use App\Library\Domains\Loans\Models\Loan;
use App\Library\Shared\Application\Clock;
use App\Library\Shared\Application\TransactionRunner;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Check-in (FR-LOAN-2). Same shape as check-out: lock the book row, then close
 * the loan and give the copy back inside one transaction.
 */
final readonly class ReturnLoanAction
{
    public function __construct(
        private LoanRepositoryInterface $loans,
        private BookRepositoryInterface $books,
        private TransactionRunner $transaction,
        private Clock $clock,
        private Dispatcher $events,
    ) {}

    public function __invoke(Loan $loan): Loan
    {
        $returned = $this->transaction->run(function () use ($loan): Loan {
            $book = $this->books->findForUpdate($loan->book_id);

            if ($book === null) {
                throw new ModelNotFoundException;
            }

            // Re-read under the lock: a concurrent check-in of the same loan
            // must not increment the counter twice (BR-LOAN-8).
            $fresh = $this->loans->findById($loan->id);

            if ($fresh === null) {
                throw new ModelNotFoundException;
            }

            if ($fresh->isReturned()) {
                throw new LoanAlreadyReturnedException;
            }

            $closed = $this->loans->markReturned($fresh, $this->clock->now());

            $this->books->incrementAvailableCopies($book);

            return $closed;
        });

        $this->events->dispatch(new LoanReturned($returned->id, $returned->user_id, $returned->book_id));

        return $returned;
    }
}
