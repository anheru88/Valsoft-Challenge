<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Events;

use App\Library\Shared\Domain\DomainEvent;

final readonly class UserDeleted implements DomainEvent
{
    public function __construct(
        public int $userId,
        public string $email,
    ) {}

    public function eventName(): string
    {
        return 'user.deleted';
    }

    /**
     * @return array<string, mixed>
     */
    public function auditContext(): array
    {
        return ['user_id' => $this->userId];
    }
}
