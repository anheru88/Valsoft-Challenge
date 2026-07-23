<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Events;

use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Shared\Domain\DomainEvent;

/**
 * Privilege changes are the entries an auditor looks for first (RFC 8).
 */
final readonly class UserRoleChanged implements DomainEvent
{
    public function __construct(
        public int $userId,
        public UserRole $from,
        public UserRole $to,
    ) {}

    public function eventName(): string
    {
        return 'user.role_changed';
    }

    /**
     * @return array<string, mixed>
     */
    public function auditContext(): array
    {
        return [
            'user_id' => $this->userId,
            'from' => $this->from->value,
            'to' => $this->to->value,
        ];
    }
}
