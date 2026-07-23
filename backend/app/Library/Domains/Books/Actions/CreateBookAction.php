<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Actions;

use App\Library\Domains\Books\Contracts\BookRepositoryInterface;
use App\Library\Domains\Books\DTOs\BookData;
use App\Library\Domains\Books\Events\BookCreated;
use App\Library\Domains\Books\Models\Book;
use App\Library\Shared\Application\TransactionRunner;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class CreateBookAction
{
    public function __construct(
        private BookRepositoryInterface $books,
        private TransactionRunner $transaction,
        private Dispatcher $events,
    ) {}

    public function __invoke(BookData $data): Book
    {
        // The row and its author/category links are one fact (BR-BOOK-5): a
        // book must never exist without them.
        $book = $this->transaction->run(fn (): Book => $this->books->create($data));

        $this->events->dispatch(new BookCreated($book->id, $book->title, $book->isbn));

        return $book;
    }
}
