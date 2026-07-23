<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\DTOs;

use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Enums\UserSortField;
use App\Library\Shared\Application\PaginationParams;
use App\Library\Shared\Application\SortParams;
use Illuminate\Http\Request;

final readonly class UserFilters
{
    public function __construct(
        public ?string $q,
        public ?UserRole $role,
        public ?bool $isActive,
        public PaginationParams $pagination,
        public SortParams $sort,
    ) {}

    /**
     * The same query, narrowed to member accounts.
     *
     * Used for an actor who may read accounts for desk service but not survey
     * the staff: whatever `role` the request asked for, the answer is members.
     */
    public function scopedToMembers(): self
    {
        return new self(
            q: $this->q,
            role: UserRole::Member,
            isActive: $this->isActive,
            pagination: $this->pagination,
            sort: $this->sort,
        );
    }

    public static function fromRequest(Request $request): self
    {
        $role = $request->string('role')->toString();

        return new self(
            q: $request->string('q')->trim()->toString() ?: null,
            role: $role !== '' ? UserRole::from($role) : null,
            isActive: $request->has('is_active') ? $request->boolean('is_active') : null,
            pagination: PaginationParams::fromRequest($request),
            sort: SortParams::fromRequest($request, UserSortField::values(), UserSortField::CreatedAt->value),
        );
    }
}
