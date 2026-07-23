<?php

declare(strict_types=1);

namespace App\Library\Domains\Categories\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * BR-CAT-1.
 */
final class CategoryInUseException extends DomainException
{
    private function __construct(private readonly int $booksCount)
    {
        parent::__construct('This category is attached to books and cannot be deleted.');
    }

    public static function withBooks(int $booksCount): self
    {
        return new self($booksCount);
    }

    public function errorCode(): string
    {
        return 'CATEGORY_IN_USE';
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return ['books_count' => $this->booksCount];
    }
}
