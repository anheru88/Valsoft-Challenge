<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Contracts;

use App\Library\Domains\Books\DTOs\BookData;
use App\Library\Domains\Books\DTOs\BookFilters;
use App\Library\Domains\Books\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Book>
     */
    public function paginate(BookFilters $filters): LengthAwarePaginator;

    public function findById(int $id): ?Book;

    /**
     * Reads the row under a pessimistic lock (ADR-5). The lock is what
     * serialises concurrent check-outs of the last copy, so it must be taken
     * inside the caller's transaction.
     */
    public function findForUpdate(int $id): ?Book;

    /**
     * The counter is owned by this domain (BR-BOOK-2): circulation asks for an
     * adjustment rather than writing the column itself.
     */
    public function decrementAvailableCopies(Book $book): void;

    public function incrementAvailableCopies(Book $book): void;

    public function create(BookData $data): Book;

    public function update(Book $book, BookData $data): Book;

    public function delete(Book $book): void;
}
