<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Actions;

use App\Library\Domains\Books\Contracts\BookRepositoryInterface;
use App\Library\Domains\Books\Events\BookDeleted;
use App\Library\Domains\Books\Exceptions\BookHasActiveLoansException;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Loans\Contracts\LoanRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class DeleteBookAction
{
    public function __construct(
        private BookRepositoryInterface $books,
        private LoanRepositoryInterface $loans,
        private Dispatcher $events,
    ) {}

    public function __invoke(Book $book): void
    {
        // BR-BOOK-4, asked through the Loans contract rather than by querying
        // another domain's tables (RFC 4).
        $activeLoans = $this->loans->countActiveForBook($book->id);

        if ($activeLoans > 0) {
            throw BookHasActiveLoansException::withCount($activeLoans);
        }

        $this->books->delete($book);

        $this->events->dispatch(new BookDeleted($book->id, $book->title));
    }
}
