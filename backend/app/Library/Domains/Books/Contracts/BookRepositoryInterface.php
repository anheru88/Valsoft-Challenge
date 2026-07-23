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

    public function create(BookData $data): Book;

    public function update(Book $book, BookData $data): Book;

    public function delete(Book $book): void;
}
