<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Events;

use App\Library\Shared\Domain\DomainEvent;

final readonly class BookCreated implements DomainEvent
{
    public function __construct(
        public int $bookId,
        public string $title,
        public string $isbn,
    ) {}

    public function eventName(): string
    {
        return 'book.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function auditContext(): array
    {
        return ['book_id' => $this->bookId, 'isbn' => $this->isbn];
    }
}
