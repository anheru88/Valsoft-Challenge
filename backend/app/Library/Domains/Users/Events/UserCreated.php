<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Events;

use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Shared\Domain\DomainEvent;

final readonly class UserCreated implements DomainEvent
{
    public function __construct(
        public int $userId,
        public string $email,
        public UserRole $role,
    ) {}

    public function eventName(): string
    {
        return 'user.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function auditContext(): array
    {
        return [
            'user_id' => $this->userId,
            'role' => $this->role->value,
        ];
    }
}
