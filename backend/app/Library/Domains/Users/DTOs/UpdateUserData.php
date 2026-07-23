<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\DTOs;

use App\Library\Domains\Users\Enums\UserRole;
use Illuminate\Http\Request;

/**
 * Every field is optional: a null means "not submitted", which is how a member
 * editing their own name never touches their role (FR-USER-3).
 */
final readonly class UpdateUserData
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?UserRole $role = null,
        public ?bool $isActive = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $role = $request->string('role')->toString();

        return new self(
            name: $request->has('name') ? trim($request->string('name')->toString()) : null,
            email: $request->has('email') ? strtolower(trim($request->string('email')->toString())) : null,
            password: $request->has('password') ? $request->string('password')->toString() : null,
            role: $role !== '' ? UserRole::from($role) : null,
            isActive: $request->has('is_active') ? $request->boolean('is_active') : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'is_active' => $this->isActive,
        ], fn (mixed $value): bool => $value !== null);
    }

    public function changesRole(): bool
    {
        return $this->role !== null;
    }

    public function changesStatus(): bool
    {
        return $this->isActive !== null;
    }
}
