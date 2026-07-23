<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * FR-SRCH-3: a one-character non-ISBN query would scan most of the catalogue
 * and return nothing useful, so it is refused rather than served slowly.
 */
final class SearchQueryTooShortException extends DomainException
{
    private function __construct(private readonly int $minimum)
    {
        parent::__construct("The search query must be at least {$minimum} characters long.");
    }

    public static function requiring(int $minimum): self
    {
        return new self($minimum);
    }

    public function errorCode(): string
    {
        return 'SEARCH_QUERY_TOO_SHORT';
    }

    public function httpStatus(): int
    {
        return 422;
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return ['min_length' => $this->minimum];
    }
}
