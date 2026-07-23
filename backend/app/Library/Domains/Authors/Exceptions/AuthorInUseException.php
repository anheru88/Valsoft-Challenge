<?php

declare(strict_types=1);

namespace App\Library\Domains\Authors\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * BR-AUTHOR-1: an author attached to books cannot be deleted; BR-BOOK-5 says
 * every book keeps at least one author, so the books must be reassigned first.
 */
final class AuthorInUseException extends DomainException
{
    private function __construct(private readonly int $booksCount)
    {
        parent::__construct('This author is attached to books and cannot be deleted.');
    }

    public static function withBooks(int $booksCount): self
    {
        return new self($booksCount);
    }

    public function errorCode(): string
    {
        return 'AUTHOR_IN_USE';
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return ['books_count' => $this->booksCount];
    }
}
