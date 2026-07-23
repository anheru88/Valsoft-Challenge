<?php

declare(strict_types=1);

namespace App\Library\Domains\Authors\Contracts;

use App\Library\Domains\Authors\DTOs\AuthorData;
use App\Library\Domains\Authors\DTOs\AuthorFilters;
use App\Library\Domains\Authors\Models\Author;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuthorRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Author>
     */
    public function paginate(AuthorFilters $filters): LengthAwarePaginator;

    public function findById(int $id): ?Author;

    public function create(AuthorData $data): Author;

    public function update(Author $author, AuthorData $data): Author;

    public function delete(Author $author): void;

    /**
     * BR-AUTHOR-1: books referencing this author, which block its removal.
     */
    public function countBooks(int $authorId): int;
}
