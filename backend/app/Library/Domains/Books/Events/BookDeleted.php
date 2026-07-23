<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Events;

use App\Library\Shared\Domain\DomainEvent;

final readonly class BookDeleted implements DomainEvent
{
    public function __construct(
        public int $bookId,
        public string $title,
    ) {}

    public function eventName(): string
    {
        return 'book.deleted';
    }

    /**
     * @return array<string, mixed>
     */
    public function auditContext(): array
    {
        return ['book_id' => $this->bookId, 'title' => $this->title];
    }
}
