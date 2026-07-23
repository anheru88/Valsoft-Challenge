<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\DTOs;

use Illuminate\Http\Request;

final readonly class ChangePasswordData
{
    public function __construct(
        public string $currentPassword,
        public string $newPassword,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            currentPassword: $request->string('current_password')->toString(),
            newPassword: $request->string('password')->toString(),
        );
    }
}
