<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Events;

use App\Library\Shared\Domain\DomainEvent;

final readonly class LoanReturned implements DomainEvent
{
    public function __construct(
        public int $loanId,
        public int $userId,
        public int $bookId,
    ) {}

    public function eventName(): string
    {
        return 'loan.returned';
    }

    /**
     * @return array<string, mixed>
     */
    public function auditContext(): array
    {
        return [
            'loan_id' => $this->loanId,
            'user_id' => $this->userId,
            'book_id' => $this->bookId,
        ];
    }
}
