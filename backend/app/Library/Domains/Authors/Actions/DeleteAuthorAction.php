<?php

declare(strict_types=1);

namespace App\Library\Domains\Authors\Actions;

use App\Library\Domains\Authors\Contracts\AuthorRepositoryInterface;
use App\Library\Domains\Authors\Exceptions\AuthorInUseException;
use App\Library\Domains\Authors\Models\Author;

final readonly class DeleteAuthorAction
{
    public function __construct(private AuthorRepositoryInterface $authors) {}

    public function __invoke(Author $author): void
    {
        $booksCount = $this->authors->countBooks($author->id);

        if ($booksCount > 0) {
            throw AuthorInUseException::withBooks($booksCount);
        }

        $this->authors->delete($author);
    }
}
