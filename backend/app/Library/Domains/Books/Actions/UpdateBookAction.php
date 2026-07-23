<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Actions;

use App\Library\Domains\Books\Contracts\BookRepositoryInterface;
use App\Library\Domains\Books\DTOs\BookData;
use App\Library\Domains\Books\Exceptions\CopiesBelowLoanedException;
use App\Library\Domains\Books\Models\Book;
use App\Library\Shared\Application\TransactionRunner;

final readonly class UpdateBookAction
{
    public function __construct(
        private BookRepositoryInterface $books,
        private TransactionRunner $transaction,
    ) {}

    public function __invoke(Book $book, BookData $data): Book
    {
        // BR-BOOK-3: the copies already in members' hands are the floor for any
        // stock correction.
        $loanedCopies = $book->loanedCopies();

        if ($data->totalCopies < $loanedCopies) {
            throw CopiesBelowLoanedException::make($data->totalCopies, $loanedCopies);
        }

        return $this->transaction->run(fn (): Book => $this->books->update($book, $data));
    }
}
