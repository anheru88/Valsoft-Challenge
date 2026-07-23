<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\DTOs;

use App\Library\Domains\Users\Enums\UserRole;
use Illuminate\Http\Request;

/**
 * Immutable input for user creation. Controllers hand this inward instead of a
 * request array, which is also what makes mass assignment a non-issue: only the
 * fields named here can ever be written (RFC 12).
 */
final readonly class CreateUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserRole $role,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: trim($request->string('name')->toString()),
            email: strtolower(trim($request->string('email')->toString())),
            password: $request->string('password')->toString(),
            role: UserRole::from($request->string('role')->toString()),
        );
    }

    /**
     * Public registration always yields a member (FR-AUTH-2, BR-USER-2).
     */
    public static function forRegistration(Request $request): self
    {
        return new self(
            name: trim($request->string('name')->toString()),
            email: strtolower(trim($request->string('email')->toString())),
            password: $request->string('password')->toString(),
            role: UserRole::Member,
        );
    }
}
