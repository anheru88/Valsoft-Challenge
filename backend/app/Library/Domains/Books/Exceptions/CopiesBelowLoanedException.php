<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * BR-BOOK-3: the stock cannot be corrected down past the copies that are
 * currently out, which would make available_copies negative.
 *
 * Answered as 422 rather than 409 because the submitted number itself is the
 * problem — the API specification pins this code to that status.
 */
final class CopiesBelowLoanedException extends DomainException
{
    private function __construct(
        private readonly int $requestedTotal,
        private readonly int $loanedCopies,
    ) {
        parent::__construct("Total copies cannot be lower than the {$loanedCopies} copies currently on loan.");
    }

    public static function make(int $requestedTotal, int $loanedCopies): self
    {
        return new self($requestedTotal, $loanedCopies);
    }

    public function errorCode(): string
    {
        return 'BOOK_COPIES_BELOW_LOANED';
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
        return [
            'requested_total_copies' => $this->requestedTotal,
            'loaned_copies' => $this->loanedCopies,
        ];
    }
}
